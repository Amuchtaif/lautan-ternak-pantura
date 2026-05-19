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

$customer_id = $_POST['customer_id'] ?? null;
$livestock_id = $_POST['livestock_id'] ?? null;
$total_price = $_POST['total_price'] ?? null;
$status = $_POST['status'] ?? 'pending';
$order_date = $_POST['order_date'] ?? null;

if (!$customer_id || !$livestock_id || !$total_price) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. If customer_id is manual, register customer on the fly
    if ($customer_id === 'manual') {
        $manual_name = $_POST['manual_customer_name'] ?? null;
        $manual_phone = $_POST['manual_customer_phone'] ?? null;
        $manual_email = $_POST['manual_customer_email'] ?? null;
        $manual_address = $_POST['manual_customer_address'] ?? null;

        if (!$manual_name) {
            echo json_encode(['success' => false, 'message' => 'Nama lengkap pelanggan manual wajib diisi.']);
            exit();
        }

        if (!$manual_email) {
            $manual_email = 'walkin_' . time() . '_' . rand(100, 999) . '@lautanternak.com';
        }

        // Check if email already exists
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$manual_email]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $customer_id = $existingUser['id'];
        } else {
            $password_hash = password_hash('password123', PASSWORD_BCRYPT);
            $stmtUser = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, 'customer', ?, ?)");
            $stmtUser->execute([$manual_name, $manual_email, $password_hash, $manual_phone, $manual_address]);
            $customer_id = $conn->lastInsertId();
        }
    } else {
        // If existing customer and address is provided, update it
        $existing_address = $_POST['existing_customer_address'] ?? null;
        if (!empty($existing_address)) {
            $stmtUpdateUser = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
            $stmtUpdateUser->execute([$existing_address, $customer_id]);
        }
    }

    // 2. If livestock_id is manual, create custom livestock record
    $price_snapshot = $total_price;
    if ($livestock_id === 'manual') {
        $manual_code = $_POST['manual_livestock_code'] ?? null;
        $manual_name = $_POST['manual_livestock_name'] ?? null;
        $manual_breed = $_POST['manual_livestock_breed'] ?? null;
        $manual_gender = $_POST['manual_livestock_gender'] ?? 'male';
        $manual_weight = $_POST['manual_livestock_weight'] ?? 0;
        $manual_price = $_POST['manual_livestock_price'] ?? 0;
        $manual_desc = $_POST['manual_livestock_description'] ?? 'Offline Custom Sale';

        if (!$manual_name || !$manual_breed || !$manual_weight || !$manual_price) {
            echo json_encode(['success' => false, 'message' => 'Nama, ras, berat, dan harga hewan manual wajib diisi.']);
            exit();
        }

        if (!$manual_code) {
            $manual_code = 'LTP-MAN-' . time() . rand(10, 99);
        }

        $price_snapshot = $manual_price;

        $stmtLive = $conn->prepare("INSERT INTO livestock (code, name, breed, gender, weight, price, stock, status, description) VALUES (?, ?, ?, ?, ?, ?, 1, 'sold', ?)");
        $stmtLive->execute([$manual_code, $manual_name, $manual_breed, $manual_gender, $manual_weight, $manual_price, $manual_desc]);
        $livestock_id = $conn->lastInsertId();
    } else {
        // Fetch selected livestock's price for snapshot
        $stmtLivePrice = $conn->prepare("SELECT price FROM livestock WHERE id = ?");
        $stmtLivePrice->execute([$livestock_id]);
        $liveData = $stmtLivePrice->fetch(PDO::FETCH_ASSOC);
        if ($liveData) {
            $price_snapshot = $liveData['price'];
        }
    }

    // 3. Generate a unique order code
    $order_code = 'LTP-ORD-' . strtoupper(substr(uniqid(), 7)) . rand(10, 99);
    $qty = 1;

    // Fetch names for transaction summary notes
    $stmtCustName = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmtCustName->execute([$customer_id]);
    $custName = $stmtCustName->fetchColumn() ?: 'Pelanggan';

    $stmtLivName = $conn->prepare("SELECT name FROM livestock WHERE id = ?");
    $stmtLivName->execute([$livestock_id]);
    $livName = $stmtLivName->fetchColumn() ?: 'Hewan Qurban';

    require_once '../../models/Order.php';
    $orderModelForSummary = new Order($conn);
    $autoNotes = $orderModelForSummary->generateOfflineSummary($custName, $livName, $total_price);

    // 4. Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_id, livestock_id, qty, livestock_price_snapshot, total_price, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (empty($order_date)) {
        $order_date = date('Y-m-d H:i:s');
    } else {
        $order_date = date('Y-m-d H:i:s', strtotime($order_date));
    }
    
    $stmt->execute([$order_code, $customer_id, $livestock_id, $qty, $price_snapshot, $total_price, $status, $autoNotes, $order_date]);
    $order_id = $conn->lastInsertId();

    // 4.5 Insert payment if proof uploaded
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
            $stmtPayment = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_proof, payment_status, verified_by, verified_at, created_at) VALUES (?, ?, ?, ?, 'verified', ?, CURRENT_TIMESTAMP, ?)");
            $stmtPayment->execute([$order_id, $total_price, $payment_method, $payment_proof_path, $_SESSION['user_id'], $order_date]);
        }
    }

    // 5. Update livestock stock and status (if NOT manually added livestock, otherwise already set to sold)
    if ($_POST['livestock_id'] !== 'manual') {
        if ($status !== 'cancelled') {
            // Fetch current stock
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
            // Cancelled: update status based on current stock
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

    echo json_encode(['success' => true, 'message' => 'Data penjualan baru berhasil ditambahkan']);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
