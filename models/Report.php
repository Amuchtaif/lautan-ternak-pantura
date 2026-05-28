<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDailySummary($date) {
        $summary = [
            'total_transactions' => 0,
            'total_dp_transactions' => 0,
            'total_lunas_transactions' => 0,
            'cash_received' => 0,
            'total_revenue' => 0,
            'total_receivables' => 0,
            'purchase_expense' => 0,
            'total_payables' => 0,
            'net_margin' => 0,
            'completed_orders' => 0,
            'pending_orders' => 0,
            'top_selling' => []
        ];

        // Total sales transactions today
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?)");
        $stmt->execute([$date]);
        $summary['total_transactions'] = $stmt->fetchColumn();

        // Total DP sales today
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND payment_type = 'dp'");
        $stmt->execute([$date]);
        $summary['total_dp_transactions'] = $stmt->fetchColumn();

        // Total Lunas sales today
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND payment_type = 'lunas'");
        $stmt->execute([$date]);
        $summary['total_lunas_transactions'] = $stmt->fetchColumn();

        // Cash received today (any verified payments paid today)
        $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM sale_payments WHERE DATE(payment_date) = DATE(?) AND payment_status = 'verified'");
        $stmt->execute([$date]);
        $summary['cash_received'] = $stmt->fetchColumn() ?: 0;
        $summary['total_revenue'] = $summary['cash_received'];

        // Total receivables for transactions created today (Total price - Verified payments for today's orders)
        $stmt = $this->conn->prepare("SELECT SUM(total_price) FROM sales WHERE DATE(created_at) = DATE(?)");
        $stmt->execute([$date]);
        $totalPriceToday = $stmt->fetchColumn() ?: 0;

        $stmt = $this->conn->prepare("SELECT SUM(sp.payment_amount) FROM sale_payments sp JOIN sales s ON sp.sale_id = s.id WHERE DATE(s.created_at) = DATE(?) AND sp.payment_status = 'verified'");
        $stmt->execute([$date]);
        $totalPaidTodayOrders = $stmt->fetchColumn() ?: 0;

        $summary['total_receivables'] = max(0, $totalPriceToday - $totalPaidTodayOrders);

        // Purchase cash outflows registered today
        $stmt = $this->conn->prepare("SELECT SUM(amount_paid) FROM purchases WHERE DATE(purchased_at) = DATE(?)");
        $stmt->execute([$date]);
        $summary['purchase_expense'] = $stmt->fetchColumn() ?: 0;

        // Purchase payables created today
        $stmt = $this->conn->prepare("SELECT SUM(total_purchase - amount_paid) FROM purchases WHERE DATE(purchased_at) = DATE(?)");
        $stmt->execute([$date]);
        $summary['total_payables'] = $stmt->fetchColumn() ?: 0;

        // Net margin/surplus today
        $summary['net_margin'] = $summary['total_revenue'] - $summary['purchase_expense'];

        // Completed sales status
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND sale_status = 'completed'");
        $stmt->execute([$date]);
        $summary['completed_orders'] = $stmt->fetchColumn();

        // Pending sales status
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND sale_status = 'pending'");
        $stmt->execute([$date]);
        $summary['pending_orders'] = $stmt->fetchColumn();

        // Top selling breeds today
        $stmt = $this->conn->prepare("
            SELECT s.livestock_name as product_name, l.livestock_code as product_code, SUM(s.qty) as total_sold, SUM(s.total_price) as total_sales_value
            FROM sales s
            LEFT JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE(s.created_at) = DATE(?)
            GROUP BY s.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ");
        $stmt->execute([$date]);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }

    public function getMonthlySummary($yearMonth) {
        $summary = [
            'total_transactions' => 0,
            'total_dp_transactions' => 0,
            'total_lunas_transactions' => 0,
            'cash_received' => 0,
            'total_revenue' => 0,
            'total_receivables' => 0,
            'purchase_expense' => 0,
            'total_payables' => 0,
            'net_margin' => 0,
            'chart_data' => [],
            'payment_stats' => [],
            'top_selling' => []
        ];

        // Total sales transactions this month
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
        $summary['total_transactions'] = $stmt->fetchColumn();

        // Total DP sales this month
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND payment_type = 'dp'");
        $stmt->execute([$yearMonth]);
        $summary['total_dp_transactions'] = $stmt->fetchColumn();

        // Total Lunas sales this month
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND payment_type = 'lunas'");
        $stmt->execute([$yearMonth]);
        $summary['total_lunas_transactions'] = $stmt->fetchColumn();

        // Cash received this month (verified payments made this month)
        $stmt = $this->conn->prepare("SELECT SUM(payment_amount) FROM sale_payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? AND payment_status = 'verified'");
        $stmt->execute([$yearMonth]);
        $summary['cash_received'] = $stmt->fetchColumn() ?: 0;
        $summary['total_revenue'] = $summary['cash_received'];

        // Total receivables for transactions created this month (Total price - Verified payments of this month's orders)
        $stmt = $this->conn->prepare("SELECT SUM(total_price) FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
        $totalPriceMonth = $stmt->fetchColumn() ?: 0;

        $stmt = $this->conn->prepare("SELECT SUM(sp.payment_amount) FROM sale_payments sp JOIN sales s ON sp.sale_id = s.id WHERE DATE_FORMAT(s.created_at, '%Y-%m') = ? AND sp.payment_status = 'verified'");
        $stmt->execute([$yearMonth]);
        $totalPaidMonthOrders = $stmt->fetchColumn() ?: 0;

        $summary['total_receivables'] = max(0, $totalPriceMonth - $totalPaidMonthOrders);

        // Purchase cash outflows registered this month
        $stmt = $this->conn->prepare("SELECT SUM(amount_paid) FROM purchases WHERE DATE_FORMAT(purchased_at, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
        $summary['purchase_expense'] = $stmt->fetchColumn() ?: 0;

        // Purchase payables created this month
        $stmt = $this->conn->prepare("SELECT SUM(total_purchase - amount_paid) FROM purchases WHERE DATE_FORMAT(purchased_at, '%Y-%m') = ?");
        $stmt->execute([$yearMonth]);
        $summary['total_payables'] = $stmt->fetchColumn() ?: 0;

        // Net margin/surplus this month
        $summary['net_margin'] = $summary['total_revenue'] - $summary['purchase_expense'];

        // Chart data: daily cash flow (cash received) for this month
        $stmt = $this->conn->prepare("
            SELECT DATE_FORMAT(payment_date, '%Y-%m-%d') as sale_date, SUM(payment_amount) as revenue, COUNT(*) as trx_count
            FROM sale_payments
            WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? AND payment_status = 'verified'
            GROUP BY DATE(payment_date)
            ORDER BY sale_date ASC
        ");
        $stmt->execute([$yearMonth]);
        $summary['chart_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Payment status breakdown
        $stmt = $this->conn->prepare("
            SELECT payment_status as status, COUNT(*) as count, SUM(total_price) as total_value
            FROM sales
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
            GROUP BY payment_status
        ");
        $stmt->execute([$yearMonth]);
        $summary['payment_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top selling products this month
        $stmt = $this->conn->prepare("
            SELECT s.livestock_name as product_name, l.livestock_code as product_code, SUM(s.qty) as total_sold, SUM(s.total_price) as total_sales_value
            FROM sales s
            LEFT JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE_FORMAT(s.created_at, '%Y-%m') = ?
            GROUP BY s.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ");
        $stmt->execute([$yearMonth]);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }
}
