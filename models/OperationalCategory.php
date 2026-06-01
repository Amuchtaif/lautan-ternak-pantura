<?php

class OperationalCategory {
    private $conn;
    private $table = 'operational_categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name) {
        $name = trim($name);
        if (empty($name)) {
            throw new Exception("Nama kategori wajib diisi.");
        }

        $query = "INSERT INTO " . $this->table . " (name) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name]);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table . " ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
