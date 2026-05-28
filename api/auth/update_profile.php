<?php
ob_start();
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /lautan-ternak-pantura/views/customer/dashboard");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /lautan-ternak-pantura/auth/login");
    exit();
}

$userId = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$currentPw = $_POST['current_password'] ?? '';
$newPw = $_POST['new_password'] ?? '';
$confirmPw = $_POST['confirm_password'] ?? '';

$error = '';

if (empty($name) || empty($email)) {
    $error = 'Nama dan email wajib diisi.';
}

// Validate & update password if provided
if (empty($error) && !empty($newPw)) {
    if (strlen($newPw) < 8) {
        $error = 'Kata sandi baru minimal 8 karakter.';
    } elseif ($newPw !== $confirmPw) {
        $error = 'Konfirmasi kata sandi baru tidak cocok.';
    } elseif (empty($currentPw)) {
        $error = 'Masukkan kata sandi saat ini untuk mengubah.';
    }
}

if (!empty($error)) {
    header("Location: /lautan-ternak-pantura/views/customer/profile?error=" . urlencode($error));
    exit();
}

if (isset($conn)) {
    try {
        // Verify current password if changing password
        if (!empty($newPw)) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($currentPw, $row['password'])) {
                header("Location: /lautan-ternak-pantura/views/customer/profile?error=" . urlencode('Kata sandi saat ini salah.'));
                exit();
            }

            $hashed = password_hash($newPw, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $hashed, $userId]);
        } else {
            // Check email uniqueness (exclude self)
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $userId]);
            if ($check->fetch()) {
                header("Location: /lautan-ternak-pantura/views/customer/profile?error=" . urlencode('Email sudah digunakan akun lain.'));
                exit();
            }

            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $userId]);
        }

        // Update session name/email
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;

        header("Location: /lautan-ternak-pantura/views/customer/profile?success=1");
    } catch (PDOException $e) {
        header("Location: /lautan-ternak-pantura/views/customer/profile?error=" . urlencode('Database error: ' . $e->getMessage()));
    }
} else {
    header("Location: /lautan-ternak-pantura/views/customer/profile?error=" . urlencode('Koneksi database gagal.'));
}
exit();
