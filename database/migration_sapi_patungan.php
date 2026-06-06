<?php
require_once __DIR__ . '/../config/database.php';

if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>Akses khusus Administrator.</p>";
        exit;
    }
}

if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.\n");
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create savings_groups table
    $sqlGroups = "
    CREATE TABLE IF NOT EXISTS savings_groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_code VARCHAR(50) UNIQUE NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Menunggu Anggota',
        livestock_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($sqlGroups);
    echo "Table savings_groups created or already exists.\n";

    // 2. Alter savings_plans table status column type
    $conn->exec("ALTER TABLE savings_plans MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Aktif'");
    echo "Modified savings_plans status column to VARCHAR.\n";

    // Translate legacy status values
    $conn->exec("UPDATE savings_plans SET status = 'Aktif' WHERE status = 'active'");
    $conn->exec("UPDATE savings_plans SET status = 'Target Tercapai' WHERE status = 'completed'");
    $conn->exec("UPDATE savings_plans SET status = 'Selesai' WHERE status = 'done'");
    echo "Translated legacy status values.\n";

    // Check and add program_type column to savings_plans
    $stmt = $conn->prepare("SHOW COLUMNS FROM savings_plans LIKE 'program_type'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE savings_plans ADD COLUMN program_type VARCHAR(50) NOT NULL DEFAULT 'kambing'");
        echo "Added column program_type to savings_plans.\n";
    }

    // Check and add group_id column to savings_plans
    $stmt = $conn->prepare("SHOW COLUMNS FROM savings_plans LIKE 'group_id'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE savings_plans ADD COLUMN group_id INT NULL");
        $conn->exec("ALTER TABLE savings_plans ADD FOREIGN KEY (group_id) REFERENCES savings_groups(id) ON DELETE SET NULL");
        echo "Added column group_id to savings_plans with foreign key.\n";
    }

    // 3. Create notifications table
    $sqlNotif = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($sqlNotif);
    echo "Table notifications created or already exists.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

