<?php
require_once '../../config/database.php';
require_once '../../helpers/AuthHelper.php';
require_once '../../services/AuthService.php';

AuthHelper::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/auth/login');
    exit;
}

$redirect = $_POST['redirect'] ?? '';

if (!AuthHelper::validateCsrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Sesi form kedaluwarsa. Silakan coba lagi.';
    header('Location: /lautan-ternak-pantura/auth/login' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

if (!isset($conn)) {
    $_SESSION['error'] = 'Koneksi database gagal.';
    header('Location: /lautan-ternak-pantura/auth/login');
    exit;
}

$service = new AuthService($conn);
$result = $service->login($_POST['email'] ?? '', $_POST['password'] ?? '');

if (!$result['success']) {
    $_SESSION['error'] = $result['message'];
    header('Location: /lautan-ternak-pantura/auth/login' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

header('Location: ' . ($redirect ?: AuthHelper::dashboardUrl($_SESSION['role'] ?? 'customer')));
exit;
