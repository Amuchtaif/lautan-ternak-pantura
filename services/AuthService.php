<?php
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/User.php';

class AuthService {
    private $users;

    public function __construct($db) {
        $this->users = new User($db);
    }

    public function registerCustomer($input) {
        $validation = ValidationHelper::validateRegister($input);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $data = $validation['data'];
        if ($this->users->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['email' => 'Email sudah digunakan.']];
        }

        $userId = $this->users->createCustomer([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'],
            'address' => $data['address'],
            'city' => $data['city'],
            'province' => $data['province']
        ]);

        $user = $this->users->findArrayById($userId);
        AuthHelper::login($user);
        $this->users->updateLastLogin($userId);

        return ['success' => true, 'user' => $user];
    }

    public function login($email, $password) {
        $email = strtolower(ValidationHelper::clean($email));
        $user = $this->users->findArrayByLogin($email);

        if (!$user || !password_verify((string)$password, $user['password'])) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return ['success' => false, 'message' => 'Akun tidak aktif.'];
        }

        AuthHelper::login($user);
        $this->users->updateLastLogin((int)$user['id']);

        return ['success' => true, 'user' => $user];
    }
}
