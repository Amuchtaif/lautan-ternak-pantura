<?php
class Purchase {
    private $conn;
    private $table = 'purchases';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->conn->beginTransaction();
        try {
            if (empty($data['peternak_name'])) {
                throw new Exception("Nama peternak harus diisi.");
            }
            if (empty($data['livestock_id'])) {
                throw new Exception("Hewan harus dipilih.");
            }
            if (empty($data['qty']) || $data['qty'] <= 0) {
                throw new Exception("Jumlah pembelian harus lebih dari 0.");
            }
            if (empty($data['purchase_price']) || $data['purchase_price'] <= 0) {
                throw new Exception("Harga beli harus lebih dari 0.");
            }
            if (empty($data['purchase_code'])) {
                throw new Exception("Kode pembelian harus diisi.");
            }

            // Fetch livestock
            $stmt = $this->conn->prepare("SELECT * FROM livestock WHERE id = ?");
            $stmt->execute([$data['livestock_id']]);
            $livestock = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$livestock) {
                throw new Exception("Hewan tidak ditemukan.");
            }

            $total_purchase = $data['qty'] * $data['purchase_price'];
            $payment_type = $data['payment_type'] ?? 'lunas';
            $amount_paid = ($payment_type === 'dp') ? floatval($data['amount_paid'] ?? 0) : $total_purchase;

            // Insert purchase
            $query = "INSERT INTO " . $this->table . " 
                (purchase_code, livestock_id, peternak_name, qty, purchase_price, total_purchase, amount_paid, payment_type, notes, created_by, purchased_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['purchase_code'],
                $data['livestock_id'],
                $data['peternak_name'],
                $data['qty'],
                $data['purchase_price'],
                $total_purchase,
                $amount_paid,
                $data['payment_type'] ?? 'lunas',
                $data['notes'] ?? null,
                $data['created_by'],
                $data['purchased_at'] ?? date('Y-m-d H:i:s')
            ]);

            $purchaseId = intval($this->conn->lastInsertId());

            if ($amount_paid > 0) {
                $payCode = 'LTP-PUR-PAY-' . time() . '-' . rand(100, 999) . '-' . $purchaseId;
                $payStmt = $this->conn->prepare("INSERT INTO purchase_payments (purchase_id, payment_code, payment_amount, payment_date, notes) VALUES (?, ?, ?, ?, ?)");
                $payStmt->execute([
                    $purchaseId,
                    $payCode,
                    $amount_paid,
                    $data['purchased_at'] ?? date('Y-m-d H:i:s'),
                    ($payment_type === 'dp') ? 'Pembayaran Uang Muka (DP)' : 'Pembayaran Lunas Awal'
                ]);
            }

            // Increment livestock stock
            $newStock = $livestock['stock'] + $data['qty'];
            $status = $livestock['status'];
            if ($status === 'sold' && $newStock > 0) {
                $status = 'available';
            }

            $updateStmt = $this->conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
            $updateStmt->execute([$newStock, $status, $data['livestock_id']]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getAll($search = '', $start_date = '', $end_date = '') {
        $query = "SELECT p.*, u.name as admin_name, l.breed as livestock_name, l.livestock_code, l.breed, l.weight, l.selling_price
                 FROM " . $this->table . " p
                 JOIN users u ON p.created_by = u.id
                 JOIN livestock l ON p.livestock_id = l.id
                 WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (p.peternak_name LIKE ? OR p.purchase_code LIKE ? OR l.breed LIKE ?)";
            $searchVal = "%$search%";
            $params[] = $searchVal;
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        if ($start_date) {
            $query .= " AND DATE(p.purchased_at) >= DATE(?)";
            $params[] = $start_date;
        }

        if ($end_date) {
            $query .= " AND DATE(p.purchased_at) <= DATE(?)";
            $params[] = $end_date;
        }

        $query .= " ORDER BY p.purchased_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT p.*, u.name as admin_name, l.breed as livestock_name, l.livestock_code, l.selling_price, l.breed, l.weight
                 FROM " . $this->table . " p
                 JOIN users u ON p.created_by = u.id
                 JOIN livestock l ON p.livestock_id = l.id
                 WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $purchase = $this->getById($id);
        if (!$purchase) {
            throw new Exception("Data pembelian tidak ditemukan.");
        }

        $this->conn->beginTransaction();
        try {
            // Get livestock
            $stmt = $this->conn->prepare("SELECT * FROM livestock WHERE id = ?");
            $stmt->execute([$purchase['livestock_id']]);
            $livestock = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$livestock) {
                throw new Exception("Hewan tidak ditemukan.");
            }

            // Calculate new stock
            $qtyDiff = $data['qty'] - $purchase['qty'];
            $newStock = max(0, $livestock['stock'] + $qtyDiff);
            $status = ($newStock === 0) ? 'sold' : $livestock['status'];
            if ($status === 'sold' && $newStock > 0) {
                $status = 'available';
            }

            // Update livestock details
            // Combine breed and livestock_name
            $breedLower = strtolower($data['breed']);
            $nameLower = strtolower($data['livestock_name']);
            if (strpos($nameLower, $breedLower) === 0) {
                $breedDbValue = ucfirst($data['livestock_name']);
            } else {
                $breedDbValue = ucfirst($data['breed']) . ' ' . $data['livestock_name'];
            }
            $updateLivestockQuery = "UPDATE livestock 
                                     SET breed = ?, weight = ?, purchase_price = ?, selling_price = ?, stock = ?, status = ? 
                                     WHERE id = ?";
            $updateLivestockStmt = $this->conn->prepare($updateLivestockQuery);
            $updateLivestockStmt->execute([
                $breedDbValue,
                $data['weight'],
                $data['purchase_price'],
                $data['selling_price'],
                $newStock,
                $status,
                $purchase['livestock_id']
            ]);

            // Update purchase record
            $total_purchase = $data['qty'] * $data['purchase_price'];
            $updatePurchaseQuery = "UPDATE " . $this->table . " 
                                    SET qty = ?, purchase_price = ?, total_purchase = ?, notes = ? 
                                    WHERE id = ?";
            $updatePurchaseStmt = $this->conn->prepare($updatePurchaseQuery);
            $updatePurchaseStmt->execute([
                $data['qty'],
                $data['purchase_price'],
                $total_purchase,
                $data['notes'],
                $id
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        // Deleting a purchase might decrease livestock stock
        $purchase = $this->getById($id);
        if (!$purchase) return false;

        $this->conn->beginTransaction();
        try {
            // Delete record
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            // Get livestock
            $stmt = $this->conn->prepare("SELECT stock, status FROM livestock WHERE id = ?");
            $stmt->execute([$purchase['livestock_id']]);
            $livestock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($livestock) {
                $newStock = max(0, $livestock['stock'] - $purchase['qty']);
                $status = ($newStock === 0) ? 'sold' : $livestock['status'];
                
                $updateStmt = $this->conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
                $updateStmt->execute([$newStock, $status, $purchase['livestock_id']]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function recordPayment($id, $amount, $paymentDate = null) {
        $purchase = $this->getById($id);
        if (!$purchase) {
            throw new Exception("Data pembelian tidak ditemukan.");
        }
        
        $remaining = $purchase['total_purchase'] - $purchase['amount_paid'];
        if ($amount > $remaining) {
            throw new Exception("Jumlah pembayaran melebihi sisa kekurangan Rp " . number_format($remaining, 0, ',', '.'));
        }
        
        $newAmountPaid = $purchase['amount_paid'] + $amount;
        $paymentType = ($newAmountPaid >= $purchase['total_purchase']) ? 'lunas' : $purchase['payment_type'];
        
        $this->conn->beginTransaction();
        try {
            // Update purchase table
            $query = "UPDATE " . $this->table . " SET amount_paid = ?, payment_type = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$newAmountPaid, $paymentType, $id]);
            
            // Insert into purchase_payments table
            $payCode = 'LTP-PUR-PAY-' . time() . '-' . rand(100, 999) . '-' . $id;
            
            // Format payment date
            $dateVal = $paymentDate ?: date('Y-m-d H:i:s');
            // If the user only selected a date (YYYY-MM-DD), let's append current time so it preserves chronological order nicely
            if (strlen($dateVal) == 10) {
                $dateVal .= ' ' . date('H:i:s');
            }
            
            $payStmt = $this->conn->prepare("INSERT INTO purchase_payments (purchase_id, payment_code, payment_amount, payment_date, notes) VALUES (?, ?, ?, ?, ?)");
            $payStmt->execute([
                $id,
                $payCode,
                $amount,
                $dateVal,
                'Pembayaran Tambahan / Pelunasan'
            ]);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
