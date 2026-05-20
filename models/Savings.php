<?php
// Savings Model - Lautan Ternak Pantura

class Savings {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Calculates total verified savings deposited into a plan.
     */
    public function getVerifiedTotal($planId) {
        $query = "SELECT SUM(amount) FROM savings_transactions WHERE plan_id = ? AND status = 'verified'";
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
            $queryUp = "UPDATE savings_plans SET status = 'completed' WHERE id = ?";
            $stmtUp = $this->conn->prepare($queryUp);
            return $stmtUp->execute([$planId]);
        }
        return false;
    }
}
