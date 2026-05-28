<?php
class Sale {
    private $conn;
    private $table = 'sales';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->conn->beginTransaction();
        try {
            if (empty($data['customer_name'])) {
                throw new Exception("Nama pelanggan harus diisi.");
            }
            if (empty($data['qty']) || $data['qty'] <= 0) {
                throw new Exception("Jumlah pembelian harus lebih dari 0.");
            }
            if (empty($data['livestock_id'])) {
                throw new Exception("Hewan harus dipilih.");
            }
            if (empty($data['invoice_code'])) {
                throw new Exception("Kode invoice harus diisi.");
            }

            // Fetch livestock
            $stmt = $this->conn->prepare("SELECT * FROM livestock WHERE id = ?");
            $stmt->execute([$data['livestock_id']]);
            $livestock = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$livestock) {
                throw new Exception("Hewan tidak ditemukan.");
            }

            if ($livestock['status'] === 'inactive') {
                throw new Exception("Hewan tidak aktif.");
            }

            if ($livestock['stock'] < $data['qty']) {
                throw new Exception("Stok hewan tidak mencukupi. Tersedia: " . $livestock['stock']);
            }

            $sellingPrice = $livestock['selling_price'];
            $totalPrice = $sellingPrice * $data['qty'];

            // Check dynamic payment validations
            if ($data['payment_type'] === 'dp') {
                if (!isset($data['payment_amount']) || $data['payment_amount'] <= 0) {
                    throw new Exception("Pembayaran DP harus diisi dan lebih dari 0.");
                }
                if ($data['payment_amount'] >= $totalPrice) {
                    throw new Exception("Pembayaran DP tidak boleh melebihi atau sama dengan total harga. Gunakan tipe bayar Lunas.");
                }
            }

            // Insert sale
            $query = "INSERT INTO " . $this->table . " 
                (invoice_code, customer_name, customer_phone, livestock_id, livestock_name, peternak_name, qty, selling_price_snapshot, total_price, payment_type, payment_status, sale_status, notes, created_by, payment_method, payment_proof) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['invoice_code'],
                $data['customer_name'],
                $data['customer_phone'] ?? '',
                $data['livestock_id'],
                $livestock['breed'],
                $livestock['peternak_name'],
                $data['qty'],
                $sellingPrice,
                $totalPrice,
                $data['payment_type'],
                $data['payment_status'] ?? 'unpaid',
                $data['sale_status'] ?? 'pending',
                $data['notes'] ?? null,
                $data['created_by'],
                $data['payment_method'] ?? null,
                $data['payment_proof'] ?? null
            ]);

            $saleId = $this->conn->lastInsertId();

            // Insert initial payment if amount is provided
            if (isset($data['payment_amount']) && $data['payment_amount'] > 0) {
                $paymentCode = 'PAY-' . time() . '-' . rand(100, 999);
                $pQuery = "INSERT INTO sale_payments 
                    (sale_id, payment_code, payment_method, payment_amount, payment_note, payment_proof, payment_status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $pStmt = $this->conn->prepare($pQuery);
                $pStmt->execute([
                    $saleId,
                    $paymentCode,
                    $data['payment_method'] ?? 'Transfer Bank Manual',
                    $data['payment_amount'],
                    $data['payment_note'] ?? 'Pembayaran awal',
                    $data['payment_proof'] ?? null,
                    $data['initial_payment_status'] ?? 'pending',
                    $data['created_by']
                ]);
            }

            // Reduce livestock stock
            $newStock = $livestock['stock'] - $data['qty'];
            $status = ($newStock === 0) ? 'sold' : $livestock['status'];
            $updateStmt = $this->conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
            $updateStmt->execute([$newStock, $status, $data['livestock_id']]);

            $this->conn->commit();

            // Recalculate balance
            $this->recalculateBalance($saleId);

            return $saleId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getAll($search = '', $payment_status = '', $sale_status = '', $created_by = null) {
        $query = "SELECT s.*, u.name as creator_name, l.image as livestock_image
                 FROM " . $this->table . " s
                 JOIN users u ON s.created_by = u.id
                 LEFT JOIN livestock l ON s.livestock_id = l.id
                 WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (s.invoice_code LIKE ? OR s.customer_name LIKE ? OR s.livestock_name LIKE ? OR s.peternak_name LIKE ?)";
            $searchVal = "%$search%";
            $params[] = $searchVal;
            $params[] = $searchVal;
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        if ($payment_status) {
            $query .= " AND s.payment_status = ?";
            $params[] = $payment_status;
        }

        if ($sale_status) {
            $query .= " AND s.sale_status = ?";
            $params[] = $sale_status;
        }

        if ($created_by !== null) {
            $query .= " AND s.created_by = ?";
            $params[] = $created_by;
        }

        $query .= " ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT s.*, u.name as creator_name, l.image as livestock_image, l.livestock_code
                 FROM " . $this->table . " s
                 JOIN users u ON s.created_by = u.id
                 LEFT JOIN livestock l ON s.livestock_id = l.id
                 WHERE s.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByInvoiceCode($invoice_code) {
        $query = "SELECT s.*, u.name as creator_name, l.image as livestock_image, l.livestock_code
                 FROM " . $this->table . " s
                 JOIN users u ON s.created_by = u.id
                 LEFT JOIN livestock l ON s.livestock_id = l.id
                 WHERE s.invoice_code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$invoice_code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
                  sale_status = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['sale_status'],
            $id
        ]);
    }

    public function recalculateBalance($id) {
        // Fetch total_price of the sale
        $stmt = $this->conn->prepare("SELECT total_price FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$id]);
        $totalPrice = $stmt->fetchColumn();
        if ($totalPrice === false) return false;

        // Fetch sum of verified payments
        $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM sale_payments WHERE sale_id = ? AND payment_status = 'verified'");
        $stmt->execute([$id]);
        $totalPaid = $stmt->fetchColumn() ?: 0;

        $paymentStatus = 'unpaid';
        if ($totalPaid >= $totalPrice) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        // Update payment_status of sales
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET payment_status = ? WHERE id = ?");
        return $stmt->execute([$paymentStatus, $id]);
    }

    public function getPayments($sale_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sale_payments WHERE sale_id = ? ORDER BY payment_date ASC");
        $stmt->execute([$sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPaid($sale_id) {
        $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM sale_payments WHERE sale_id = ? AND payment_status = 'verified'");
        $stmt->execute([$sale_id]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getRemainingBalance($sale_id) {
        $stmt = $this->conn->prepare("SELECT total_price FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$sale_id]);
        $total = $stmt->fetchColumn() ?: 0;

        $paid = $this->getTotalPaid($sale_id);
        return max(0, $total - $paid);
    }

    public function delete($id) {
        // Deleting a sale should restore livestock stock!
        $sale = $this->getById($id);
        if (!$sale) return false;

        $this->conn->beginTransaction();
        try {
            // Delete record
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            // Restore stock
            $stmt = $this->conn->prepare("SELECT stock, status FROM livestock WHERE id = ?");
            $stmt->execute([$sale['livestock_id']]);
            $livestock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($livestock) {
                $newStock = $livestock['stock'] + $sale['qty'];
                $status = $livestock['status'];
                if ($status === 'sold' && $newStock > 0) {
                    $status = 'available';
                }
                
                $updateStmt = $this->conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
                $updateStmt->execute([$newStock, $status, $sale['livestock_id']]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
