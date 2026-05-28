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
