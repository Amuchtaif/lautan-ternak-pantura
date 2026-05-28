<?php
require_once '../../config/database.php';
require_once '../../helpers/AuthHelper.php';
require_once '../../models/User.php';
require_once '../../models/Livestock.php';
require_once '../../models/SavingsPlan.php';
require_once '../../models/SavingsTransaction.php';

AuthHelper::start();

function redirectTabungan($error) {
    header('Location: /lautan-ternak-pantura/tabungan?error=' . urlencode($error) . '#form-registrasi');
    exit;
}

function tableHasColumn($conn, $table, $column) {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/tabungan');
    exit;
}

if (!AuthHelper::validateCsrf($_POST['csrf_token'] ?? '')) {
    redirectTabungan('csrf');
}

if (!isset($conn)) {
    redirectTabungan('database');
}

try {
    if (!tableHasColumn($conn, 'users', 'username')) {
        $conn->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL UNIQUE AFTER name");
    }
    if (!tableHasColumn($conn, 'savings_plans', 'livestock_id')) {
        $conn->exec("ALTER TABLE savings_plans ADD COLUMN livestock_id INT NULL AFTER customer_id");
    }
    if (!tableHasColumn($conn, 'savings_plans', 'target_type')) {
        $conn->exec("ALTER TABLE savings_plans ADD COLUMN target_type ENUM('livestock', 'manual') DEFAULT 'livestock' AFTER livestock_id");
    }
} catch (Throwable $e) {
    redirectTabungan('username_column_failed');
}

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$address = trim($_POST['address'] ?? '');
$username = strtolower(trim($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirmPassword = (string)($_POST['password_confirm'] ?? '');
$targetMode = $_POST['target_mode'] ?? 'livestock';
$livestockId = filter_input(INPUT_POST, 'livestock_id', FILTER_VALIDATE_INT);
$manualAmount = filter_input(INPUT_POST, 'manual_target_amount', FILTER_VALIDATE_FLOAT);
$targetDateInput = trim($_POST['target_date'] ?? '');
$initialDeposit = filter_input(INPUT_POST, 'initial_deposit', FILTER_VALIDATE_FLOAT);
$paymentMethod = trim($_POST['payment_method'] ?? 'transfer_bank');
$paymentProof = null;
$targetPath = null;

if ($fullName === '' || $phone === '' || $address === '' || $username === '') {
    redirectTabungan('invalid_data');
}

if (!preg_match('/^[a-z0-9._-]{4,50}$/', $username)) {
    redirectTabungan('username_invalid');
}

if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    redirectTabungan('password_weak');
}

if ($password !== $confirmPassword) {
    redirectTabungan('password_mismatch');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectTabungan('email_invalid');
}

$initialDeposit = $initialDeposit ?: 0;
if ($initialDeposit < 10000) {
    redirectTabungan('invalid_data');
}

if (!in_array($paymentMethod, ['transfer_bank', 'qris', 'cash'], true)) {
    $paymentMethod = 'transfer_bank';
}

if ($paymentMethod !== 'cash') {
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        redirectTabungan('upload_failed');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $file = $_FILES['payment_proof'];
    if ($file['size'] > 2 * 1024 * 1024) {
        redirectTabungan('upload_failed');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowedTypes[$mime])) {
        redirectTabungan('invalid_file');
    }

    $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/savings/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'savings_register_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowedTypes[$mime];
    $targetPath = $uploadDir . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        redirectTabungan('upload_failed');
    }
    $paymentProof = '/lautan-ternak-pantura/storage/uploads/savings/' . $fileName;
} else {
    $paymentProof = 'cash_registration';
}

try {
    $today = new DateTimeImmutable('today');
    $targetDate = new DateTimeImmutable($targetDateInput);
    if ($targetDate <= $today) {
        throw new RuntimeException('invalid date');
    }
    $diff = $today->diff($targetDate);
    $durationMonth = max(1, ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0));
    if ($durationMonth > 60) {
        redirectTabungan('invalid_data');
    }
} catch (Throwable $e) {
    redirectTabungan('invalid_data');
}

try {
    $userModel = new User($conn);
    if ($userModel->usernameExists($username)) {
        redirectTabungan('username_taken');
    }

    if ($email === '') {
        $email = $username . '@tabungan-qurban.local';
    }
    if ($userModel->emailExists($email)) {
        redirectTabungan('email_taken');
    }

    $livestockTarget = 'Tabungan Qurban Fleksibel';
    $targetAmount = (float)$manualAmount;

    if ($targetMode === 'livestock') {
        $livestockModel = new Livestock($conn);
        $livestock = $livestockModel->getById((int)$livestockId);
        if (!$livestock || $livestock['status'] !== 'available' || (int)$livestock['stock'] < 1) {
            redirectTabungan('livestock_unavailable');
        }
        $livestockTarget = $livestock['name'] ?? $livestock['breed'] ?? 'Hewan Qurban';
        $targetAmount = (float)$livestock['price'];
    }

    if ($targetAmount < 100000 || $initialDeposit > $targetAmount) {
        redirectTabungan('invalid_data');
    }

    $conn->beginTransaction();

    $userId = $userModel->createCustomer([
        'full_name' => $fullName,
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'gender' => null,
        'address' => $address,
        'city' => null,
        'province' => null
    ]);

    $planModel = new SavingsPlan($conn);
    $planId = $planModel->create([
        'customer_id' => $userId,
        'livestock_id' => $targetMode === 'livestock' ? $livestockId : null,
        'target_type' => $targetMode === 'livestock' ? 'livestock' : 'manual',
        'livestock_target' => $livestockTarget,
        'target_amount' => $targetAmount,
        'monthly_target' => round(($targetAmount - $initialDeposit) / $durationMonth, 2),
        'duration_month' => $durationMonth,
        'target_date' => $targetDate->format('Y-m-d'),
        'notes' => $targetMode === 'manual' ? 'Target nominal manual' : null
    ]);

    $stmtSohibul = $conn->prepare("
        INSERT INTO sohibul_qurban (plan_id, name, phone, address, relationship)
        VALUES (?, ?, ?, ?, 'self')
    ");
    $stmtSohibul->execute([$planId, $fullName, $phone, $address]);

    $transactionModel = new SavingsTransaction($conn);
    $transactionModel->createDeposit([
        'savings_plan_id' => $planId,
        'amount' => $initialDeposit,
        'payment_method' => $paymentMethod,
        'payment_proof' => $paymentProof,
        'notes' => 'Setoran awal dari registrasi Program Tabungan Qurban.'
    ]);

    $conn->commit();

    $user = $userModel->findArrayById($userId);
    AuthHelper::login($user);
    $userModel->updateLastLogin($userId);

    header('Location: /lautan-ternak-pantura/savings/detail/' . $planId . '?success=plan_created');
} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($targetPath && file_exists($targetPath)) {
        unlink($targetPath);
    }
    redirectTabungan($e->getMessage());
}
exit;
