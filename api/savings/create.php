<?php
require_once '../../config/database.php';
require_once '../../models/SavingsPlan.php';
require_once '../../models/SavingsTransaction.php';
require_once '../../models/Livestock.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/savings/create');
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: /lautan-ternak-pantura/auth/login?redirect=' . urlencode('/lautan-ternak-pantura/savings/create'));
    exit;
}

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: /lautan-ternak-pantura/savings/create?error=csrf');
    exit;
}

$targetAmount = filter_input(INPUT_POST, 'target_amount', FILTER_VALIDATE_FLOAT);
$durationMonth = filter_input(INPUT_POST, 'duration_month', FILTER_VALIDATE_INT);
$livestockTarget = trim($_POST['livestock_target'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$livestockId = filter_input(INPUT_POST, 'livestock_id', FILTER_VALIDATE_INT);
$initialDeposit = filter_input(INPUT_POST, 'initial_deposit', FILTER_VALIDATE_FLOAT);
$paymentMethod = trim($_POST['payment_method'] ?? 'transfer_bank');
$participantName = trim($_POST['participant_name'] ?? '');
$participantPhone = trim($_POST['participant_phone'] ?? '');
$participantAddress = trim($_POST['participant_address'] ?? '');
$targetDateInput = trim($_POST['target_date'] ?? '');

if (!$livestockId || !$durationMonth || $durationMonth < 1 || $durationMonth > 60 || $participantName === '' || $participantPhone === '' || $participantAddress === '') {
    header('Location: /lautan-ternak-pantura/savings/create?error=invalid_data');
    exit;
}

try {
    $today = new DateTimeImmutable('today');
    $targetDate = new DateTimeImmutable($targetDateInput);
    if ($targetDate <= $today) {
        throw new RuntimeException('Target waktu pelunasan harus di masa depan.');
    }
    $diff = $today->diff($targetDate);
    $durationMonth = max(1, ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0));
    if ($durationMonth > 60) {
        throw new RuntimeException('Target waktu pelunasan maksimal 60 bulan.');
    }
} catch (Throwable $e) {
    header('Location: /lautan-ternak-pantura/savings/create?error=invalid_data');
    exit;
}

$initialDeposit = $initialDeposit ?: 0;
if ($initialDeposit < 10000) {
    header('Location: /lautan-ternak-pantura/savings/create?error=invalid_data');
    exit;
}

if (!in_array($paymentMethod, ['transfer_bank', 'qris', 'cash'], true)) {
    $paymentMethod = 'transfer_bank';
}

try {
    $conn->beginTransaction();

    $livestockModel = new Livestock($conn);
    $livestock = $livestockModel->getById($livestockId);
    if (!$livestock || $livestock['status'] !== 'available' || (int)$livestock['stock'] < 1) {
        throw new RuntimeException('Hewan target tidak tersedia.');
    }

    $livestockTarget = $livestock['name'] ?? $livestock['breed'] ?? 'Hewan Qurban';
    $targetAmount = (float)$livestock['price'];
    if ($targetAmount < 100000) {
        throw new RuntimeException('Harga hewan target tidak valid.');
    }
    if ($initialDeposit > $targetAmount) {
        throw new RuntimeException('Tabungan awal tidak boleh melebihi target nominal.');
    }

    $planModel = new SavingsPlan($conn);
    $planId = $planModel->create([
        'customer_id' => $_SESSION['user_id'],
        'livestock_id' => $livestockId,
        'livestock_target' => $livestockTarget,
        'target_amount' => $targetAmount,
        'monthly_target' => round(($targetAmount - $initialDeposit) / $durationMonth, 2),
        'duration_month' => $durationMonth,
        'target_date' => $targetDate->format('Y-m-d'),
        'notes' => $notes ?: $participantAddress
    ]);

    $stmtUser = $conn->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmtUser->execute([$participantPhone, $participantAddress, (int)$_SESSION['user_id']]);

    $stmtSohibul = $conn->prepare("
        INSERT INTO sohibul_qurban (plan_id, name, phone, address, relationship)
        VALUES (?, ?, ?, ?, 'self')
    ");
    $stmtSohibul->execute([$planId, $participantName, $participantPhone, $participantAddress]);

    if ($initialDeposit >= 10000) {
        $transactionModel = new SavingsTransaction($conn);
        $transactionModel->createDeposit([
            'savings_plan_id' => $planId,
            'amount' => $initialDeposit,
            'payment_method' => $paymentMethod,
            'payment_proof' => 'initial_registration',
            'notes' => 'Setoran awal dari form pendaftaran tabungan.'
        ]);
    }

    $conn->commit();
    header('Location: /lautan-ternak-pantura/savings/detail/' . $planId . '?success=plan_created');
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: /lautan-ternak-pantura/savings/create?error=' . urlencode($e->getMessage()));
}
exit;
