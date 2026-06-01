<?php
require_once '../../config/database.php';
require_once '../../models/SavingsPlan.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Accept JSON, FormData, or query param
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) $id = $data['id'] ?? null;
}

$id = filter_var($id, FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid.']);
    exit();
}

try {
    // Fetch transaction details before deleting
    $stmtFetch = $conn->prepare("SELECT * FROM savings_transactions WHERE id = ?");
    $stmtFetch->execute([$id]);
    $trx = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$trx) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan.']);
        exit();
    }

    $conn->beginTransaction();

    // Determine column names (new vs legacy schema)
    $statusCol = array_key_exists('transaction_status', $trx) ? 'transaction_status' : 'status';
    $planCol   = array_key_exists('savings_plan_id',   $trx) ? 'savings_plan_id'   : 'plan_id';

    // If verified, roll back the saldo on savings_plans
    if (($trx[$statusCol] ?? '') === 'verified') {
        $planModel = new SavingsPlan($conn);
        $planModel->applyVerifiedDeposit($trx[$planCol], -1 * abs($trx['amount']));
    }

    // Delete the upload file if exists
    if (!empty($trx['payment_proof']) && strpos($trx['payment_proof'], '/storage/uploads/') !== false) {
        $filePath = dirname(__DIR__, 2) . str_replace('/lautan-ternak-pantura', '', $trx['payment_proof']);
        if (file_exists($filePath)) @unlink($filePath);
    }

    // Delete the transaction record
    $stmt = $conn->prepare("DELETE FROM savings_transactions WHERE id = ?");
    $stmt->execute([$id]);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus dan saldo diperbarui.']);
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
}
