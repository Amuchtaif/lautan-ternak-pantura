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

$type = $_POST['type'] ?? null;
$category = $_POST['category'] ?? null;
$weight = $_POST['weight'] ?? null;
$age = $_POST['age'] ?? null;
$price = $_POST['price'] ?? null;
$status = $_POST['status'] ?? 'available';
$health_condition = $_POST['health_condition'] ?? null;

// Handle image upload
$image_url = 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../../assets/uploads/livestock/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'livestock_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        $image_url = '/lautan-ternak-pantura/assets/uploads/livestock/' . $filename;
    }
}

if (!$type || !$category || !$weight || !$age || !$price) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO livestock (type, category, weight, age, price, status, health_condition, image_url, breeder_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$type, $category, $weight, $age, $price, $status, $health_condition, $image_url, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Data hewan baru berhasil ditambahkan']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
