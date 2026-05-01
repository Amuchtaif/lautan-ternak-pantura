<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/customer/dashboard");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login");
    exit();
}

$planId = (int)$_POST['plan_id'];
$amount = (float)$_POST['amount'];

if ($planId <= 0 || $amount <= 0 || !isset($_FILES['proof'])) {
    header("Location: ../../views/customer/dashboard?error=invalid_data");
    exit();
}

// Handle Image Upload
$uploadDir = '../../assets/uploads/proofs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileExtension = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
$fileName = 'proof_' . time() . '_' . uniqid() . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
    $proofUrl = '/lautan-ternak-pantura/assets/uploads/proofs/' . $fileName;
    
    try {
        $stmt = $conn->prepare("INSERT INTO savings_transactions (plan_id, amount, proof_of_payment, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$planId, $amount, $proofUrl]);
        
        header("Location: ../../views/customer/dashboard?success=payment_sent");
    } catch (PDOException $e) {
        header("Location: ../../views/customer/dashboard?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../../views/customer/dashboard?error=upload_failed");
}
exit();
