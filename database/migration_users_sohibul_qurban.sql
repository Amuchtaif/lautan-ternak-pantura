USE lautan_ternak_pantura;

ALTER TABLE users
    ADD COLUMN full_name VARCHAR(150) NULL AFTER id,
    ADD COLUMN gender ENUM('male', 'female') NULL AFTER password,
    ADD COLUMN city VARCHAR(100) DEFAULT '' AFTER address,
    ADD COLUMN province VARCHAR(100) DEFAULT '' AFTER city,
    ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL AFTER role,
    ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER profile_photo,
    ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER status,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

UPDATE users SET full_name = name WHERE full_name IS NULL OR full_name = '';

ALTER TABLE users
    MODIFY full_name VARCHAR(150) NOT NULL,
    MODIFY role ENUM('admin', 'customer', 'breeder') DEFAULT 'customer';
