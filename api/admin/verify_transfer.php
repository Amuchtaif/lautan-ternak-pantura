<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Handle both FormData and JSON input
if (empty($_POST)) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) { $_POST = $data; }
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

if (!in_array($status, ['verified', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit();
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE savings_transactions SET status = ?, verified_by = ? WHERE id = ?");
    $stmt->execute([$status, $_SESSION['user_id'], $id]);

    // Refactor: If verified, automatically check and complete the savings plan if target is met
    if ($status === 'verified') {
        $stmtPlan = $conn->prepare("SELECT plan_id FROM savings_transactions WHERE id = ?");
        $stmtPlan->execute([$id]);
        $planId = $stmtPlan->fetchColumn();

        if ($planId) {
            require_once '../../models/Savings.php';
            $savingsModel = new Savings($conn);
            $savingsModel->checkAndCompletePlan($planId);
        }
    }

    $conn->commit();

    $msg = $status === 'verified' ? 'Transaksi berhasil diverifikasi' : 'Transaksi telah ditolak';
    echo json_encode(['success' => true, 'message' => $msg]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
