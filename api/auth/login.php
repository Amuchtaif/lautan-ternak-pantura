<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Simulating login for demo purposes
    // In production, verify against users table with password_verify
    
    $_SESSION['user_id'] = 1;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = 'Demo User';
    
    if (strpos($email, 'admin') !== false) {
        $_SESSION['role'] = 'admin';
        $_SESSION['name'] = 'Admin Utama';
        header("Location: /lautan-ternak-pantura/views/admin/dashboard.php");
    } elseif (strpos($email, 'breeder') !== false) {
        $_SESSION['role'] = 'breeder';
        $_SESSION['name'] = 'Ahmad Peternak';
        header("Location: /lautan-ternak-pantura/views/breeder/dashboard.php");
    } else {
        $_SESSION['role'] = 'customer';
        $_SESSION['name'] = 'Siti Customer';
        header("Location: /lautan-ternak-pantura/views/customer/dashboard.php");
    }
    exit;
}
?>
