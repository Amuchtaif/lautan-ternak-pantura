<?php
require_once 'models/CashTransaction.php';

class InvestorFund {
    private $conn;
    private $table = 'investor_funds';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->conn->beginTransaction();
        try {
            if (empty($data['investor_id'])) {
                throw new Exception("Investor harus dipilih.");
            }
            if (empty($data['cash_account_id'])) {
                throw new Exception("Rekening kas penerima harus dipilih.");
            }
            if (empty($data['amount']) || floatval($data['amount']) <= 0) {
                throw new Exception("Nominal investasi harus lebih dari 0.");
            }
            if (empty($data['date'])) {
                throw new Exception("Tanggal setor harus ditentukan.");
            }

            $query = "INSERT INTO " . $this->table . " 
                (investor_id, cash_account_id, date, amount, proof, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['investor_id'],
                $data['cash_account_id'],
                $data['date'],
                $data['amount'],
                $data['proof'] ?? null,
                $data['description'] ?? null,
                $data['status'] ?? 'active'
            ]);

            $fundId = $this->conn->lastInsertId();

            // Record into central ledger
            $ledger = new CashTransaction($this->conn);
            $desc = "Modal Masuk Investor - " . ($data['investor_name'] ?? 'Investor ID: ' . $data['investor_id']);
            if (!empty($data['description'])) {
                $desc .= " (" . $data['description'] . ")";
            }

            $ledger->record(
                $data['cash_account_id'],
                'MODAL_INVESTOR',
                'investor_funds',
                $fundId,
                $desc,
                $data['amount'], // cash_in
                0,               // cash_out
                $data['created_by'],
                $data['date'] . ' ' . date('H:i:s')
            );

            $this->conn->commit();
            return $fundId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function updateStatus($id, $status) {
        if (!in_array($status, ['active', 'completed'])) {
            throw new Exception("Status tidak valid.");
        }

        $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function delete($id, $userId) {
        $fund = $this->getById($id);
        if (!$fund) {
            throw new Exception("Data modal investor tidak ditemukan.");
        }

        $this->conn->beginTransaction();
        try {
            // Safe check: reverse the cash balance change
            $ledger = new CashTransaction($this->conn);
            $desc = "PEMBATALAN / HAPUS: Modal Masuk Investor - " . $fund['investor_name'];

            $ledger->record(
                $fund['cash_account_id'],
                'MODAL_INVESTOR',
                'investor_funds',
                $id,
                $desc,
                0,                  // cash_in
                $fund['amount'],    // cash_out (reversing the inflow)
                $userId
            );

            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getAll($investorId = null) {
        $query = "SELECT f.*, i.name as investor_name, i.phone as investor_phone, a.name as account_name
                 FROM " . $this->table . " f
                 JOIN investors i ON f.investor_id = i.id
                 JOIN cash_accounts a ON f.cash_account_id = a.id";
        $params = [];

        if ($investorId) {
            $query .= " WHERE f.investor_id = ?";
            $params[] = $investorId;
        }

        $query .= " ORDER BY f.date DESC, f.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT f.*, i.name as investor_name, i.phone as investor_phone, a.name as account_name
                 FROM " . $this->table . " f
                 JOIN investors i ON f.investor_id = i.id
                 JOIN cash_accounts a ON f.cash_account_id = a.id
                 WHERE f.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalActiveCapital() {
        $stmt = $this->conn->query("SELECT SUM(amount) FROM " . $this->table . " WHERE status = 'active'");
        return $stmt->fetchColumn() ?: 0;
    }
}
