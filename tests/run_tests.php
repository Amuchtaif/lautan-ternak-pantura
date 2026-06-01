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
    
    // Register MySQL DATE_FORMAT polyfill for SQLite TDD isolation
    $db->sqliteCreateFunction('DATE_FORMAT', function($date, $format) {
        if (!$date) return null;
        $timestamp = strtotime($date);
        if (!$timestamp) return null;
        $phpFormat = str_replace(
            ['%Y', '%m', '%d', '%H', '%i', '%s'],
            ['Y', 'm', 'd', 'H', 'i', 's'],
            $format
        );
        return date($phpFormat, $timestamp);
    }, 2);
    
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
            livestock_code VARCHAR(100) UNIQUE NOT NULL,
            peternak_name VARCHAR(150) NOT NULL,
            breed VARCHAR(100) NOT NULL,
            age INTEGER NOT NULL,
            weight DECIMAL(10,2) NOT NULL,
            gender VARCHAR(50) NOT NULL,
            purchase_price DECIMAL(15,2) DEFAULT 0,
            selling_price DECIMAL(15,2) NOT NULL,
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
            plan_code VARCHAR(100) UNIQUE NOT NULL,
            customer_id INTEGER NOT NULL,
            livestock_target VARCHAR(150) NOT NULL,
            target_amount DECIMAL(15,2) NOT NULL,
            current_amount DECIMAL(15,2) DEFAULT 0,
            monthly_target DECIMAL(15,2) NOT NULL,
            duration_month INTEGER NOT NULL,
            start_date DATE NOT NULL,
            target_date DATE NOT NULL,
            status VARCHAR(100) DEFAULT 'active',
            notes TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE savings_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            savings_plan_id INTEGER NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_proof VARCHAR(255) NOT NULL,
            transaction_status VARCHAR(100) DEFAULT 'pending',
            verified_by INTEGER DEFAULT NULL,
            verified_at DATETIME DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $db->exec("
        CREATE TABLE purchases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_code VARCHAR(100) UNIQUE NOT NULL,
            livestock_id INTEGER NOT NULL,
            peternak_name VARCHAR(150) NOT NULL,
            qty INTEGER NOT NULL,
            purchase_price DECIMAL(15,2) NOT NULL,
            total_purchase DECIMAL(15,2) NOT NULL,
            amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0,
            payment_type VARCHAR(50) DEFAULT 'lunas',
            notes TEXT DEFAULT NULL,
            purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER NOT NULL
        )
    ");

    $db->exec("
        CREATE TABLE purchase_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_id INTEGER NOT NULL,
            payment_code VARCHAR(100) UNIQUE NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_code VARCHAR(100) UNIQUE NOT NULL,
            customer_name VARCHAR(150) NOT NULL,
            customer_phone VARCHAR(50) DEFAULT '',
            livestock_id INTEGER NOT NULL,
            livestock_name VARCHAR(150) NOT NULL,
            peternak_name VARCHAR(150) NOT NULL,
            qty INTEGER NOT NULL,
            selling_price_snapshot DECIMAL(15,2) NOT NULL,
            total_price DECIMAL(15,2) NOT NULL,
            payment_type VARCHAR(50) NOT NULL,
            payment_status VARCHAR(50) DEFAULT 'unpaid',
            sale_status VARCHAR(50) DEFAULT 'pending',
            notes TEXT DEFAULT NULL,
            payment_method VARCHAR(50) DEFAULT NULL,
            payment_proof VARCHAR(255) DEFAULT NULL,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE sale_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            payment_code VARCHAR(100) UNIQUE NOT NULL,
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            payment_method VARCHAR(50) NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            payment_note TEXT DEFAULT NULL,
            payment_proof VARCHAR(255) DEFAULT NULL,
            payment_status VARCHAR(50) DEFAULT 'pending',
            created_by INTEGER NOT NULL,
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
require_once __DIR__ . '/../models/Purchase.php';
require_once __DIR__ . '/../models/Report.php';

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

echo "\n📦 [TEST SUITE: Purchase & Inventory Sync]\n";
require_once __DIR__ . '/PurchaseTest.php';

echo "\n📊 [TEST SUITE: Admin Sales Reports]\n";
require_once __DIR__ . '/ReportTest.php';

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
