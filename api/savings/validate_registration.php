<?php
require_once '../../config/database.php';
require_once '../../models/User.php';

header('Content-Type: application/json');

try {
    if (!isset($conn)) {
        throw new RuntimeException('Koneksi database tidak tersedia.');
    }

    $username = strtolower(trim($_GET['username'] ?? ''));
    $email = strtolower(trim($_GET['email'] ?? ''));
    $userModel = new User($conn);
    $errors = [];

    if ($username !== '') {
        if (!preg_match('/^[a-z0-9._-]{4,50}$/', $username)) {
            $errors['username'] = 'Username minimal 4 karakter dan hanya boleh huruf, angka, titik, strip, atau underscore.';
        } elseif ($userModel->usernameExists($username)) {
            $errors['username'] = 'Username sudah digunakan.';
        }
    }

    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($userModel->emailExists($email)) {
            $errors['email'] = 'Email sudah digunakan.';
        }
    }

    echo json_encode(['success' => true, 'valid' => empty($errors), 'errors' => $errors]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'valid' => false, 'errors' => ['general' => $e->getMessage()]]);
}
