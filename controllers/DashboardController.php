<?php
require_once 'helpers/AuthHelper.php';

class DashboardController {
    public function customer() {
        AuthHelper::requireLogin('customer');
        require 'views/customer/dashboard.php';
    }
}
