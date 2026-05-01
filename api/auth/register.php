<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'customer';

    if (isset($conn)) {
        $userModel = new User($conn);
        
        // Cek email exist
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email sudah terdaftar.';
            header("Location: /lautan-ternak-pantura/views/auth/register");
            exit;
        }

        $userModel->name = $name;
        $userModel->email = $email;
        $userModel->password = password_hash($password, PASSWORD_BCRYPT);
        $userModel->role = $role;

        if ($userModel->create()) {
            $_SESSION['success'] = 'Registrasi berhasil. Silakan login.';
            header("Location: /lautan-ternak-pantura/views/auth/login");
            exit;
        } else {
            $_SESSION['error'] = 'Registrasi gagal. Coba lagi.';
        }
    } else {
        $_SESSION['error'] = 'Koneksi database gagal.';
    }
    
    // Redirect back to register on failure
    header("Location: /lautan-ternak-pantura/views/auth/register");
    exit;
}
?>
