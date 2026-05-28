<?php

class SavingsTransaction {
    private $conn;
    private $table = 'savings_transactions';
    private $columnCache = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    private function hasColumn($table, $column) {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
            $stmt->execute([$column]);
            $this->columnCache[$key] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $stmt = $this->conn->query("PRAGMA table_info({$table})");
            $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $this->columnCache[$key] = in_array($column, array_column($columns, 'name'), true);
        }

        return $this->columnCache[$key];
    }

    private function usesNewSchema() {
        return $this->hasColumn($this->table, 'savings_plan_id');
    }

    private function planCodeExpr() {
        return $this->hasColumn('savings_plans', 'plan_code')
            ? 'sp.plan_code'
            : "CONCAT('TQ-OLD-', LPAD(sp.id, 6, '0'))";
    }

    private function livestockTargetExpr() {
        return $this->hasColumn('savings_plans', 'livestock_target')
            ? 'sp.livestock_target'
            : "'Hewan Qurban'";
    }

    private function transactionSelectFields() {
        if ($this->usesNewSchema()) {
            return 'st.*';
        }

        return "
            st.id,
            st.plan_id AS savings_plan_id,
            st.amount,
            'transfer_bank' AS payment_method,
            st.proof_of_payment AS payment_proof,
            st.status AS transaction_status,
            st.verified_by,
            NULL AS verified_at,
            NULL AS notes,
            st.created_at
        ";
    }

    public function createDeposit($data) {
        if (!$this->usesNewSchema()) {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table}
                (plan_id, amount, proof_of_payment, status)
                VALUES (?, ?, ?, 'pending')
            ");

            $stmt->execute([
                $data['savings_plan_id'],
                $data['amount'],
                $data['payment_proof']
            ]);

            return $this->conn->lastInsertId();
        }

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table}
            (savings_plan_id, amount, payment_method, payment_proof, transaction_status, notes)
            VALUES (?, ?, ?, ?, 'pending', ?)
        ");

        $stmt->execute([
            $data['savings_plan_id'],
            $data['amount'],
            $data['payment_method'],
            $data['payment_proof'],
            $data['notes'] ?? null
        ]);

        return $this->conn->lastInsertId();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT " . $this->transactionSelectFields() . " FROM {$this->table} st WHERE st.id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByPlan($planId) {
        $planColumn = $this->usesNewSchema() ? 'savings_plan_id' : 'plan_id';
        $stmt = $this->conn->prepare("
            SELECT " . $this->transactionSelectFields() . ", u.name AS verifier_name
            FROM {$this->table} st
            LEFT JOIN users u ON st.verified_by = u.id
            WHERE st.{$planColumn} = ?
            ORDER BY st.created_at DESC
        ");
        $stmt->execute([(int)$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentByCustomer($customerId, $limit = 5) {
        $planColumn = $this->usesNewSchema() ? 'savings_plan_id' : 'plan_id';
        $stmt = $this->conn->prepare("
            SELECT " . $this->transactionSelectFields() . ", " . $this->planCodeExpr() . " AS plan_code, " . $this->livestockTargetExpr() . " AS livestock_target
            FROM {$this->table} st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            WHERE sp.customer_id = ?
            ORDER BY st.created_at DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute([(int)$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($filters = []) {
        $where = [];
        $params = [];
        $planColumn = $this->usesNewSchema() ? 'savings_plan_id' : 'plan_id';
        $statusColumn = $this->usesNewSchema() ? 'transaction_status' : 'status';

        if (!empty($filters['status'])) {
            $where[] = 'st.' . $statusColumn . ' = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $where[] = 'DATE(st.created_at) = ?';
            $params[] = $filters['date'];
        }

        if (!empty($filters['customer'])) {
            $where[] = $this->hasColumn('savings_plans', 'plan_code')
                ? '(u.name LIKE ? OR u.email LIKE ? OR sp.plan_code LIKE ?)'
                : '(u.name LIKE ? OR u.email LIKE ? OR CAST(sp.id AS CHAR) LIKE ?)';
            $keyword = '%' . $filters['customer'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql = "
            SELECT " . $this->transactionSelectFields() . ", " . $this->planCodeExpr() . " AS plan_code, sp.target_amount,
                " . ($this->hasColumn('savings_plans', 'current_amount') ? 'sp.current_amount' : '0') . " AS current_amount,
                " . $this->livestockTargetExpr() . " AS livestock_target,
                u.name AS customer_name, u.email AS customer_email
            FROM {$this->table} st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
        ";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY st.created_at DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPending() {
        $statusColumn = $this->usesNewSchema() ? 'transaction_status' : 'status';
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE {$statusColumn} = 'pending'");
        return (int)$stmt->fetchColumn();
    }

    public function verify($id, $status, $adminId, $notes = null) {
        if (!in_array($status, ['verified', 'rejected'], true)) {
            throw new InvalidArgumentException('Status transaksi tidak valid.');
        }

        if ($this->usesNewSchema()) {
            $stmt = $this->conn->prepare("
                UPDATE {$this->table}
                SET transaction_status = ?, verified_by = ?, verified_at = CURRENT_TIMESTAMP, notes = COALESCE(?, notes)
                WHERE id = ? AND transaction_status = 'pending'
            ");
            $stmt->execute([$status, $adminId, $notes, (int)$id]);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE {$this->table}
                SET status = ?, verified_by = ?
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->execute([$status, $adminId, (int)$id]);
        }

        return $stmt->rowCount() > 0;
    }
}
