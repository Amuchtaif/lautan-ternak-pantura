<?php
// Marketplace Sale Integration Tests
global $db;

it('dapat melakukan pembelian hewan qurban di marketplace dengan sukses', function() use ($db) {
    // Arrange
    $livestock = new Livestock($db);
    $livestock->create([
        'code' => 'LTP-SAPI-01',
        'peternak_name' => 'Pak Ahmad Breeder',
        'name' => 'Sapi Limosin Super',
        'breed' => 'Limosin',
        'age' => 24,
        'weight' => 450.00,
        'gender' => 'male',
        'selling_price' => 25000000.00,
        'purchase_price' => 20000000.00,
        'stock' => 1,
        'status' => 'available'
    ]);

    // Fetch the inserted livestock id
    $stmt = $db->query("SELECT id FROM livestock WHERE livestock_code = 'LTP-SAPI-01'");
    $livestockId = $stmt->fetchColumn();

    // Create a customer
    $customer = new User($db);
    $customer->name = "Budi Santoso";
    $customer->email = "budi@email.com";
    $customer->password = "password123";
    $customer->role = "customer";
    $customer->create();

    // Fetch the inserted customer id
    $stmtC = $db->query("SELECT id FROM users WHERE email = 'budi@email.com'");
    $customerId = $stmtC->fetchColumn();

    // Act - Create Order
    $order = new Order($db);
    $orderData = [
        'order_code' => 'LTP-ORD-TEST-01',
        'customer_id' => $customerId,
        'livestock_id' => $livestockId,
        'qty' => 1,
        'livestock_price_snapshot' => 25000000.00,
        'total_price' => 25000000.00,
        'status' => 'waiting_payment',
        'notes' => 'Qurban atas nama Budi'
    ];

    $orderId = $order->create($orderData);
    if (!$orderId) {
        throw new Exception("Gagal membuat order baru");
    }

    // Reduce stock
    $stockReduced = $livestock->reduceStock($livestockId, 1);
    if (!$stockReduced) {
        throw new Exception("Gagal mengurangi stok");
    }

    // Assert
    $checkLivestock = $livestock->getById($livestockId);
    if ($checkLivestock['stock'] != 0) {
        throw new Exception("Stok hewan tidak berkurang menjadi 0");
    }
    if ($checkLivestock['status'] !== 'sold') {
        throw new Exception("Status hewan tidak berubah menjadi 'sold'");
    }

    $checkOrder = $order->getById($orderId);
    if ($checkOrder['status'] !== 'waiting_payment') {
        throw new Exception("Status order tidak valid: " . $checkOrder['status']);
    }
});

// 🔴 RED TEST: Menguji verifikasi stok pembelian menggunakan verifyPurchaseStock()
it('akan melemparkan Exception jika melakukan checkout melebihi jumlah stok yang tersedia', function() use ($db) {
    // Arrange
    $livestock = new Livestock($db);
    $stmt = $db->query("SELECT id FROM livestock WHERE livestock_code = 'LTP-SAPI-01'");
    $livestockId = $stmt->fetchColumn();

    // Act & Assert
    // Memanggil method verifyPurchaseStock yang belum ada di model Livestock
    $exceptionThrown = false;
    try {
        $livestock->verifyPurchaseStock($livestockId, 5); // Membeli 5 padahal stok 0 (setelah dikurangi di test atas)
    } catch (Exception $e) {
        $exceptionThrown = true;
        if (strpos($e->getMessage(), 'Stok tidak mencukupi') === false) {
            throw new Exception("Pesan error exception tidak sesuai: " . $e->getMessage());
        }
    }

    if (!$exceptionThrown) {
        throw new Exception("Exception gagal dilemparkan ketika stok tidak mencukupi");
    }
});
