<?php
// Savings Model - Lautan Ternak Pantura

class Savings {
    private $conn;
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

    /**
     * Calculates total verified savings deposited into a plan.
     */
    public function getVerifiedTotal($planId) {
        $planColumn = $this->hasColumn('savings_transactions', 'savings_plan_id') ? 'savings_plan_id' : 'plan_id';
        $statusColumn = $this->hasColumn('savings_transactions', 'transaction_status') ? 'transaction_status' : 'status';
        $query = "SELECT SUM(amount) FROM savings_transactions WHERE {$planColumn} = ? AND {$statusColumn} = 'verified'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$planId]);
        return floatval($stmt->fetchColumn() ?: 0);
    }

    /**
     * Automatically completes a savings plan if the total verified savings meets or exceeds the target amount.
     */
    public function checkAndCompletePlan($planId) {
        // 1. Fetch target amount
        $query = "SELECT target_amount FROM savings_plans WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$planId]);
        $target = floatval($stmt->fetchColumn() ?: 0);

        if ($target <= 0) return false;

        // 2. Fetch total verified savings
        $totalVerified = $this->getVerifiedTotal($planId);

        // 3. Complete plan if target is met or exceeded
        if ($totalVerified >= $target) {
            if ($this->hasColumn('savings_plans', 'current_amount')) {
                $queryUp = "UPDATE savings_plans SET current_amount = ?, status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmtUp = $this->conn->prepare($queryUp);
                return $stmtUp->execute([$totalVerified, $planId]);
            }

            $queryUp = "UPDATE savings_plans SET status = 'completed' WHERE id = ?";
            $stmtUp = $this->conn->prepare($queryUp);
            return $stmtUp->execute([$planId]);
        }
        return false;
    }
}
