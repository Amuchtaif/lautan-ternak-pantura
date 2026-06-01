<?php
require_once 'models/CashTransaction.php';

class OperationalExpense {
    private $conn;
    private $table = 'operational_expenses';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->conn->beginTransaction();
        try {
            if (empty($data['category_id'])) {
                throw new Exception("Kategori pengeluaran harus dipilih.");
            }
            if (empty($data['cash_account_id'])) {
                throw new Exception("Rekening kas sumber harus dipilih.");
            }
            if (empty($data['amount']) || floatval($data['amount']) <= 0) {
                throw new Exception("Nominal pengeluaran harus lebih dari 0.");
            }
            if (empty($data['date'])) {
                throw new Exception("Tanggal transaksi harus ditentukan.");
            }

            // Verify cash account balance
            $stmt = $this->conn->prepare("SELECT current_balance FROM cash_accounts WHERE id = ?");
            $stmt->execute([$data['cash_account_id']]);
            $currentBalance = $stmt->fetchColumn() ?: 0;
            if ($currentBalance < floatval($data['amount'])) {
                throw new Exception("Saldo kas tidak mencukupi untuk melakukan pengeluaran ini. Saldo tersedia: Rp " . number_format($currentBalance, 0, ',', '.'));
            }

            $query = "INSERT INTO " . $this->table . " 
                (category_id, cash_account_id, date, amount, description, attachment) 
                VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['category_id'],
                $data['cash_account_id'],
                $data['date'],
                $data['amount'],
                $data['description'] ?? null,
                $data['attachment'] ?? null
            ]);

            $expenseId = $this->conn->lastInsertId();

            // Record into central ledger
            $ledger = new CashTransaction($this->conn);
            $desc = "Biaya Operasional (" . ($data['category_name'] ?? 'Kategori ID: ' . $data['category_id']) . ")";
            if (!empty($data['description'])) {
                $desc .= " - " . $data['description'];
            }

            $ledger->record(
                $data['cash_account_id'],
                'OPERASIONAL',
                'operational_expenses',
                $expenseId,
                $desc,
                0,               // cash_in
                $data['amount'], // cash_out
                $data['created_by'],
                $data['date'] . ' ' . date('H:i:s')
            );

            $this->conn->commit();
            return $expenseId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function delete($id, $userId) {
        $expense = $this->getById($id);
        if (!$expense) {
            throw new Exception("Data pengeluaran tidak ditemukan.");
        }

        $this->conn->beginTransaction();
        try {
            // Reverse the ledger cash deduction
            $ledger = new CashTransaction($this->conn);
            $desc = "PEMBATALAN / HAPUS: Biaya Operasional (" . $expense['category_name'] . ")";

            $ledger->record(
                $expense['cash_account_id'],
                'OPERASIONAL',
                'operational_expenses',
                $id,
                $desc,
                $expense['amount'], // cash_in (reversing the outflow)
                0,                  // cash_out
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

    public function getAll($categoryId = null, $startDate = null, $endDate = null) {
        $query = "SELECT e.*, c.name as category_name, a.name as account_name
                 FROM " . $this->table . " e
                 JOIN operational_categories c ON e.category_id = c.id
                 JOIN cash_accounts a ON e.cash_account_id = a.id
                 WHERE 1=1";
        $params = [];

        if ($categoryId) {
            $query .= " AND e.category_id = ?";
            $params[] = $categoryId;
        }

        if ($startDate) {
            $query .= " AND DATE(e.date) >= DATE(?)";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND DATE(e.date) <= DATE(?)";
            $params[] = $endDate;
        }

        $query .= " ORDER BY e.date DESC, e.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT e.*, c.name as category_name, a.name as account_name
                 FROM " . $this->table . " e
                 JOIN operational_categories c ON e.category_id = c.id
                 JOIN cash_accounts a ON e.cash_account_id = a.id
                 WHERE e.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCurrentMonthTotal() {
        $stmt = $this->conn->query("
            SELECT SUM(amount) 
            FROM " . $this->table . " 
            WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())
        ");
        return $stmt->fetchColumn() ?: 0;
    }
}
