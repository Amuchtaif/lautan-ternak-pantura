<?php
class TabunganController {
    public function index() {
        require_once 'config/database.php';
        
        $selectedLivestock = null;
        if (isset($_GET['livestock_id'])) {
            $livestockId = (int)$_GET['livestock_id'];
            $stmt = $conn->prepare("SELECT * FROM livestock WHERE id = ? AND status = 'available'");
            $stmt->execute([$livestockId]);
            $selectedLivestock = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        require_once 'views/tabungan.php';
    }
}
