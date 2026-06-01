<?php

class Investor {
    private $conn;
    private $table = 'investors';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        if (empty($data['name'])) {
            throw new Exception("Nama investor wajib diisi.");
        }

        $query = "INSERT INTO " . $this->table . " (name, phone, address) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);
    }

    public function update($id, $data) {
        if (empty($data['name'])) {
            throw new Exception("Nama investor wajib diisi.");
        }

        $query = "UPDATE " . $this->table . " SET name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        // Safe check: verify if investor has active funds before delete
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM investor_funds WHERE investor_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Investor tidak dapat dihapus karena memiliki riwayat setoran modal investasi.");
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table . " ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
