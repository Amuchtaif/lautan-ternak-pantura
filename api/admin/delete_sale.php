<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');
error_reporting(0);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID is required']);
    exit();
}

try {
    $conn->beginTransaction();

    // Get order info to revert livestock status and stock
    $stmtOrig = $conn->prepare("SELECT livestock_id, status FROM orders WHERE id = ?");
    $stmtOrig->execute([$id]);
    $order = $stmtOrig->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        if ($order['status'] !== 'cancelled') {
            // Restore stock and set status to available
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$order['livestock_id']]);
            $live = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($live) {
                $newStock = $live['stock'] + 1;
                $stmtRevert = $conn->prepare("UPDATE livestock SET stock = ?, status = 'available' WHERE id = ?");
                $stmtRevert->execute([$newStock, $order['livestock_id']]);
            }
        } else {
            // Just update status to available if stock > 0
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$order['livestock_id']]);
            $live = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($live) {
                $newStatus = $live['stock'] == 0 ? 'sold' : 'available';
                $stmtRevert = $conn->prepare("UPDATE livestock SET status = ? WHERE id = ?");
                $stmtRevert->execute([$newStatus, $order['livestock_id']]);
            }
        }
    }

    // Delete order
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Data penjualan berhasil dihapus']);
    } else {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Data penjualan tidak ditemukan']);
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
