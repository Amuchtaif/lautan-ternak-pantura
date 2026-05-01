<?php
require_once 'models/Livestock.php';

class LivestockController {
    public function detail($id = null) {
        if (!$id) {
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        require_once 'config/database.php';
        
        $livestock = null;
        if (isset($conn)) {
            $livestockModel = new Livestock($conn);
            $livestock = $livestockModel->getById($id);
        }

        if (!$livestock) {
            // Jika tidak ditemukan, redirect ke marketplace
            header("Location: /lautan-ternak-pantura/marketplace");
            exit;
        }

        require 'views/livestock/detail.php';
    }
}
