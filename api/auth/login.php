<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (isset($conn)) {
        $userModel = new User($conn);
        
        if ($userModel->findByEmail($email)) {
            if (password_verify($password, $userModel->password)) {
                // Set session
                $_SESSION['user_id'] = $userModel->id;
                $_SESSION['email'] = $userModel->email;
                $_SESSION['name'] = $userModel->name;
                $_SESSION['role'] = $userModel->role;
                
                // Redirect based on role
                if ($userModel->role === 'admin') {
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
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit;
}
?>
