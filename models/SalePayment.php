<?php
class SalePayment {
    private $conn;
    private $table = 'sale_payments';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->conn->beginTransaction();
        try {
            if (empty($data['sale_id'])) {
                throw new Exception("Sale ID harus diisi.");
            }
            if (empty($data['payment_amount']) || $data['payment_amount'] <= 0) {
                throw new Exception("Jumlah pembayaran harus lebih dari 0.");
            }
            if (empty($data['payment_code'])) {
                throw new Exception("Kode pembayaran harus diisi.");
            }

            // Fetch the sale
            $stmt = $this->conn->prepare("SELECT total_price FROM sales WHERE id = ?");
            $stmt->execute([$data['sale_id']]);
            $totalPrice = $stmt->fetchColumn();
            if ($totalPrice === false) {
                throw new Exception("Transaksi penjualan tidak ditemukan.");
            }

            // Fetch current verified payments sum
            $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM " . $this->table . " WHERE sale_id = ? AND payment_status = 'verified'");
            $stmt->execute([$data['sale_id']]);
            $totalPaid = $stmt->fetchColumn() ?: 0;

            $remaining = $totalPrice - $totalPaid;

            // Strict ceiling validation
            if ($data['payment_status'] === 'verified' && $data['payment_amount'] > $remaining) {
                throw new Exception("Jumlah pembayaran Rp " . number_format($data['payment_amount'], 0, ',', '.') . " melebihi sisa kekurangan Rp " . number_format($remaining, 0, ',', '.') . ".");
            }

            // Insert payment
            $query = "INSERT INTO " . $this->table . " 
                (sale_id, payment_code, payment_method, payment_amount, payment_note, payment_proof, payment_status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['sale_id'],
                $data['payment_code'],
                $data['payment_method'] ?? 'Transfer Bank Manual',
                $data['payment_amount'],
                $data['payment_note'] ?? null,
                $data['payment_proof'] ?? null,
                $data['payment_status'] ?? 'pending',
                $data['created_by']
            ]);

            $paymentId = $this->conn->lastInsertId();

            if (isset($data['payment_status']) && $data['payment_status'] === 'verified') {
                $bankStmt = $this->conn->query("SELECT id FROM cash_accounts WHERE status = 'active' ORDER BY type = 'bank' DESC, id ASC LIMIT 1");
                $defaultBankId = $bankStmt->fetchColumn();
                if ($defaultBankId) {
                    require_once 'models/CashTransaction.php';
                    $cashTx = new CashTransaction($this->conn);
                    
                    // Fetch livestock details
                    $livestockStmt = $this->conn->prepare("SELECT l.* FROM livestock l JOIN sales s ON s.livestock_id = l.id WHERE s.id = ?");
                    $livestockStmt->execute([$data['sale_id']]);
                    $livestock = $livestockStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Fetch customer name
                    $custStmt = $this->conn->prepare("SELECT customer_name FROM sales WHERE id = ?");
                    $custStmt->execute([$data['sale_id']]);
                    $custName = $custStmt->fetchColumn();
                    
                    $desc = "Cicilan Penjualan " . ($livestock['breed'] ?: 'Hewan') . " - " . ($livestock['livestock_code'] ?? '') . " (" . $custName . ")";
                    
                    $cashTx->record(
                        $defaultBankId,
                        'PENJUALAN_HEWAN',
                        'sale_payments',
                        $paymentId,
                        $desc,
                        $data['payment_amount'], // cash_in
                        0,                       // cash_out
                        $data['created_by']
                    );
                }
            }

            $this->conn->commit();

            // Recalculate sale status
            require_once 'models/Sale.php';
            $saleModel = new Sale($this->conn);
            $saleModel->recalculateBalance($data['sale_id']);

            return $paymentId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getById($id) {
        $query = "SELECT p.*, s.invoice_code, s.customer_name, s.total_price 
                 FROM " . $this->table . " p
                 JOIN sales s ON p.sale_id = s.id
                 WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySaleId($sale_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE sale_id = ? ORDER BY payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $payment = $this->getById($id);
        if (!$payment) {
            throw new Exception("Data pembayaran tidak ditemukan.");
        }

        $this->conn->beginTransaction();
        try {
            if ($status === 'verified') {
                // Fetch total price of sale
                $stmt = $this->conn->prepare("SELECT total_price FROM sales WHERE id = ?");
                $stmt->execute([$payment['sale_id']]);
                $totalPrice = $stmt->fetchColumn();

                // Fetch sum of OTHER verified payments
                $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM " . $this->table . " WHERE sale_id = ? AND payment_status = 'verified' AND id != ?");
                $stmt->execute([$payment['sale_id'], $id]);
                $otherPaid = $stmt->fetchColumn() ?: 0;

                $remaining = $totalPrice - $otherPaid;

                if ($payment['payment_amount'] > $remaining) {
                    throw new Exception("Tidak dapat memverifikasi pembayaran ini. Jumlah Rp " . number_format($payment['payment_amount'], 0, ',', '.') . " melebihi sisa kekurangan Rp " . number_format($remaining, 0, ',', '.') . ".");
                }

                // Log into general cash transactions ledger
                $bankStmt = $this->conn->query("SELECT id FROM cash_accounts WHERE status = 'active' ORDER BY type = 'bank' DESC, id ASC LIMIT 1");
                $defaultBankId = $bankStmt->fetchColumn();
                if ($defaultBankId) {
                    require_once 'models/CashTransaction.php';
                    $cashTx = new CashTransaction($this->conn);
                    
                    // Fetch livestock details
                    $livestockStmt = $this->conn->prepare("SELECT l.* FROM livestock l JOIN sales s ON s.livestock_id = l.id WHERE s.id = ?");
                    $livestockStmt->execute([$payment['sale_id']]);
                    $livestock = $livestockStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $desc = "Pelunasan / Cicilan Penjualan " . ($livestock['breed'] ?: 'Hewan') . " - " . ($livestock['livestock_code'] ?? '') . " (" . $payment['customer_name'] . ")";
                    
                    $cashTx->record(
                        $defaultBankId,
                        'PENJUALAN_HEWAN',
                        'sale_payments',
                        $id,
                        $desc,
                        $payment['payment_amount'], // cash_in
                        0,                          // cash_out
                        $_SESSION['user_id'] ?? 1
                    );
                }
            }

            $query = "UPDATE " . $this->table . " SET payment_status = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$status, $id]);

            $this->conn->commit();

            // Recalculate balance
            require_once 'models/Sale.php';
            $saleModel = new Sale($this->conn);
            $saleModel->recalculateBalance($payment['sale_id']);

            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $payment = $this->getById($id);
        if (!$payment) return false;

        $this->conn->beginTransaction();
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $this->conn->commit();

            // Recalculate balance
            require_once 'models/Sale.php';
            $saleModel = new Sale($this->conn);
            $saleModel->recalculateBalance($payment['sale_id']);

            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
