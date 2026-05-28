<?php
require_once '../../config/database.php';
require_once '../../models/SavingsPlan.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

if (empty($_POST)) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_array($data)) {
        $_POST = $data;
    }
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Data tabungan tidak valid.']);
    exit;
}

try {
    $planModel = new SavingsPlan($conn);
    $plan = $planModel->getById($id);
    if (!$plan) {
        throw new RuntimeException('Data tabungan tidak ditemukan.');
    }

    $planModel->cancel($id);
    echo json_encode(['success' => true, 'message' => 'Data tabungan dibatalkan. Histori transaksi tetap tersimpan.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
