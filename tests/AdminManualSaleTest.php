<?php
// Admin Manual Sale Integration Tests
global $db;

it('dapat mencatat transaksi penjualan manual oleh admin untuk pelanggan walk-in', function() use ($db) {
    // Arrange
    // Register walk-in user on the fly
    $customer = new User($db);
    $customer->name = "Pelanggan Walk-In";
    $customer->email = "walkin_123@email.com";
    $customer->password = "password123";
    $customer->role = "customer";
    $customer->create();

    $stmtC = $db->query("SELECT id FROM users WHERE email = 'walkin_123@email.com'");
    $customerId = $stmtC->fetchColumn();

    // Register offline manual custom livestock on the fly
    $livestock = new Livestock($db);
    $livestock->create([
        'code' => 'LTP-MAN-123',
        'name' => 'Kambing PE Offline',
        'category' => 'aqiqah',
        'breed' => 'PE',
        'age' => 12,
        'weight' => 45.00,
        'gender' => 'male',
        'price' => 3500000.00,
        'stock' => 1,
        'status' => 'sold' // Sold immediately
    ]);

    $stmtL = $db->query("SELECT id FROM livestock WHERE code = 'LTP-MAN-123'");
    $livestockId = $stmtL->fetchColumn();

    // Act - Create Order manually
    $order = new Order($db);
    $orderData = [
        'order_code' => 'LTP-ORD-MAN-01',
        'customer_id' => $customerId,
        'livestock_id' => $livestockId,
        'qty' => 1,
        'livestock_price_snapshot' => 3500000.00,
        'total_price' => 3500000.00,
        'status' => 'paid', // Cash sale is instantly marked paid
        'notes' => 'Transaksi langsung di toko oleh Admin'
    ];

    $orderId = $order->create($orderData);

    // Assert
    if (!$orderId) {
        throw new Exception("Gagal mencatat transaksi manual");
    }

    $checkOrder = $order->getById($orderId);
    if ($checkOrder['status'] !== 'paid') {
        throw new Exception("Status order manual tidak valid: " . $checkOrder['status']);
    }
    if ($checkOrder['customer_name'] !== 'Pelanggan Walk-In') {
        throw new Exception("Nama pembeli manual salah");
    }
});

// 🔴 RED TEST: Menguji method penulisan deskripsi otomatis generateOfflineSummary()
it('dapat menghasilkan ringkasan transaksi offline otomatis untuk catatan order', function() use ($db) {
    // Arrange
    $order = new Order($db);

    // Act
    // Memanggil method generateOfflineSummary yang belum ada di class Order
    $summary = $order->generateOfflineSummary("Pelanggan Walk-In", "Kambing PE Offline", 3500000.00);

    // Assert
    $expected = "[Transaksi Offline] Pembeli: Pelanggan Walk-In | Hewan: Kambing PE Offline | Total: Rp 3.500.000";
    if ($summary !== $expected) {
        throw new Exception("Format ringkasan tidak sesuai. Dihasilkan: " . $summary);
    }
});
