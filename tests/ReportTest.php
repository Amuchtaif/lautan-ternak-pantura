<?php
// Report Unit and Integration Tests
global $db;

it('dapat menghitung metrik laba rugi laporan harian dengan margin nyata', function() use ($db) {
    // Clean tables
    $db->exec("DELETE FROM sales");
    $db->exec("DELETE FROM livestock");

    // Arrange: Create livestock with purchase and selling prices
    $db->exec("
        INSERT INTO livestock (id, livestock_code, peternak_name, breed, age, weight, gender, purchase_price, selling_price, stock, status)
        VALUES 
        (10, 'CODE-01', 'Breeder A', 'Limosin', 20, 300, 'male', 15000000.00, 20000000.00, 5, 'available'),
        (11, 'CODE-02', 'Breeder B', 'Madura', 18, 250, 'male', 10000000.00, 13000000.00, 3, 'available')
    ");

    // Arrange: Create sales
    // Sale 1: qty = 2, total_price = 40,000,000, margin = 2 * (20,000,000 - 15,000,000) = 10,000,000
    // Sale 2: qty = 1, total_price = 13,000,000, margin = 1 * (13,000,000 - 10,000,000) = 3,000,000
    $today = date('Y-m-d');
    $db->exec("
        INSERT INTO sales (id, invoice_code, customer_name, customer_phone, livestock_id, livestock_name, peternak_name, qty, selling_price_snapshot, total_price, payment_type, payment_status, sale_status, created_by, created_at)
        VALUES 
        (100, 'INV-01', 'Cust A', '081', 10, 'Limosin', 'Breeder A', 2, 20000000.00, 40000000.00, 'lunas', 'paid', 'completed', 1, '{$today} 10:00:00'),
        (101, 'INV-02', 'Cust B', '082', 11, 'Madura', 'Breeder B', 1, 13000000.00, 13000000.00, 'dp', 'partial', 'pending', 1, '{$today} 11:00:00')
    ");

    // Act
    $report = new Report($db);
    $summary = $report->getDailySummary($today);

    // Assert
    // Total transactions = 2
    if ($summary['total_transactions'] !== 2) {
        throw new Exception("Jumlah transaksi tidak sesuai: " . $summary['total_transactions']);
    }
    // Total sales = 40,000,000 + 13,000,000 = 53,000,000
    if ($summary['total_sales'] != 53000000.00) {
        throw new Exception("Total penjualan tidak sesuai: " . $summary['total_sales']);
    }
    // Total purchase cost = (2 * 15,000,000) + (1 * 10,000,000) = 40,000,000
    if ($summary['total_purchase_cost'] != 40000000.00) {
        throw new Exception("Total modal pembelian tidak sesuai: " . $summary['total_purchase_cost']);
    }
    // Total margin = 53,000,000 - 40,000,000 = 13,000,000
    if ($summary['total_margin'] != 13000000.00) {
        throw new Exception("Total margin keuntungan tidak sesuai: " . $summary['total_margin']);
    }
});

it('dapat memfilter laporan harian berdasarkan status transaksi', function() use ($db) {
    $today = date('Y-m-d');
    $report = new Report($db);

    // Act: Filter by 'completed'
    $summaryCompleted = $report->getDailySummary($today, 'completed');
    if ($summaryCompleted['total_transactions'] !== 1) {
        throw new Exception("Jumlah transaksi terfilter 'completed' tidak sesuai: " . $summaryCompleted['total_transactions']);
    }
    if ($summaryCompleted['total_sales'] != 40000000.00) {
        throw new Exception("Total penjualan terfilter 'completed' tidak sesuai: " . $summaryCompleted['total_sales']);
    }

    // Act: Filter by 'pending'
    $summaryPending = $report->getDailySummary($today, 'pending');
    if ($summaryPending['total_transactions'] !== 1) {
        throw new Exception("Jumlah transaksi terfilter 'pending' tidak sesuai: " . $summaryPending['total_transactions']);
    }
    if ($summaryPending['total_sales'] != 13000000.00) {
        throw new Exception("Total penjualan terfilter 'pending' tidak sesuai: " . $summaryPending['total_sales']);
    }
});

it('dapat menghitung metrik laporan bulanan dengan benar', function() use ($db) {
    $currentMonth = date('Y-m');
    $report = new Report($db);

    // Act
    $summary = $report->getMonthlySummary($currentMonth);

    // Assert
    if ($summary['total_transactions'] !== 2) {
        throw new Exception("Jumlah transaksi bulanan tidak sesuai: " . $summary['total_transactions']);
    }
    if ($summary['total_sales'] != 53000000.00) {
        throw new Exception("Total penjualan bulanan tidak sesuai: " . $summary['total_sales']);
    }
    if ($summary['total_purchase_cost'] != 40000000.00) {
        throw new Exception("Total modal pembelian bulanan tidak sesuai: " . $summary['total_purchase_cost']);
    }
    if ($summary['total_margin'] != 13000000.00) {
        throw new Exception("Total margin bulanan tidak sesuai: " . $summary['total_margin']);
    }
});
