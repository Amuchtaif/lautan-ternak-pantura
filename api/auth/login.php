<?php
ob_start();
session_start();
require_once '../../config/database.php';
require_once '../../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    if (isset($conn)) {
        $userModel = new User($conn);
        
        if ($userModel->findByEmail($email)) {
            if (password_verify($password, $userModel->password)) {
                // Set session
                $_SESSION['user_id'] = $userModel->id;
                $_SESSION['email'] = $userModel->email;
                $_SESSION['name'] = $userModel->name;
                $_SESSION['role'] = $userModel->role;
                
                // If redirect param exists, go there; otherwise role-based default
                if ($redirect) {
                    header("Location: " . $redirect);
                } elseif ($userModel->role === 'admin') {
                    header("Location: /lautan-ternak-pantura/views/admin/dashboard");
                } elseif ($userModel->role === 'breeder') {
                    header("Location: /lautan-ternak-pantura/views/breeder/dashboard");
                } else {
                    header("Location: /lautan-ternak-pantura/views/customer/dashboard");
                }
                exit;
            } else {
                $_SESSION['error'] = 'Password salah.';
            }
        } else {
            $_SESSION['error'] = 'Email tidak ditemukan.';
        }
    } else {
        $_SESSION['error'] = 'Koneksi database gagal.';
    }
    
    // Redirect back to login on failure
    $back = $redirect ? "/lautan-ternak-pantura/views/auth/login?redirect=" . urlencode($redirect) : "/lautan-ternak-pantura/views/auth/login";
    header("Location: $back");
    exit;
}
?>
