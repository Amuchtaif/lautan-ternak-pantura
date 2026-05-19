<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDailySummary($date) {
        $summary = [
            'total_transactions' => 0,
            'total_revenue' => 0,
            'completed_orders' => 0,
            'pending_orders' => 0,
            'top_selling' => []
        ];

        // Total transactions today
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = DATE(?)");
        $stmt->execute([$date]);
        $summary['total_transactions'] = $stmt->fetchColumn();

        // Total revenue today (paid/completed/processing/delivered)
        $stmt = $this->conn->prepare("SELECT SUM(total_price) FROM orders WHERE DATE(created_at) = DATE(?) AND status IN ('paid', 'processing', 'delivered', 'completed')");
        $stmt->execute([$date]);
        $summary['total_revenue'] = $stmt->fetchColumn() ?: 0;

        // Completed orders
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = DATE(?) AND status = 'completed'");
        $stmt->execute([$date]);
        $summary['completed_orders'] = $stmt->fetchColumn();

        // Pending orders
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = DATE(?) AND status = 'pending'");
        $stmt->execute([$date]);
        $summary['pending_orders'] = $stmt->fetchColumn();

        // Top selling products today
        $stmt = $this->conn->prepare("
            SELECT l.name as product_name, l.code as product_code, SUM(o.qty) as total_sold, SUM(o.total_price) as total_sales_value
            FROM orders o
            JOIN livestock l ON o.livestock_id = l.id
            WHERE DATE(o.created_at) = DATE(?) AND o.status IN ('paid', 'processing', 'delivered', 'completed')
            GROUP BY o.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ");
        $stmt->execute([$date]);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }

    public function getMonthlySummary($yearMonth) {
        $summary = [
            'total_transactions' => 0,
            'total_revenue' => 0,
            'chart_data' => [],
            'payment_stats' => [],
            'top_selling' => []
        ];

        // Total transactions this month
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
        $summary['total_transactions'] = $stmt->fetchColumn();

        // Total revenue this month
        $stmt = $this->conn->prepare("SELECT SUM(total_price) FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND status IN ('paid', 'processing', 'delivered', 'completed')");
        $stmt->execute([$yearMonth]);
        $summary['total_revenue'] = $stmt->fetchColumn() ?: 0;

        // Chart data (daily revenue for this month)
        $stmt = $this->conn->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as sale_date, SUM(total_price) as revenue, COUNT(*) as trx_count
            FROM orders
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND status IN ('paid', 'processing', 'delivered', 'completed')
            GROUP BY DATE(created_at)
            ORDER BY sale_date ASC
        ");
        $stmt->execute([$yearMonth]);
        $summary['chart_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Payment status breakdown
        $stmt = $this->conn->prepare("
            SELECT status, COUNT(*) as count, SUM(total_price) as total_value
            FROM orders
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
            GROUP BY status
        ");
        $stmt->execute([$yearMonth]);
        $summary['payment_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top selling products this month
        $stmt = $this->conn->prepare("
            SELECT l.name as product_name, l.code as product_code, SUM(o.qty) as total_sold, SUM(o.total_price) as total_sales_value
            FROM orders o
            JOIN livestock l ON o.livestock_id = l.id
            WHERE DATE_FORMAT(o.created_at, '%Y-%m') = ? AND o.status IN ('paid', 'processing', 'delivered', 'completed')
            GROUP BY o.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ");
        $stmt->execute([$yearMonth]);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }
}
