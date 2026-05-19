<?php
require_once 'models/Report.php';

class ReportController {

    private function dbConnect() {
        require 'config/database.php';
        return $conn;
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: /lautan-ternak-pantura/views/auth/login");
            exit;
        }
    }

    // Daily Transaction & Revenue report
    public function daily() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $date = $_GET['date'] ?? date('Y-m-d');
        
        $reportModel = new Report($db);
        $summary = $reportModel->getDailySummary($date);

        require 'views/admin/reports_daily.php';
    }

    // Monthly Transaction & Revenue report with daily charts and aggregates
    public function monthly() {
        $this->checkAdmin();
        $db = $this->dbConnect();

        $month = $_GET['month'] ?? date('Y-m');

        $reportModel = new Report($db);
        $summary = $reportModel->getMonthlySummary($month);

        require 'views/admin/reports_monthly.php';
    }
}
