<?php
require_once 'models/SavingsPlan.php';
require_once 'models/SavingsTransaction.php';
require_once 'models/Livestock.php';
require_once 'models/User.php';

class SavingsController {
    private function dbConnect() {
        global $conn;
        if (!isset($conn)) {
            require 'config/database.php';
        }
        return $conn;
    }

    private function requireRole($role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            header('Location: /lautan-ternak-pantura/auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    private function ensureCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function index() {
        $this->requireRole('customer');
        header('Location: /lautan-ternak-pantura/customer/dashboard');
        exit;
    }

    public function create() {
        $this->requireRole('customer');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $livestockModel = new Livestock($db);
        $userModel = new User($db);
        $livestocks = $livestockModel->getAvailable('qurban');
        $selectedLivestock = null;
        $currentUser = $userModel->findArrayById((int)$_SESSION['user_id']);

        if (!empty($_GET['livestock_id'])) {
            $candidate = $livestockModel->getById((int)$_GET['livestock_id']);
            if ($candidate && $candidate['status'] === 'available' && (int)$candidate['stock'] > 0) {
                $selectedLivestock = $candidate;
            }
        }

        require 'views/customer/savings_create.php';
    }

    public function detail($id = null) {
        $this->requireRole('customer');
        header('Location: /lautan-ternak-pantura/customer/dashboard');
        exit;
    }

    public function management() {
        $this->requireRole('admin');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $planModel = new SavingsPlan($db);
        $transactionModel = new SavingsTransaction($db);

        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer' => $_GET['customer'] ?? ''
        ];

        $plans = $planModel->getAll($filters);
        $stats = $planModel->getAdminStats();
        $pendingTransactions = $transactionModel->countPending();

        require 'views/admin/savings_management.php';
    }

    public function adminDetail($id = null) {
        $this->requireRole('admin');
        $this->ensureCsrfToken();

        $db = $this->dbConnect();
        $planModel = new SavingsPlan($db);
        $transactionModel = new SavingsTransaction($db);

        $plan = $planModel->getById((int)$id);
        if (!$plan) {
            http_response_code(404);
            echo 'Rencana tabungan tidak ditemukan.';
            return;
        }

        $transactions = $transactionModel->getByPlan($plan['id']);
        $progress = $planModel->calculateProgress((float)$plan['current_amount'], (float)$plan['target_amount']);

        require 'views/admin/savings_detail.php';
    }

    public function receipt($id = null) {
        $this->printReceipt($id);
    }

    public function printReceipt($id = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /lautan-ternak-pantura/auth/login');
            exit;
        }

        $db = $this->dbConnect();
        $transactionModel = new SavingsTransaction($db);
        $planModel = new SavingsPlan($db);

        $trx = $transactionModel->getById((int)$id);
        if (!$trx) {
            http_response_code(404);
            echo 'Transaksi tidak ditemukan.';
            return;
        }

        $plan = $planModel->getById($trx['savings_plan_id']);
        if (!$plan) {
            http_response_code(404);
            echo 'Rencana tabungan terkait tidak ditemukan.';
            return;
        }

        if ($_SESSION['role'] !== 'admin' && (int)$plan['customer_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        require 'views/print_receipt.php';
    }
}
