<?php

class SavingsReport {
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

    private function txPlanColumn() {
        return $this->hasColumn('savings_transactions', 'savings_plan_id') ? 'savings_plan_id' : 'plan_id';
    }

    private function txStatusColumn() {
        return $this->hasColumn('savings_transactions', 'transaction_status') ? 'transaction_status' : 'status';
    }

    private function planCodeExpr() {
        return $this->hasColumn('savings_plans', 'plan_code')
            ? 'sp.plan_code'
            : "CONCAT('TQ-OLD-', LPAD(sp.id, 6, '0'))";
    }

    private function paymentMethodExpr() {
        return $this->hasColumn('savings_transactions', 'payment_method') ? 'payment_method' : "'transfer_bank'";
    }

    public function getDailyReport($date, $status = '', $customer = '') {
        $where = ['DATE(st.created_at) = ?'];
        $params = [$date];
        $planColumn = $this->txPlanColumn();
        $statusColumn = $this->txStatusColumn();
        $planCodeExpr = $this->planCodeExpr();

        if ($status !== '') {
            $where[] = 'st.' . $statusColumn . ' = ?';
            $params[] = $status;
        }

        if ($customer !== '') {
            $where[] = $this->hasColumn('savings_plans', 'plan_code')
                ? '(u.name LIKE ? OR u.email LIKE ? OR sp.plan_code LIKE ?)'
                : '(u.name LIKE ? OR u.email LIKE ? OR CAST(sp.id AS CHAR) LIKE ?)';
            $keyword = '%' . $customer . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $stmt = $this->conn->prepare("
            SELECT
                DATE(st.created_at) AS report_date,
                COUNT(*) AS transaction_count,
                COALESCE(SUM(CASE WHEN st.{$statusColumn} = 'verified' THEN st.amount ELSE 0 END), 0) AS verified_amount,
                COUNT(CASE WHEN st.{$statusColumn} = 'pending' THEN 1 END) AS pending_count,
                COUNT(CASE WHEN st.{$statusColumn} = 'verified' THEN 1 END) AS verified_count,
                COUNT(CASE WHEN st.{$statusColumn} = 'rejected' THEN 1 END) AS rejected_count
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY DATE(st.created_at)
        ");
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        $detailStmt = $this->conn->prepare("
            SELECT st.*, st.{$statusColumn} AS transaction_status, {$planCodeExpr} AS plan_code, u.name AS customer_name
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY st.created_at DESC
        ");
        $detailStmt->execute($params);

        return [
            'summary' => $summary ?: [
                'report_date' => $date,
                'transaction_count' => 0,
                'verified_amount' => 0,
                'pending_count' => 0,
                'verified_count' => 0,
                'rejected_count' => 0
            ],
            'transactions' => $detailStmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function getMonthlyReport($month, $status = '', $customer = '') {
        $where = ["DATE_FORMAT(st.created_at, '%Y-%m') = ?"];
        $params = [$month];
        $planColumn = $this->txPlanColumn();
        $statusColumn = $this->txStatusColumn();
        $paymentMethod = $this->paymentMethodExpr();

        if ($status !== '') {
            $where[] = 'st.' . $statusColumn . ' = ?';
            $params[] = $status;
        }

        if ($customer !== '') {
            $where[] = $this->hasColumn('savings_plans', 'plan_code')
                ? '(u.name LIKE ? OR u.email LIKE ? OR sp.plan_code LIKE ?)'
                : '(u.name LIKE ? OR u.email LIKE ? OR CAST(sp.id AS CHAR) LIKE ?)';
            $keyword = '%' . $customer . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $summaryStmt = $this->conn->prepare("
            SELECT
                DATE_FORMAT(st.created_at, '%Y-%m') AS report_month,
                COALESCE(SUM(CASE WHEN st.{$statusColumn} = 'verified' THEN st.amount ELSE 0 END), 0) AS total_collected,
                COUNT(DISTINCT sp.customer_id) AS active_customers,
                COUNT(*) AS transaction_count,
                COUNT(CASE WHEN st.{$statusColumn} = 'verified' THEN 1 END) AS verified_count,
                COUNT(CASE WHEN st.{$statusColumn} = 'pending' THEN 1 END) AS pending_count,
                COUNT(CASE WHEN st.{$statusColumn} = 'rejected' THEN 1 END) AS rejected_count
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY DATE_FORMAT(st.created_at, '%Y-%m')
        ");
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

        $growthStmt = $this->conn->prepare("
            SELECT
                DATE_FORMAT(st.created_at, '%Y-%m-%d') AS period,
                COALESCE(SUM(CASE WHEN st.{$statusColumn} = 'verified' THEN st.amount ELSE 0 END), 0) AS amount,
                COUNT(*) AS transaction_count
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY DATE_FORMAT(st.created_at, '%Y-%m-%d')
            ORDER BY period ASC
        ");
        $growthStmt->execute($params);

        $paymentStmt = $this->conn->prepare("
            SELECT {$paymentMethod} AS payment_method, COUNT(*) AS count, COALESCE(SUM(amount), 0) AS amount
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$planColumn} = sp.id
            JOIN users u ON sp.customer_id = u.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY payment_method
            ORDER BY amount DESC
        ");
        $paymentStmt->execute($params);

        $completionStmt = $this->conn->query("
            SELECT
                COUNT(*) AS total_plans,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed_plans
            FROM savings_plans
        ");
        $completion = $completionStmt->fetch(PDO::FETCH_ASSOC);
        $completionRate = ((int)$completion['total_plans'] > 0)
            ? round(((int)$completion['completed_plans'] / (int)$completion['total_plans']) * 100, 2)
            : 0;

        return [
            'summary' => $summary ?: [
                'report_month' => $month,
                'total_collected' => 0,
                'active_customers' => 0,
                'transaction_count' => 0,
                'verified_count' => 0,
                'pending_count' => 0,
                'rejected_count' => 0
            ],
            'growth' => $growthStmt->fetchAll(PDO::FETCH_ASSOC),
            'payment_stats' => $paymentStmt->fetchAll(PDO::FETCH_ASSOC),
            'completion_rate' => $completionRate
        ];
    }
}
