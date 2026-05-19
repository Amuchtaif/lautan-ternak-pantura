<?php
class Purchase {
    private $conn;
    private $table = 'purchases';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
            (livestock_name, breed, weight, purchase_price, selling_price, qty, notes, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['livestock_name'],
            $data['breed'],
            $data['weight'],
            $data['purchase_price'],
            $data['selling_price'],
            $data['qty'],
            $data['notes'] ?? null,
            $data['created_by']
        ]);
    }

    public function getAll() {
        $query = "SELECT p.*, u.name as admin_name 
                 FROM " . $this->table . " p
                 JOIN users u ON p.created_by = u.id
                 ORDER BY p.purchased_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET livestock_name = ?, breed = ?, weight = ?, purchase_price = ?, selling_price = ?, qty = ?, notes = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['livestock_name'],
            $data['breed'],
            $data['weight'],
            $data['purchase_price'],
            $data['selling_price'],
            $data['qty'],
            $data['notes'],
            $id
        ]);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
