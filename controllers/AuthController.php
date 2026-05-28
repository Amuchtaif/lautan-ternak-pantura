<?php
require_once 'helpers/AuthHelper.php';

class AuthController {
    public function login() {
        AuthHelper::start();
        AuthHelper::csrfToken();
        $redirectUrl = $_GET['redirect'] ?? '';
        require 'views/auth/login.php';
    }

    public function register() {
        AuthHelper::start();
        AuthHelper::csrfToken();
        $redirectUrl = $_GET['redirect'] ?? '';
        require 'views/auth/register.php';
    }

    public function logout() {
        AuthHelper::logout();
        header('Location: /lautan-ternak-pantura/auth/login');
        exit;
    }
}
