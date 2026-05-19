<?php
ob_start();
session_start();
require_once '../../config/database.php';
require_once '../../models/User.php';

function redirectWithError($msg, $redirect = '') {
    $_SESSION['error'] = $msg;
    $back = $redirect ? "/lautan-ternak-pantura/views/auth/login?action=register&redirect=" . urlencode($redirect) : "/lautan-ternak-pantura/views/auth/register";
    header("Location: $back");
    ob_end_flush();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = isset($_POST['role']) ? $_POST['role'] : 'customer';
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    if (empty($name) || empty($email) || empty($password)) {
        redirectWithError('Semua field wajib diisi.', $redirect);
    }

    if (!isset($conn)) {
        redirectWithError('Koneksi database gagal.', $redirect);
    }

    $userModel = new User($conn);

    // Cek email exist
    if ($userModel->findByEmail($email)) {
        redirectWithError('Email sudah terdaftar.', $redirect);
    }

    $userModel->name = $name;
    $userModel->email = $email;
    $userModel->password = password_hash($password, PASSWORD_BCRYPT);
    $userModel->role = $role;

    if ($userModel->create()) {
        // Auto-login after registration
        $userModel->findByEmail($email);
        $_SESSION['user_id'] = $userModel->id;
        $_SESSION['email'] = $userModel->email;
        $_SESSION['name'] = $userModel->name;
        $_SESSION['role'] = $userModel->role;

        $location = $redirect ?: '/lautan-ternak-pantura/views/customer/dashboard';
        header("Location: $location");
        ob_end_flush();
        exit;
    }

    redirectWithError('Registrasi gagal. Coba lagi.', $redirect);
}

header("Location: /lautan-ternak-pantura/views/auth/register");
ob_end_flush();
exit;
