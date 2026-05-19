<?php
class Payment {
    private $conn;
    private $table = 'payments';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
            (order_id, amount, payment_method, payment_proof, payment_status) 
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $data['order_id'],
            $data['amount'],
            $data['payment_method'],
            $data['payment_proof'],
            $data['payment_status'] ?? 'pending'
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getByOrderId($orderId) {
        $query = "SELECT p.*, u.name as verifier_name 
                 FROM " . $this->table . " p
                 LEFT JOIN users u ON p.verified_by = u.id
                 WHERE p.order_id = ?
                 ORDER BY p.created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verify($id, $verifiedBy, $status) {
        $query = "UPDATE " . $this->table . " SET 
            payment_status = ?, 
            verified_by = ?, 
            verified_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $verifiedBy, $id]);
    }
}
