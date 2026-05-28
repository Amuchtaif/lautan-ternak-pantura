<?php
class CustomerController {
    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['is_login']) && isset($_SESSION['user_id'])) {
            $_SESSION['is_login'] = true;
            $_SESSION['full_name'] = $_SESSION['full_name'] ?? ($_SESSION['name'] ?? '');
        }
        require_once 'config/database.php';
        require_once 'views/customer/dashboard.php';
    }

    public function profile() {
        require_once 'config/database.php';
        require_once 'views/customer/profile.php';
    }
}
