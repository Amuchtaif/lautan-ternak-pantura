<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../config/database.php';
require_once '../../models/Order.php';
require_once '../../models/Payment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

$orderId = $_POST['order_id'] ?? null;
$paymentMethod = $_POST['payment_method'] ?? 'Transfer Bank';

if (!$orderId || !isset($_FILES['proof'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data input atau bukti transfer tidak ditemukan.']);
    exit;
}

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi basis data gagal.']);
    exit;
}

try {
    $orderModel = new Order($conn);
    $paymentModel = new Payment($conn);

    $order = $orderModel->getById($orderId);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        exit;
    }

    if ($order['customer_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak atas pesanan ini.']);
        exit;
    }

    // Process Upload File
    $file = $_FILES['proof'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file. Error code: ' . $file['error']]);
        exit;
    }

    // Validate size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
        exit;
    }

    // Validate mime-type securely
    $tmpPath = $file['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimeTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format file tidak diizinkan. Hanya menerima JPG, PNG, dan WEBP.']);
        exit;
    }

    // Secure target directory
    $uploadDir = '../../assets/uploads/payments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate safe, unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
        $ext = 'jpg'; // Default secure fallback extension
    }
    $filename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($tmpPath, $targetPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan bukti pembayaran di server.']);
        exit;
    }

    $relativeImagePath = '/lautan-ternak-pantura/assets/uploads/payments/' . $filename;

    // Database transaction to record payment and update order status
    $conn->beginTransaction();

    $paymentData = [
        'order_id' => $orderId,
        'amount' => $order['total_price'],
        'payment_method' => $paymentMethod,
        'payment_proof' => $relativeImagePath,
        'payment_status' => 'pending'
    ];

    $paymentId = $paymentModel->create($paymentData);

    if (!$paymentId) {
        throw new Exception('Gagal mencatat transaksi pembayaran.');
    }

    // Update order status to payment_review
    $updated = $orderModel->updateStatus($orderId, 'payment_review');
    if (!$updated) {
        throw new Exception('Gagal memperbarui status pesanan.');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Bukti pembayaran berhasil diunggah! Penjualan akan ditinjau oleh Admin segera.'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
