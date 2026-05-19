<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../config/database.php';
require_once '../../models/Order.php';
require_once '../../models/Livestock.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Handle auth or guest checkout
$customerId = $_SESSION['user_id'] ?? null;

if (!$customerId) {
    if (!isset($conn)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Koneksi basis data gagal.']);
        exit;
    }

    // Read and parse guest details
    $guestName = trim($_POST['guest_name'] ?? 'Guest Customer');
    $guestPhone = trim($_POST['guest_phone'] ?? '');
    $guestAddress = trim($_POST['guest_address'] ?? '');

    // Generate unique guest email
    $guestEmail = 'guest_' . (!empty($guestPhone) ? preg_replace('/[^0-9]/', '', $guestPhone) : bin2hex(random_bytes(4))) . '@guest.com';

    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->execute([$guestEmail]);
    $guestUser = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($guestUser) {
        $customerId = $guestUser['id'];
        // Update their details in the users table in case they changed their name/address
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $updateStmt->execute([$guestName, $guestPhone, $guestAddress, $customerId]);
    } else {
        $defaultPasswordHash = password_hash('password123', PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, 'customer', ?, ?)");
        $insertStmt->execute([$guestName, $guestEmail, $defaultPasswordHash, $guestPhone, $guestAddress]);
        $customerId = $conn->lastInsertId();
    }

    // Set guest session for subsequent operations (like manual receipt upload if TF is selected)
    $_SESSION['user_id'] = $customerId;
    $_SESSION['role'] = 'customer';
    $_SESSION['user_name'] = $guestName;
}

// Read inputs
$livestockId = $_POST['livestock_id'] ?? null;
$qty = intval($_POST['qty'] ?? 1);
$notes = $_POST['notes'] ?? null;

if (!$livestockId || $qty <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data input tidak lengkap.']);
    exit;
}

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi basis data gagal.']);
    exit;
}

try {
    $livestockModel = new Livestock($conn);
    $orderModel = new Order($conn);

    $livestock = $livestockModel->getById($livestockId);

    if (!$livestock) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Hewan tidak ditemukan.']);
        exit;
    }

    try {
        $livestockModel->verifyPurchaseStock($livestockId, $qty);
    } catch (Exception $stockEx) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $stockEx->getMessage()]);
        exit;
    }

    // Begin Database Transaction
    $conn->beginTransaction();

    $livestockPrice = floatval($livestock['price']);
    $totalPrice = $livestockPrice * $qty;
    
    // Generate Unique Invoice/Order Code
    $orderCode = 'LTP-ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    $orderData = [
        'order_code' => $orderCode,
        'customer_id' => $_SESSION['user_id'],
        'livestock_id' => $livestockId,
        'qty' => $qty,
        'livestock_price_snapshot' => $livestockPrice,
        'total_price' => $totalPrice,
        'status' => 'waiting_payment', // Flow sets status to waiting_payment immediately after checkout
        'notes' => $notes
    ];

    $orderId = $orderModel->create($orderData);

    if (!$orderId) {
        throw new Exception('Gagal menyimpan pesanan ke database.');
    }

    // Reduce inventory stock
    $reduced = $livestockModel->reduceStock($livestockId, $qty);
    if (!$reduced) {
        throw new Exception('Gagal memperbarui stok hewan.');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_code' => $orderCode,
        'message' => 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error transaksi: ' . $e->getMessage()]);
}
