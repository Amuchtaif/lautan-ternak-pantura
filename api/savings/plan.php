<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/tabungan");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login");
    exit();
}

$customerId = $_SESSION['user_id'];
$livestockId = isset($_POST['livestock_id']) ? (int)$_POST['livestock_id'] : null;
$targetAmount = (float)$_POST['target_amount'];
$duration = (int)$_POST['duration'];

$sqName = $_POST['sq_name'] ?? '';
$sqPhone = $_POST['sq_phone'] ?? '';
$sqAddress = $_POST['sq_address'] ?? '';
$sqRelationship = $_POST['sq_relationship'] ?? 'self';

if ($targetAmount <= 0 || $duration <= 0) {
    header("Location: ../../views/tabungan?error=invalid_data");
    exit();
}

if (empty($sqName)) {
    header("Location: ../../views/tabungan?error=nama_sohibul_qurban_wajib_diisi");
    exit();
}

$monthlyInstallment = $targetAmount / $duration;

try {
    $conn->beginTransaction();

    // 1. Create Savings Plan
    $stmt = $conn->prepare("INSERT INTO savings_plans (customer_id, livestock_id, target_amount, monthly_installment, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->execute([$customerId, $livestockId, $targetAmount, $monthlyInstallment]);
    $planId = $conn->lastInsertId();

    // 2. Save Sohibul Qurban data
    $stmt = $conn->prepare("INSERT INTO sohibul_qurban (plan_id, name, phone, address, relationship) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$planId, $sqName, $sqPhone, $sqAddress, $sqRelationship]);

    // 3. If livestock selected, mark it as booked
    if ($livestockId) {
        $stmt = $conn->prepare("UPDATE livestock SET status = 'booked' WHERE id = ?");
        $stmt->execute([$livestockId]);
    }

    $conn->commit();
    header("Location: ../../views/customer/dashboard?success=plan_created");
} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: ../../views/tabungan?error=" . urlencode($e->getMessage()));
}
exit();
