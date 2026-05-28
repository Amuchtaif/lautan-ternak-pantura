<?php
require_once '../../config/database.php';
require_once '../../models/SavingsPlan.php';
require_once '../../models/SavingsTransaction.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
$status = $_POST['status'] ?? null;
$notes = trim($_POST['notes'] ?? '');

if (!$id || !in_array($status, ['verified', 'rejected'], true)) {
    echo json_encode(['success' => false, 'message' => 'Data verifikasi tidak valid.']);
    exit;
}

try {
    $conn->beginTransaction();

    $transactionModel = new SavingsTransaction($conn);
    $planModel = new SavingsPlan($conn);

    $transaction = $transactionModel->getById($id);
    if (!$transaction || $transaction['transaction_status'] !== 'pending') {
        throw new RuntimeException('Transaksi tidak ditemukan atau sudah diproses.');
    }

    $transactionModel->verify($id, $status, (int)$_SESSION['user_id'], $notes ?: null);

    if ($status === 'verified') {
        $planModel->applyVerifiedDeposit((int)$transaction['savings_plan_id'], (float)$transaction['amount']);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $status === 'verified' ? 'Setoran tabungan berhasil diverifikasi.' : 'Setoran tabungan ditolak.'
    ]);
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
