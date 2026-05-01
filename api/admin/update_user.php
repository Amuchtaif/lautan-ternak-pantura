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

// Debug: Check if POST is received
if (empty($_POST)) {
    // Try reading raw input if POST is empty (sometimes happens with certain server configs)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data) {
        $_POST = $data;
    }
}

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$role = $_POST['role'] ?? null;

if (!$id) { echo json_encode(['success' => false, 'message' => 'ID User tidak terbaca']); exit(); }
if (!$name) { echo json_encode(['success' => false, 'message' => 'Nama wajib diisi']); exit(); }
if (!$email) { echo json_encode(['success' => false, 'message' => 'Email wajib diisi']); exit(); }
if (!$role) { echo json_encode(['success' => false, 'message' => 'Role wajib diisi']); exit(); }

try {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$name, $email, $role, $id]);

    echo json_encode(['success' => true, 'message' => 'Data pengguna berhasil diperbarui']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
