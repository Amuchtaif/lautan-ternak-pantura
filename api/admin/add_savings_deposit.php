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

$planId = filter_input(INPUT_POST, 'savings_plan_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$paymentMethod = trim($_POST['payment_method'] ?? 'cash');
$depositDate = trim($_POST['deposit_date'] ?? date('Y-m-d'));
$notes = trim($_POST['notes'] ?? '');

if (!$planId || !$amount || $amount < 10000) {
    echo json_encode(['success' => false, 'message' => 'Data setoran tidak valid. Minimal Rp 10.000.']);
    exit;
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

$targetPath = null;
$proofPath = 'cash_deposit';

if ($paymentMethod === 'transfer_bank') {
    $proofPath = 'transfer_manual_no_proof';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB.']);
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowedTypes[$mime])) {
            echo json_encode(['success' => false, 'message' => 'Format file harus JPG, PNG, atau WEBP.']);
            exit;
        }

        $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/savings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'savings_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowedTypes[$mime];
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah bukti transfer.']);
            exit;
        }
        $proofPath = '/lautan-ternak-pantura/storage/uploads/savings/' . $fileName;
    }
}

try {
    $planModel = new SavingsPlan($conn);
    $plan = $planModel->getById($planId);
    if (!$plan) {
        throw new RuntimeException('Rencana tabungan tidak ditemukan.');
    }

    $conn->beginTransaction();
    $transactionModel = new SavingsTransaction($conn);
    
    // Create pending deposit
    $txId = $transactionModel->createDeposit([
        'savings_plan_id' => $planId,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'payment_proof' => $proofPath,
        'notes' => trim('Setoran manual admin. Tanggal setor: ' . $depositDate . "\n" . $notes)
    ]);

    // Verify deposit instantly
    $transactionModel->verify($txId, 'verified', (int)$_SESSION['user_id'], trim('Tanggal setor: ' . $depositDate . "\n" . $notes));

    // Update plans balance
    $planModel->applyVerifiedDeposit($planId, $amount);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Setoran manual berhasil dicatat.']);
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($targetPath && file_exists($targetPath)) {
        unlink($targetPath);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
