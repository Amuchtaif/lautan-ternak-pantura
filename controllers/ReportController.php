<?php
require_once 'models/Report.php';

class ReportController {

    /**
     * @return PDO
     */
    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /lautan-ternak-pantura/auth/login");
            exit;
        }
    }

    // Daily Transaction & Revenue report
    public function daily() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $date = $_GET['date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';
        
        $reportModel = new Report($db);
        $summary = $reportModel->getDailySummary($date, $status);

        require 'views/admin/reports_daily.php';
    }

    // Monthly Transaction & Revenue report with daily charts and aggregates
    public function monthly() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $month = $_GET['month'] ?? date('Y-m');
        $status = $_GET['status'] ?? '';

        $reportModel = new Report($db);
        $summary = $reportModel->getMonthlySummary($month, $status);

        require 'views/admin/reports_monthly.php';
    }
}
