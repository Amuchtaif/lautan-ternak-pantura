CREATE DATABASE IF NOT EXISTS lautan_ternak_pantura;
USE lautan_ternak_pantura;

-- Disable foreign key checks for clean migration
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS purchase_payments;
DROP TABLE IF EXISTS qurban_registrations;
DROP TABLE IF EXISTS sale_payments;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS sohibul_qurban;
DROP TABLE IF EXISTS savings_transactions;
DROP TABLE IF EXISTS savings_plans;
DROP TABLE IF EXISTS livestock;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female') NULL,
    role ENUM('admin', 'customer', 'breeder') DEFAULT 'customer',
    profile_photo VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL DEFAULT NULL,
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    city VARCHAR(100) DEFAULT '',
    province VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE livestock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livestock_code VARCHAR(50) UNIQUE NOT NULL,
    peternak_name VARCHAR(150) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    age INT NOT NULL, -- in months
    weight DECIMAL(5,2) NOT NULL, -- in kg
    purchase_price DECIMAL(15,2) NOT NULL,
    selling_price DECIMAL(15,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    status ENUM('available', 'reserved', 'sold', 'inactive') DEFAULT 'available',
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE savings_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_code VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    livestock_id INT NULL,
    target_type ENUM('livestock', 'manual') DEFAULT 'livestock',
    livestock_target VARCHAR(150) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    monthly_target DECIMAL(15,2) NOT NULL,
    duration_month INT NOT NULL,
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    status ENUM('active', 'completed', 'overdue', 'cancelled') DEFAULT 'active',
    notes TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL
);

CREATE TABLE savings_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    savings_plan_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_proof VARCHAR(255) NOT NULL,
    transaction_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (savings_plan_id) REFERENCES savings_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE sohibul_qurban (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    relationship VARCHAR(50) DEFAULT 'self',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES savings_plans(id) ON DELETE CASCADE
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_code VARCHAR(50) UNIQUE NOT NULL,
    livestock_id INT NOT NULL,
    peternak_name VARCHAR(150) NOT NULL,
    qty INT NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    total_purchase DECIMAL(15,2) NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
    notes TEXT DEFAULT NULL,
    payment_type ENUM('dp', 'lunas') DEFAULT 'lunas',
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_code VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) DEFAULT '',
    livestock_id INT NOT NULL,
    livestock_name VARCHAR(150) NOT NULL,
    peternak_name VARCHAR(150) NOT NULL,
    qty INT NOT NULL,
    selling_price_snapshot DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    payment_type ENUM('dp', 'lunas') NOT NULL,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    sale_status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_proof VARCHAR(255) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sale_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    payment_code VARCHAR(50) UNIQUE NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    payment_note TEXT DEFAULT NULL,
    payment_proof VARCHAR(255) DEFAULT NULL,
    payment_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE purchase_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    payment_code VARCHAR(50) UNIQUE NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qurban_registrations (
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

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
