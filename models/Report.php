<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDailySummary($date, $status = '') {
        $summary = [
            'total_transactions' => 0,
            'total_sales' => 0,
            'total_purchase_cost' => 0,
            'total_margin' => 0,
            'completed_orders' => 0,
            'pending_orders' => 0,
            'top_selling' => []
        ];

        // 1. Calculate main aggregated metrics (total_transactions, total_sales, total_purchase_cost, total_margin)
        $params = [$date];
        $statusQuery = "";
        if ($status !== '') {
            $statusQuery = " AND s.sale_status = ?";
            $params[] = $status;
        }

        $query = "
            SELECT 
                COUNT(s.id) as total_transactions,
                SUM(s.total_price) as total_sales,
                SUM(l.purchase_price * s.qty) as total_purchase_cost,
                SUM(s.total_price - (l.purchase_price * s.qty)) as total_margin
            FROM sales s
            JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE(s.created_at) = DATE(?)" . $statusQuery;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $summary['total_transactions'] = (int)($res['total_transactions'] ?? 0);
            $summary['total_sales'] = (float)($res['total_sales'] ?? 0);
            $summary['total_purchase_cost'] = (float)($res['total_purchase_cost'] ?? 0);
            $summary['total_margin'] = (float)($res['total_margin'] ?? 0);
        }

        // 2. Count completed and pending orders for chart distribution (honoring the status filter if applied)
        $completedQuery = "SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND sale_status = 'completed'";
        $completedParams = [$date];
        if ($status !== '') {
            $completedQuery .= " AND sale_status = ?";
            $completedParams[] = $status;
        }
        $stmt = $this->conn->prepare($completedQuery);
        $stmt->execute($completedParams);
        $summary['completed_orders'] = (int)$stmt->fetchColumn();

        $pendingQuery = "SELECT COUNT(*) FROM sales WHERE DATE(created_at) = DATE(?) AND sale_status = 'pending'";
        $pendingParams = [$date];
        if ($status !== '') {
            $pendingQuery .= " AND sale_status = ?";
            $pendingParams[] = $status;
        }
        $stmt = $this->conn->prepare($pendingQuery);
        $stmt->execute($pendingParams);
        $summary['pending_orders'] = (int)$stmt->fetchColumn();

        // 3. Top selling breeds today
        $topSellingParams = [$date];
        $topSellingStatusQuery = "";
        if ($status !== '') {
            $topSellingStatusQuery = " AND s.sale_status = ?";
            $topSellingParams[] = $status;
        }
        $topSellingQuery = "
            SELECT s.livestock_name as product_name, l.livestock_code as product_code, SUM(s.qty) as total_sold, SUM(s.total_price) as total_sales_value
            FROM sales s
            LEFT JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE(s.created_at) = DATE(?)" . $topSellingStatusQuery . "
            GROUP BY s.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ";
        $stmt = $this->conn->prepare($topSellingQuery);
        $stmt->execute($topSellingParams);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }

    public function getMonthlySummary($yearMonth, $status = '') {
        $summary = [
            'total_transactions' => 0,
            'total_sales' => 0,
            'total_purchase_cost' => 0,
            'total_margin' => 0,
            'chart_data' => [],
            'payment_stats' => [],
            'top_selling' => []
        ];

        // 1. Calculate main aggregated metrics (total_transactions, total_sales, total_purchase_cost, total_margin)
        $params = [$yearMonth];
        $statusQuery = "";
        if ($status !== '') {
            $statusQuery = " AND s.sale_status = ?";
            $params[] = $status;
        }

        $query = "
            SELECT 
                COUNT(s.id) as total_transactions,
                SUM(s.total_price) as total_sales,
                SUM(l.purchase_price * s.qty) as total_purchase_cost,
                SUM(s.total_price - (l.purchase_price * s.qty)) as total_margin
            FROM sales s
            JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE_FORMAT(s.created_at, '%Y-%m') = ?" . $statusQuery;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $summary['total_transactions'] = (int)($res['total_transactions'] ?? 0);
            $summary['total_sales'] = (float)($res['total_sales'] ?? 0);
            $summary['total_purchase_cost'] = (float)($res['total_purchase_cost'] ?? 0);
            $summary['total_margin'] = (float)($res['total_margin'] ?? 0);
        }

        // 2. Chart data: daily cash flow (cash received) for this month
        $chartParams = [$yearMonth];
        if ($status !== '') {
            $chartQuery = "
                SELECT DATE_FORMAT(sp.payment_date, '%Y-%m-%d') as sale_date, SUM(sp.payment_amount) as revenue, COUNT(*) as trx_count
                FROM sale_payments sp
                JOIN sales s ON sp.sale_id = s.id
                WHERE DATE_FORMAT(sp.payment_date, '%Y-%m') = ? AND sp.payment_status = 'verified' AND s.sale_status = ?
                GROUP BY DATE(sp.payment_date)
                ORDER BY sale_date ASC
            ";
            $chartParams[] = $status;
        } else {
            $chartQuery = "
                SELECT DATE_FORMAT(payment_date, '%Y-%m-%d') as sale_date, SUM(payment_amount) as revenue, COUNT(*) as trx_count
                FROM sale_payments
                WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? AND payment_status = 'verified'
                GROUP BY DATE(payment_date)
                ORDER BY sale_date ASC
            ";
        }
        $stmt = $this->conn->prepare($chartQuery);
        $stmt->execute($chartParams);
        $summary['chart_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Payment status breakdown
        $paymentStatsParams = [$yearMonth];
        $paymentStatsQuery = "
            SELECT payment_status as status, COUNT(*) as count, SUM(total_price) as total_value
            FROM sales
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
        if ($status !== '') {
            $paymentStatsQuery .= " AND sale_status = ?";
            $paymentStatsParams[] = $status;
        }
        $paymentStatsQuery .= " GROUP BY payment_status";
        $stmt = $this->conn->prepare($paymentStatsQuery);
        $stmt->execute($paymentStatsParams);
        $summary['payment_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Top selling products this month
        $topSellingParams = [$yearMonth];
        $topSellingQuery = "
            SELECT s.livestock_name as product_name, l.livestock_code as product_code, SUM(s.qty) as total_sold, SUM(s.total_price) as total_sales_value
            FROM sales s
            LEFT JOIN livestock l ON s.livestock_id = l.id
            WHERE DATE_FORMAT(s.created_at, '%Y-%m') = ?";
        if ($status !== '') {
            $topSellingQuery .= " AND s.sale_status = ?";
            $topSellingParams[] = $status;
        }
        $topSellingQuery .= "
            GROUP BY s.livestock_id
            ORDER BY total_sold DESC LIMIT 5
        ";
        $stmt = $this->conn->prepare($topSellingQuery);
        $stmt->execute($topSellingParams);
        $summary['top_selling'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $summary;
    }
}

