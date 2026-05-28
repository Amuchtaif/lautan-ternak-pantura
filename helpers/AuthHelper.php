<?php

class AuthHelper {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function csrfToken() {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf($token) {
        self::start();
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function login($user) {
        self::start();
        session_regenerate_id(true);

        $fullName = $user['full_name'] ?? $user['name'] ?? '';
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['full_name'] = $fullName;
        $_SESSION['name'] = $fullName;
        $_SESSION['email'] = $user['email'] ?? '';
        $_SESSION['role'] = $user['role'] ?? 'customer';
        $_SESSION['is_login'] = true;
    }

    public static function logout() {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function dashboardUrl($role) {
        if ($role === 'admin') {
            return '/lautan-ternak-pantura/views/admin/dashboard';
        }

        if ($role === 'breeder') {
            return '/lautan-ternak-pantura/views/breeder/dashboard';
        }

        return '/lautan-ternak-pantura/customer/dashboard';
    }

    public static function requireLogin($role = null) {
        self::start();
        if (empty($_SESSION['is_login']) || empty($_SESSION['user_id'])) {
            header('Location: /lautan-ternak-pantura/auth/login');
            exit;
        }

        if ($role !== null && ($_SESSION['role'] ?? '') !== $role) {
            header('Location: ' . self::dashboardUrl($_SESSION['role'] ?? 'customer'));
            exit;
        }
    }
}
