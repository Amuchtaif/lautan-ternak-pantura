<?php
require_once '../../config/database.php';
require_once '../../helpers/AuthHelper.php';
require_once '../../services/AuthService.php';

AuthHelper::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/auth/register');
    exit;
}

$redirect = $_POST['redirect'] ?? '';

if (!AuthHelper::validateCsrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['auth_errors'] = ['csrf' => 'Sesi form kedaluwarsa. Silakan coba lagi.'];
    $_SESSION['old_register'] = $_POST;
    header('Location: /lautan-ternak-pantura/auth/register' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

if (!isset($conn)) {
    $_SESSION['auth_errors'] = ['database' => 'Koneksi database gagal.'];
    $_SESSION['old_register'] = $_POST;
    header('Location: /lautan-ternak-pantura/auth/register');
    exit;
}

$service = new AuthService($conn);
$result = $service->registerCustomer($_POST);

if (!$result['success']) {
    $_SESSION['auth_errors'] = $result['errors'];
    $_SESSION['old_register'] = $_POST;
    header('Location: /lautan-ternak-pantura/auth/register' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

$_SESSION['success'] = 'Registrasi berhasil. Selamat datang sebagai Sohibul Qurban.';
header('Location: ' . ($redirect ?: '/lautan-ternak-pantura/customer/dashboard'));
exit;
