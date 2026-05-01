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
$type = $_POST['type'] ?? null;
$category = $_POST['category'] ?? null;
$weight = $_POST['weight'] ?? null;
$age = $_POST['age'] ?? null;
$price = $_POST['price'] ?? null;
$status = $_POST['status'] ?? null;
$health_condition = $_POST['health_condition'] ?? null;

if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak terbaca']); exit(); }
if (!$type) { echo json_encode(['success' => false, 'message' => 'Jenis hewan wajib diisi']); exit(); }
if (!$category) { echo json_encode(['success' => false, 'message' => 'Kategori wajib diisi']); exit(); }
if (!$weight) { echo json_encode(['success' => false, 'message' => 'Berat wajib diisi']); exit(); }
if (!$age) { echo json_encode(['success' => false, 'message' => 'Usia wajib diisi']); exit(); }
if (!$price) { echo json_encode(['success' => false, 'message' => 'Harga wajib diisi']); exit(); }
if (!$status) { echo json_encode(['success' => false, 'message' => 'Status wajib diisi']); exit(); }

// Handle image upload
$image_sql = '';
$params = [$type, $category, $weight, $age, $price, $status, $health_condition];

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
        $image_sql = ', image_url = ?';
        $params[] = $image_url;
    }
}

$params[] = $id;

try {
    $stmt = $conn->prepare("UPDATE livestock SET type = ?, category = ?, weight = ?, age = ?, price = ?, status = ?, health_condition = ?{$image_sql} WHERE id = ?");
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Data hewan berhasil diperbarui']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
