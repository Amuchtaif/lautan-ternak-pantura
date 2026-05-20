<?php
// Native PHP Test Runner for Lautan Ternak Pantura TDD

define('ANSI_RED', "\033[31m");
define('ANSI_GREEN', "\033[32m");
define('ANSI_YELLOW', "\033[33m");
define('ANSI_RESET', "\033[0m");

// Header
echo "==================================================\n";
echo "🧪  *LAUTAN TERNAK PANTURA TEST RUNNER*  🧪\n";
echo "==================================================\n\n";

// Set up In-Memory Database for isolation
try {
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create isolated tables mimicking MySQL schema
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            phone VARCHAR(50) DEFAULT '',
            address TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE livestock (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(100) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            breed VARCHAR(100) NOT NULL,
            age INTEGER NOT NULL,
            weight DECIMAL(10,2) NOT NULL,
            gender VARCHAR(50) NOT NULL,
            price DECIMAL(15,2) NOT NULL,
            purchase_price DECIMAL(15,2) DEFAULT 0,
            stock INTEGER NOT NULL,
            status VARCHAR(100) DEFAULT 'available',
            image VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_code VARCHAR(100) UNIQUE NOT NULL,
            customer_id INTEGER NOT NULL,
            livestock_id INTEGER NOT NULL,
            qty INTEGER NOT NULL,
            livestock_price_snapshot DECIMAL(15,2) NOT NULL,
            total_price DECIMAL(15,2) NOT NULL,
            status VARCHAR(100) DEFAULT 'pending',
            notes TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE savings_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            livestock_id INTEGER NOT NULL,
            target_amount DECIMAL(15,2) NOT NULL,
            monthly_installment DECIMAL(15,2) NOT NULL,
            status VARCHAR(100) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE savings_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plan_id INTEGER NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            proof_of_payment VARCHAR(255) NOT NULL,
            status VARCHAR(100) DEFAULT 'pending',
            verified_by INTEGER DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

} catch (Exception $e) {
    echo ANSI_RED . "❌ GAGAL MEMBUAT DATABASE ISOLASI: " . $e->getMessage() . ANSI_RESET . "\n";
    exit(1);
}

// Require our models
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Livestock.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Savings.php';

// Simple Test Runner Engine
$testsPassed = 0;
$testsFailed = 0;

function it($description, $fn) {
    global $testsPassed, $testsFailed;
    try {
        $fn();
        echo ANSI_GREEN . "  ✔ PASSED: " . $description . ANSI_RESET . "\n";
        $testsPassed++;
    } catch (Throwable $e) {
        echo ANSI_RED . "  ✖ FAILED: " . $description . ANSI_RESET . "\n";
        echo ANSI_YELLOW . "    Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . ANSI_RESET . "\n";
        $testsFailed++;
    }
}

// ==========================================
// 🚀 RUN TEST SUITES
// ==========================================
echo "🚀 Running Tests...\n\n";

echo "👤 [TEST SUITE: User & Auth]\n";
require_once __DIR__ . '/UserTest.php';

echo "\n🛒 [TEST SUITE: Marketplace Sale]\n";
require_once __DIR__ . '/MarketplaceSaleTest.php';

echo "\n👑 [TEST SUITE: Admin Manual Sale]\n";
require_once __DIR__ . '/AdminManualSaleTest.php';

echo "\n📈 [TEST SUITE: Savings Plan]\n";
require_once __DIR__ . '/SavingsTest.php';

// Summary
echo "\n==================================================\n";
echo "📊 *HASIL UJI COBA TDD* 📊\n";
echo "==================================================\n";
echo ANSI_GREEN . "  ✔ Berhasil: " . $testsPassed . ANSI_RESET . "\n";
if ($testsFailed > 0) {
    echo ANSI_RED . "  ✖ Gagal   : " . $testsFailed . ANSI_RESET . "\n";
    echo ANSI_RED . "🔴 STATUS: RED (Perlu Perbaikan Kode / Implementasi Fitur)\n" . ANSI_RESET;
} else {
    echo ANSI_GREEN . "🟢 STATUS: GREEN (Semua Uji Coba Lolos 100%!)\n" . ANSI_RESET;
}
echo "==================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
