<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($conn)) {
    die("Database connection is not available.\n");
}

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS qurban_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        nomor_registrasi VARCHAR(50) UNIQUE NOT NULL,
        nama_pequrban VARCHAR(150) NOT NULL,
        bin_binti VARCHAR(150) NOT NULL,
        no_wa VARCHAR(20) NOT NULL,
        alamat TEXT NOT NULL,
        jenis_qurban VARCHAR(50) NOT NULL,
        paket_qurban VARCHAR(100) NOT NULL,
        harga_target DECIMAL(15,2) NOT NULL,
        pola_tabungan VARCHAR(50) NOT NULL,
        nominal_target_setoran DECIMAL(15,2) NOT NULL,
        target_lunas_bulan VARCHAR(50) NOT NULL,
        target_lunas_tahun INT NOT NULL DEFAULT 2027,
        opsi_penyaluran VARCHAR(150) NOT NULL,
        alamat_pengiriman TEXT DEFAULT NULL,
        hadir_penyembelihan BOOLEAN DEFAULT FALSE,
        nama_sertifikat VARCHAR(150) NOT NULL,
        catatan TEXT DEFAULT NULL,
        persetujuan BOOLEAN DEFAULT FALSE,
        status ENUM('Draft', 'Aktif', 'Lunas', 'Qurban Diproses', 'Selesai') DEFAULT 'Draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $conn->exec($sql);
    echo "Table 'qurban_registrations' created successfully!\n";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
