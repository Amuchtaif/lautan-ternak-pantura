<?php
class Livestock {
    private $conn;
    private $table = 'livestock';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAvailable($filter_type = '', $filter_category = '') {
        $query = "SELECT l.*, u.name as breeder_name FROM " . $this->table . " l JOIN users u ON l.breeder_id = u.id WHERE l.status = 'available'";

        if ($filter_type) $query .= " AND l.type = :type";
        if ($filter_category) $query .= " AND l.category = :category";

        $stmt = $this->conn->prepare($query);

        if ($filter_type) $stmt->bindParam(':type', $filter_type);
        if ($filter_category) $stmt->bindParam(':category', $filter_category);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT l.*, u.name as breeder_name, u.email as breeder_email FROM " . $this->table . " l JOIN users u ON l.breeder_id = u.id WHERE l.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
