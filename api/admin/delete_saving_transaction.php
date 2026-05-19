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

// Handle both FormData, Query Param, and JSON input
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) {
        $id = $data['id'] ?? null;
    }
}

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid atau kosong.']);
    exit();
}

try {
    // Delete the transaction securely
    $stmt = $conn->prepare("DELETE FROM savings_transactions WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Transaksi tabungan berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan atau sudah terhapus.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
}
?>
