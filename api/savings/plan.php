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

if ($targetAmount <= 0 || $duration <= 0) {
    header("Location: ../../views/tabungan?error=invalid_data");
    exit();
}

$monthlyInstallment = $targetAmount / $duration;

try {
    $conn->beginTransaction();

    // 1. Create Savings Plan
    $stmt = $conn->prepare("INSERT INTO savings_plans (customer_id, livestock_id, target_amount, monthly_installment, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->execute([$customerId, $livestockId, $targetAmount, $monthlyInstallment]);
    $planId = $conn->lastInsertId();

    // 2. If livestock selected, mark it as booked (optional - depends on business logic)
    // For now, we won't strictly mark as booked until a deposit is made, or we can mark it now.
    // The user said "memilih hewan", so we should probably reserve it.
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
