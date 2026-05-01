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
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Debug: Check if POST is received
if (empty($_POST)) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) { $_POST = $data; }
}

$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$role = $_POST['role'] ?? null;
$password = password_hash('password123', PASSWORD_DEFAULT);

if (!$name || !$email || !$role) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
    exit();
}

try {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $role, $password]);

    echo json_encode(['success' => true, 'message' => 'Pengguna baru berhasil ditambahkan.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
