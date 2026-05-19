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
$customer_id = $_POST['customer_id'] ?? null;
$livestock_id = $_POST['livestock_id'] ?? null;
$total_price = $_POST['total_price'] ?? null;
$status = $_POST['status'] ?? null;
$order_date = $_POST['order_date'] ?? null;

if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak terbaca']); exit(); }
if (!$customer_id) { echo json_encode(['success' => false, 'message' => 'Pelanggan wajib diisi']); exit(); }
if (!$livestock_id) { echo json_encode(['success' => false, 'message' => 'Hewan wajib diisi']); exit(); }
if (!$total_price) { echo json_encode(['success' => false, 'message' => 'Total harga wajib diisi']); exit(); }
if (!$status) { echo json_encode(['success' => false, 'message' => 'Status wajib diisi']); exit(); }

try {
    $conn->beginTransaction();

    // Get original order to see if livestock changed
    $stmtOrig = $conn->prepare("SELECT livestock_id FROM orders WHERE id = ?");
    $stmtOrig->execute([$id]);
    $origOrder = $stmtOrig->fetch(PDO::FETCH_ASSOC);

    // Update order
    $stmt = $conn->prepare("UPDATE orders SET customer_id = ?, livestock_id = ?, total_price = ?, status = ?, created_at = ? WHERE id = ?");
    
    if (empty($order_date)) {
        $order_date = date('Y-m-d H:i:s');
    } else {
        $order_date = date('Y-m-d H:i:s', strtotime($order_date));
    }
    
    $stmt->execute([$customer_id, $livestock_id, $total_price, $status, $order_date, $id]);

    // If existing customer and address is provided, update it
    $existing_address = $_POST['existing_customer_address'] ?? null;
    if (!empty($existing_address)) {
        $stmtUpdateUser = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmtUpdateUser->execute([$existing_address, $customer_id]);
    }

    // Insert payment if proof uploaded (for edits)
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    if ($payment_method === 'Transfer Bank' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
            $payment_proof_path = '/lautan-ternak-pantura/assets/uploads/payments/' . $filename;
            
            // Delete existing payment record if there is one?
            $stmtDel = $conn->prepare("DELETE FROM payments WHERE order_id = ?");
            $stmtDel->execute([$id]);

            $stmtPayment = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_proof, payment_status, verified_by, verified_at, created_at) VALUES (?, ?, ?, ?, 'verified', ?, CURRENT_TIMESTAMP, ?)");
            $stmtPayment->execute([$id, $total_price, $payment_method, $payment_proof_path, $_SESSION['user_id'], $order_date]);
        }
    }

    // Update livestock stock and status
    $oldLivestockId = $origOrder ? $origOrder['livestock_id'] : null;
    $oldStatus = $origOrder ? $origOrder['status'] : null;

    // 1. If livestock changed
    if ($oldLivestockId && $oldLivestockId != $livestock_id) {
        // Revert old livestock (restore stock if old order was NOT cancelled)
        if ($oldStatus !== 'cancelled') {
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$oldLivestockId]);
            $liveOld = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($liveOld) {
                $newStockOld = $liveOld['stock'] + 1;
                $stmtRevertOld = $conn->prepare("UPDATE livestock SET stock = ?, status = 'available' WHERE id = ?");
                $stmtRevertOld->execute([$newStockOld, $oldLivestockId]);
            }
        } else {
            // Just update old status based on current stock
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$oldLivestockId]);
            $liveOld = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($liveOld) {
                $newStatusOld = $liveOld['stock'] == 0 ? 'sold' : 'available';
                $stmtRevertOld = $conn->prepare("UPDATE livestock SET status = ? WHERE id = ?");
                $stmtRevertOld->execute([$newStatusOld, $oldLivestockId]);
            }
        }

        // Apply to new livestock
        if ($status !== 'cancelled') {
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$livestock_id]);
            $liveNew = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($liveNew) {
                $newStockNew = max(0, $liveNew['stock'] - 1);
                $newStatusNew = $newStockNew == 0 ? 'sold' : 'available';
                $stmtUpdateNew = $conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
                $stmtUpdateNew->execute([$newStockNew, $newStatusNew, $livestock_id]);
            }
        } else {
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$livestock_id]);
            $liveNew = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($liveNew) {
                $newStatusNew = $liveNew['stock'] == 0 ? 'sold' : 'available';
                $stmtUpdateNew = $conn->prepare("UPDATE livestock SET status = ? WHERE id = ?");
                $stmtUpdateNew->execute([$newStatusNew, $livestock_id]);
            }
        }
    } else {
        // Livestock did not change, but status might have
        if ($oldStatus !== 'cancelled' && $status === 'cancelled') {
            // Restore stock
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$livestock_id]);
            $live = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($live) {
                $newStock = $live['stock'] + 1;
                $stmtUpdate = $conn->prepare("UPDATE livestock SET stock = ?, status = 'available' WHERE id = ?");
                $stmtUpdate->execute([$newStock, $livestock_id]);
            }
        } elseif ($oldStatus === 'cancelled' && $status !== 'cancelled') {
            // Reduce stock
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$livestock_id]);
            $live = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($live) {
                $newStock = max(0, $live['stock'] - 1);
                $newStatus = $newStock == 0 ? 'sold' : 'available';
                $stmtUpdate = $conn->prepare("UPDATE livestock SET stock = ?, status = ? WHERE id = ?");
                $stmtUpdate->execute([$newStock, $newStatus, $livestock_id]);
            }
        } else {
            // Status and livestock stayed in same state (or both are active), just ensure status is correct based on stock
            $stmtL = $conn->prepare("SELECT stock FROM livestock WHERE id = ?");
            $stmtL->execute([$livestock_id]);
            $live = $stmtL->fetch(PDO::FETCH_ASSOC);
            if ($live) {
                $newStatus = $live['stock'] == 0 ? 'sold' : 'available';
                $stmtUpdate = $conn->prepare("UPDATE livestock SET status = ? WHERE id = ?");
                $stmtUpdate->execute([$newStatus, $livestock_id]);
            }
        }
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Data penjualan berhasil diperbarui']);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
