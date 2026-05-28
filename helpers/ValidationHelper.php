<?php

class ValidationHelper {
    public static function clean($value) {
        return trim(strip_tags((string)$value));
    }

    public static function validateRegister($data) {
        $errors = [];

        $fullName = self::clean($data['full_name'] ?? $data['name'] ?? '');
        $email = strtolower(self::clean($data['email'] ?? ''));
        $phone = self::clean($data['phone'] ?? '');
        $password = (string)($data['password'] ?? '');
        $passwordConfirm = (string)($data['password_confirm'] ?? $data['confirm_password'] ?? '');
        $gender = self::clean($data['gender'] ?? '');
        $address = self::clean($data['address'] ?? '');
        $city = self::clean($data['city'] ?? '');
        $province = self::clean($data['province'] ?? '');

        if (strlen($fullName) < 3) {
            $errors['full_name'] = 'Nama lengkap minimal 3 karakter.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        if (!preg_match('/^[0-9]{10,}$/', $phone)) {
            $errors['phone'] = 'Nomor WhatsApp hanya angka dan minimal 10 digit.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Konfirmasi password harus sama.';
        }

        if (!in_array($gender, ['male', 'female'], true)) {
            $errors['gender'] = 'Jenis kelamin wajib dipilih.';
        }

        if ($address === '') {
            $errors['address'] = 'Alamat lengkap wajib diisi.';
        }

        if ($city === '') {
            $errors['city'] = 'Kota wajib diisi.';
        }

        if ($province === '') {
            $errors['province'] = 'Provinsi wajib diisi.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'gender' => $gender,
                'address' => $address,
                'city' => $city,
                'province' => $province
            ]
        ];
    }
}
