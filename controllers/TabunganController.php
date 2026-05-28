<?php
require_once 'models/Livestock.php';

class TabunganController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        require_once 'config/database.php';

        $livestocks = [];
        if (isset($conn)) {
            $livestockModel = new Livestock($conn);
            $livestocks = $livestockModel->getAvailable();
        }

        require_once 'views/tabungan.php';
    }
}
