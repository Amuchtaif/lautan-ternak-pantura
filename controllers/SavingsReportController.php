<?php
require_once 'models/SavingsReport.php';

class SavingsReportController {
    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /lautan-ternak-pantura/auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    public function daily() {
        $this->requireAdmin();
        $db = $this->dbConnect();

        $date = $_GET['date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';
        $customer = $_GET['customer'] ?? '';

        $reportModel = new SavingsReport($db);
        $report = $reportModel->getDailyReport($date, $status, $customer);

        require 'views/admin/savings_reports_daily.php';
    }

    public function monthly() {
        $this->requireAdmin();
        $db = $this->dbConnect();

        $month = $_GET['month'] ?? date('Y-m');
        $status = $_GET['status'] ?? '';
        $customer = $_GET['customer'] ?? '';

        $reportModel = new SavingsReport($db);
        $report = $reportModel->getMonthlyReport($month, $status, $customer);

        require 'views/admin/savings_reports_monthly.php';
    }
}
