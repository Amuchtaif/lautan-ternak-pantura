<?php
class CustomerController {
    public function dashboard() {
        require_once 'config/database.php';
        require_once 'views/customer/dashboard.php';
    }

    public function profile() {
        require_once 'config/database.php';
        require_once 'views/customer/profile.php';
    }
}
