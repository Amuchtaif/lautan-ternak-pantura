USE lautan_ternak_pantura;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS livestock;

-- 1. Recreate livestock table with required fields
CREATE TABLE livestock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    category ENUM('qurban', 'aqiqah') NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age INT NOT NULL, -- in months
    weight DECIMAL(5,2) NOT NULL, -- in kg
    gender ENUM('male', 'female') NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    stock INT NOT NULL DEFAULT 1,
    status ENUM('available', 'booked', 'sold') DEFAULT 'available',
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Recreate orders table with required fields
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    livestock_id INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    livestock_price_snapshot DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'waiting_payment', 'payment_review', 'paid', 'processing', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE
);

-- 3. Recreate payments table with required fields
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_proof VARCHAR(255) NOT NULL,
    payment_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 4. Create purchases table for store stock purchase tracking
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livestock_name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    selling_price DECIMAL(15,2) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    notes TEXT DEFAULT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed new livestock data
INSERT INTO livestock (code, name, category, breed, age, weight, gender, price, stock, status, image, description) VALUES
('LTP-S001', 'Sapi Limosin Jumbo', 'qurban', 'Limosin', 24, 450.50, 'male', 28000000.00, 2, 'available', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80', 'Sapi Limosin unggul, sehat, nafsu makan baik, cocok untuk qurban premium.'),
('LTP-S002', 'Sapi Bali Sehat', 'qurban', 'Bali', 22, 380.00, 'male', 22000000.00, 3, 'available', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&q=80', 'Sapi Bali pilihan, gesit, bersertifikat sehat dari dinas pertanian.'),
('LTP-K001', 'Kambing Etawa Super', 'aqiqah', 'Etawa', 12, 45.00, 'male', 4500000.00, 5, 'available', 'https://images.unsplash.com/photo-1524024973431-2ad916746881?auto=format&fit=crop&q=80', 'Kambing Etawa jantan bertubuh tegap, berbulu bersih, ideal untuk aqiqah.'),
('LTP-D001', 'Domba Garut Gagah', 'qurban', 'Garut', 18, 50.00, 'male', 5000000.00, 1, 'available', 'https://images.unsplash.com/photo-1484557985045-edf25e08da73?auto=format&fit=crop&q=80', 'Domba Garut tanduk indah, kokoh, gagah, sangat gagah untuk ibadah qurban.');

SET FOREIGN_KEY_CHECKS = 1;
