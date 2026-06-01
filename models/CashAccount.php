<?php

class CashAccount {
    private $conn;
    private $table = 'cash_accounts';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
            (name, type, account_number, bank_name, opening_balance, current_balance, status, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $opening = floatval($data['opening_balance'] ?? 0);
        
        return $stmt->execute([
            $data['name'],
            $data['type'], // 'cash', 'bank'
            $data['account_number'] ?? null,
            $data['bank_name'] ?? null,
            $opening,
            $opening, // Initial current balance is same as opening balance
            $data['status'] ?? 'active',
            $data['description'] ?? null
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
            name = ?, 
            type = ?, 
            account_number = ?, 
            bank_name = ?, 
            status = ?, 
            description = ? 
            WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['type'],
            $data['account_number'] ?? null,
            $data['bank_name'] ?? null,
            $data['status'],
            $data['description'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        // Safe check: verify if account has cash transactions before delete
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cash_transactions WHERE cash_account_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Rekening kas tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table . " ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveAccounts() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table . " WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
