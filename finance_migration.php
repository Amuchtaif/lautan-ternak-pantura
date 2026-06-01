<?php
require_once 'config/database.php';

// Security: limit to logged-in admin users or CLI
if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>Akses khusus Administrator Super.</p>";
        exit;
    }
}

if (!isset($conn)) {
    die("Error: Koneksi database tidak tersedia.");
}

$sql = "
-- Enable strict mode
SET FOREIGN_KEY_CHECKS = 0;

-- 1. cash_accounts Table
CREATE TABLE IF NOT EXISTS cash_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('cash', 'bank') NOT NULL DEFAULT 'bank',
    account_number VARCHAR(100) DEFAULT NULL,
    bank_name VARCHAR(100) DEFAULT NULL,
    opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. investors Table
CREATE TABLE IF NOT EXISTS investors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. investor_funds Table
CREATE TABLE IF NOT EXISTS investor_funds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investor_id INT NOT NULL,
    cash_account_id INT NOT NULL,
    date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    proof VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (investor_id) REFERENCES investors(id) ON DELETE CASCADE,
    FOREIGN KEY (cash_account_id) REFERENCES cash_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. operational_categories Table
CREATE TABLE IF NOT EXISTS operational_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. operational_expenses Table
CREATE TABLE IF NOT EXISTS operational_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    cash_account_id INT NOT NULL,
    date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT DEFAULT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES operational_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (cash_account_id) REFERENCES cash_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. cash_transactions Table
CREATE TABLE IF NOT EXISTS cash_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cash_account_id INT NOT NULL,
    transaction_type ENUM('MODAL_INVESTOR', 'OPERASIONAL', 'PEMBELIAN_HEWAN', 'PENJUALAN_HEWAN') NOT NULL,
    reference_type VARCHAR(100) NOT NULL,
    reference_id INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT DEFAULT NULL,
    cash_in DECIMAL(15,2) NOT NULL DEFAULT 0,
    cash_out DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance_after DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_account_id) REFERENCES cash_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
";

try {
    $conn->exec($sql);
    $logs = ["Tabel database keuangan berhasil dibuat."];

    // Seed Operational Categories
    $categories = [
        'Pakan Ternak',
        'Vitamin dan Obat',
        'Transportasi',
        'Survey Hewan',
        'Makan Bersama Buyer',
        'Perawatan Kandang',
        'Gaji Pekerja',
        'Administrasi',
        'Lain-lain'
    ];

    $catStmt = $conn->prepare("INSERT IGNORE INTO operational_categories (name) VALUES (?)");
    foreach ($categories as $cat) {
        $catStmt->execute([$cat]);
    }
    $logs[] = "Data kategori pengeluaran operasional berhasil di-seed.";

    // Seed Default Cash Accounts
    $checkAcc = $conn->query("SELECT COUNT(*) FROM cash_accounts")->fetchColumn();
    if ($checkAcc == 0) {
        $accounts = [
            [
                'name' => 'Kas Operasional',
                'type' => 'cash',
                'account_number' => '-',
                'bank_name' => '-',
                'opening_balance' => 5000000,
                'current_balance' => 5000000,
                'description' => 'Kas fisik tunai di kantor utama untuk pengeluaran kecil.'
            ],
            [
                'name' => 'BCA Operasional',
                'type' => 'bank',
                'account_number' => '1290384112',
                'bank_name' => 'BCA',
                'opening_balance' => 50000000,
                'current_balance' => 50000000,
                'description' => 'Rekening utama Bank BCA untuk seluruh transaksi modal masuk dan operasional besar.'
            ],
            [
                'name' => 'BSI Qurban',
                'type' => 'bank',
                'account_number' => '7119028341',
                'bank_name' => 'BSI',
                'opening_balance' => 100000000,
                'current_balance' => 100000000,
                'description' => 'Rekening khusus Bank Syariah Indonesia untuk menampung setoran tabungan qurban nasabah.'
            ]
        ];

        $accStmt = $conn->prepare("
            INSERT INTO cash_accounts 
            (name, type, account_number, bank_name, opening_balance, current_balance, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($accounts as $acc) {
            $accStmt->execute([
                $acc['name'],
                $acc['type'],
                $acc['account_number'],
                $acc['bank_name'],
                $acc['opening_balance'],
                $acc['current_balance'],
                $acc['description']
            ]);
        }
        $logs[] = "Rekening kas & bank bawaan berhasil di-seed.";
    } else {
        $logs[] = "Rekening kas sudah memiliki data, melewati seeding kas.";
    }

    $success = true;
} catch (PDOException $e) {
    $success = false;
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrasi Database Keuangan - LTP Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">
    <div class="bg-white rounded-3xl p-8 border border-gray-100 max-w-lg w-full shadow-xl space-y-6">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl <?php echo $success ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'; ?> flex items-center justify-center text-2xl">
                <i class="fas <?php echo $success ? 'fa-circle-check animate-bounce' : 'fa-triangle-exclamation animate-pulse'; ?>"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900">Status Migrasi Database</h1>
                <p class="text-xs text-gray-400 font-bold uppercase mt-1">Modul Keuangan Tahap 1</p>
            </div>
        </div>

        <div class="bg-gray-900 rounded-2xl p-5 font-mono text-xs text-gray-300 space-y-2.5 max-h-60 overflow-y-auto">
            <?php if ($success): ?>
                <?php foreach ($logs as $log): ?>
                    <p class="flex gap-2 text-emerald-400"><span class="text-gray-600">[OK]</span> <span><?php echo htmlspecialchars($log); ?></span></p>
                <?php endforeach; ?>
                <p class="text-white border-t border-gray-800 pt-2 font-bold uppercase tracking-wider text-center mt-4">Migrasi Selesai dengan Sukses!</p>
            <?php else: ?>
                <p class="text-red-400 flex gap-2"><span class="text-gray-600">[ERROR]</span> <span>Gagal mengeksekusi migrasi database:</span></p>
                <p class="bg-red-950/50 p-3 rounded-lg border border-red-900/50 text-red-300"><?php echo htmlspecialchars($errorMsg); ?></p>
            <?php endif; ?>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <a href="/lautan-ternak-pantura/views/admin/dashboard" class="bg-brand-primary hover:bg-brand-dark text-white px-6 py-3 rounded-xl font-black text-sm transition shadow-md shadow-brand-primary/10">
                <i class="fas fa-house mr-2"></i> Ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
