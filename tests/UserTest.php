<?php
// User Unit Tests
global $db;

it('dapat membuat user baru di database', function() use ($db) {
    // Arrange
    $user = new User($db);
    $user->name = "Ahmad Dani";
    $user->email = "ahmad@email.com";
    $user->password = password_hash("sandi123", PASSWORD_DEFAULT);
    $user->role = "customer";

    // Act
    $result = $user->create();

    // Assert
    if (!$result) {
        throw new Exception("Gagal menyimpan user ke database");
    }

    // Verify in Database
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(["ahmad@email.com"]);
    $count = $stmt->fetchColumn();
    if ($count != 1) {
        throw new Exception("User tidak ditemukan di database setelah create()");
    }
});

it('dapat mencari user berdasarkan email', function() use ($db) {
    // Arrange
    $user = new User($db);

    // Act
    $found = $user->findByEmail("ahmad@email.com");

    // Assert
    if (!$found) {
        throw new Exception("User ber-email ahmad@email.com gagal ditemukan");
    }
    if ($user->name !== "Ahmad Dani") {
        throw new Exception("Nama user tidak cocok: " . $user->name);
    }
});

// 🔴 RED TEST: Menguji fitur updateProfile() yang BELUM diimplementasikan
it('dapat memperbarui data profil (telepon dan alamat)', function() use ($db) {
    // Arrange
    $user = new User($db);
    $user->findByEmail("ahmad@email.com");

    // Act - Memanggil fungsi baru yang belum ada di class User
    $updateResult = $user->updateProfile("08123456789", "Jalan Raya Pantura No. 12");

    // Assert
    if (!$updateResult) {
        throw new Exception("Gagal melakukan pembaruan profil");
    }

    // Verifikasi perubahan di instance objek saat ini
    if ($user->phone !== "08123456789" || $user->address !== "Jalan Raya Pantura No. 12") {
        throw new Exception("Data profil pada objek user tidak ter-update");
    }

    // Verifikasi perubahan tersimpan permanen di database
    $stmt = $db->prepare("SELECT phone, address FROM users WHERE email = ?");
    $stmt->execute(["ahmad@email.com"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row['phone'] !== "08123456789" || $row['address'] !== "Jalan Raya Pantura No. 12") {
        throw new Exception("Perubahan profil tidak tersimpan di database");
    }
});
