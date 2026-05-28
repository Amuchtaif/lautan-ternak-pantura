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
$target = trim($_POST['livestock_target'] ?? '');
$targetAmount = filter_var($_POST['target_amount'] ?? null, FILTER_VALIDATE_FLOAT);
$duration = filter_var($_POST['duration_month'] ?? null, FILTER_VALIDATE_INT);
$targetDate = trim($_POST['target_date'] ?? '');
$status = $_POST['status'] ?? 'active';
$notes = trim($_POST['notes'] ?? '');

if (!$id || $target === '' || !$targetAmount || $targetAmount < 100000 || !$duration || $duration < 1 || $duration > 60 || $targetDate === '' || !in_array($status, ['active', 'completed', 'overdue', 'cancelled'], true)) {
    echo json_encode(['success' => false, 'message' => 'Data tabungan tidak valid.']);
    exit;
}

try {
    $planModel = new SavingsPlan($conn);
    $planModel->updateAdmin($id, [
        'livestock_target' => $target,
        'target_amount' => $targetAmount,
        'duration_month' => $duration,
        'target_date' => $targetDate,
        'status' => $status,
        'notes' => $notes
    ]);

    echo json_encode(['success' => true, 'message' => 'Data tabungan berhasil diperbarui.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
