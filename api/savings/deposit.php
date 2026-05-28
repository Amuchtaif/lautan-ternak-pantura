<?php
require_once '../../config/database.php';
require_once '../../models/SavingsPlan.php';
require_once '../../models/SavingsTransaction.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/savings');
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /lautan-ternak-pantura/auth/login');
    exit;
}

$planId = filter_input(INPUT_POST, 'savings_plan_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$paymentMethod = trim($_POST['payment_method'] ?? 'transfer_bank');
$depositDate = trim($_POST['deposit_date'] ?? date('Y-m-d'));
$notes = trim($_POST['notes'] ?? '');
$redirect = $planId ? '/lautan-ternak-pantura/savings/detail/' . $planId : '/lautan-ternak-pantura/savings';

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: ' . $redirect . '?error=csrf');
    exit;
}

if (!$planId || !$amount || $amount < 10000) {
    header('Location: ' . $redirect . '?error=invalid_data');
    exit;
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

$targetPath = null;
$proofPath = 'cash_deposit';
if ($paymentMethod !== 'cash') {
    if (!isset($_FILES['payment_proof'])) {
        header('Location: ' . $redirect . '?error=invalid_data');
        exit;
    }

    $file = $_FILES['payment_proof'];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        header('Location: ' . $redirect . '?error=upload_failed');
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowedTypes[$mime])) {
        header('Location: ' . $redirect . '?error=invalid_file');
        exit;
    }

    $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/savings/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'savings_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowedTypes[$mime];
    $targetPath = $uploadDir . $fileName;
}

try {
    $planModel = new SavingsPlan($conn);
    $plan = $planModel->getCustomerPlan($planId, (int)$_SESSION['user_id']);
    if (!$plan || !in_array($plan['status'], ['active', 'overdue'], true)) {
        header('Location: /lautan-ternak-pantura/savings?error=plan_not_found');
        exit;
    }

    if ($paymentMethod !== 'cash') {
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            header('Location: ' . $redirect . '?error=upload_failed');
            exit;
        }
        $proofPath = '/lautan-ternak-pantura/storage/uploads/savings/' . $fileName;
    }

    $conn->beginTransaction();
    $transactionModel = new SavingsTransaction($conn);
    $transactionModel->createDeposit([
        'savings_plan_id' => $planId,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'payment_proof' => $proofPath,
        'notes' => trim('Tanggal setor: ' . $depositDate . "\n" . $notes)
    ]);
    $conn->commit();

    header('Location: ' . $redirect . '?success=payment_sent');
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($targetPath && file_exists($targetPath)) {
        unlink($targetPath);
    }
    header('Location: ' . $redirect . '?error=' . urlencode($e->getMessage()));
}
exit;
