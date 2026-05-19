<?php
require_once 'models/Livestock.php';

class MarketplaceController {
    public function index() {
        require_once 'config/database.php';
        
        $livestocks = [];
        if (isset($conn)) {
            $livestockModel = new Livestock($conn);
            $filter_type = isset($_GET['type']) ? $_GET['type'] : '';
            
            $livestocks = $livestockModel->getAvailable($filter_type);
        }
        
        require_once 'views/marketplace.php';
    }
}

