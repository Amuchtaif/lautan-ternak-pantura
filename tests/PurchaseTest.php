<?php
// Test Suite for Purchase Model & Inventory Synchronization

it("dapat mencatat transaksi pembelian stok hewan baru dan menginkremen stok inventori", function() use ($db) {
    $livestockModel = new Livestock($db);
    $purchaseModel = new Purchase($db);

    // 1. Create a dummy user who records the purchase
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin Test', 'admin_purch@example.com', 'pass', 'admin']);
    $adminId = $db->lastInsertId();

    // 2. Create a livestock record with 0 stock
    $livestockCode = 'LIV-PUR-TEST-1';
    $livestockData = [
        'livestock_code' => $livestockCode,
        'peternak_name' => 'Pak Bambang',
        'breed' => 'Sapi Bali',
        'age' => 12,
        'weight' => 200.0,
        'gender' => 'male',
        'purchase_price' => 15000000,
        'selling_price' => 18000000,
        'stock' => 0,
        'status' => 'available',
        'image' => null,
        'description' => 'Test'
    ];
    $livestockModel->create($livestockData);
    $livestockId = $db->lastInsertId();

    // Verify initial stock is 0
    $liveBefore = $livestockModel->getById($livestockId);
    if ($liveBefore['stock'] !== 0) {
        throw new Exception("Initial stock should be 0.");
    }

    // 3. Record purchase for 3 quantity
    $purchaseData = [
        'purchase_code' => 'PUR-CODE-001',
        'livestock_id' => $livestockId,
        'peternak_name' => 'Pak Bambang',
        'qty' => 3,
        'purchase_price' => 15000000,
        'notes' => 'Test notes',
        'created_by' => $adminId,
        'cash_account_id' => 1,
        'purchased_at' => date('Y-m-d H:i:s')
    ];
    $purchaseModel->create($purchaseData);
    $purchaseId = $db->lastInsertId();

    // 4. Verify purchase was recorded and livestock stock incremented
    $purchase = $purchaseModel->getById($purchaseId);
    if (!$purchase || intval($purchase['qty']) !== 3) {
        throw new Exception("Purchase qty should be 3.");
    }

    $liveAfter = $livestockModel->getById($livestockId);
    if (intval($liveAfter['stock']) !== 3) {
        throw new Exception("Livestock stock should have incremented to 3, got: " . $liveAfter['stock']);
    }
});

it("dapat memperbarui data transaksi pembelian dan menyesuaikan perbedaan jumlah stok dengan benar", function() use ($db) {
    $livestockModel = new Livestock($db);
    $purchaseModel = new Purchase($db);

    // Get the purchase created in previous test or query it
    $stmt = $db->query("SELECT id FROM purchases LIMIT 1");
    $purchRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $purchaseId = $purchRow['id'];

    $purchaseBefore = $purchaseModel->getById($purchaseId);
    $livestockId = $purchaseBefore['livestock_id'];

    // Update purchase qty to 5 (difference of +2)
    // and update other fields
    $updateData = [
        'livestock_name' => 'Limosin Premium',
        'breed' => 'sapi',
        'weight' => 250.0,
        'qty' => 5,
        'purchase_price' => 16000000,
        'selling_price' => 19500000,
        'notes' => 'Updated notes'
    ];

    $purchaseModel->update($purchaseId, $updateData);

    // Verify purchase qty updated
    $purchaseAfter = $purchaseModel->getById($purchaseId);
    if (intval($purchaseAfter['qty']) !== 5) {
        throw new Exception("Updated purchase qty should be 5, got: " . $purchaseAfter['qty']);
    }

    // Verify livestock stock adjusted (+2 from 3 is 5)
    $liveAfter = $livestockModel->getById($livestockId);
    if (intval($liveAfter['stock']) !== 5) {
        throw new Exception("Adjusted livestock stock should be 5, got: " . $liveAfter['stock']);
    }

    // Verify other livestock details synced
    if ($liveAfter['breed'] !== 'Sapi Limosin Premium') {
        throw new Exception("Livestock breed should be Sapi Limosin Premium, got: " . $liveAfter['breed']);
    }
    if (floatval($liveAfter['weight']) !== 250.0) {
        throw new Exception("Livestock weight should be 250.0.");
    }
    if (floatval($liveAfter['selling_price']) !== 19500000.0) {
        throw new Exception("Livestock selling price should be 19500000.0.");
    }
});

it("dapat menghapus catatan pembelian dan mendikremen stok secara otomatis", function() use ($db) {
    $livestockModel = new Livestock($db);
    $purchaseModel = new Purchase($db);

    $stmt = $db->query("SELECT id FROM purchases LIMIT 1");
    $purchRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $purchaseId = $purchRow['id'];

    $purchase = $purchaseModel->getById($purchaseId);
    $livestockId = $purchase['livestock_id'];

    // Delete purchase
    $purchaseModel->delete($purchaseId);

    // Verify purchase deleted
    $purchaseDeleted = $purchaseModel->getById($purchaseId);
    if ($purchaseDeleted) {
        throw new Exception("Purchase should be deleted.");
    }

    // Verify livestock stock decremented back by 5 (becomes 0)
    $liveAfter = $livestockModel->getById($livestockId);
    if (intval($liveAfter['stock']) !== 0) {
        throw new Exception("Livestock stock should have decremented back to 0, got: " . $liveAfter['stock']);
    }
    if ($liveAfter['status'] !== 'sold') {
        throw new Exception("Livestock status should be sold if stock is 0.");
    }
});

it("tidak akan melakukan dobel penamaan (breed duplication) jika livestock_name sudah diawali breed", function() use ($db) {
    $livestockModel = new Livestock($db);
    $purchaseModel = new Purchase($db);

    // 1. Create a livestock record
    $livestockCode = 'LIV-DOUBLE-TEST';
    $livestockData = [
        'livestock_code' => $livestockCode,
        'peternak_name' => 'Pak Bambang',
        'breed' => 'Domba Garut',
        'age' => 8,
        'weight' => 45.0,
        'gender' => 'male',
        'purchase_price' => 2000000,
        'selling_price' => 3000000,
        'stock' => 1,
        'status' => 'available',
        'image' => null,
        'description' => 'Test double naming'
    ];
    $livestockModel->create($livestockData);
    $livestockId = $db->lastInsertId();

    // 2. Create a purchase
    $purchaseData = [
        'purchase_code' => 'PUR-DOUBLE-001',
        'livestock_id' => $livestockId,
        'peternak_name' => 'Pak Bambang',
        'qty' => 1,
        'purchase_price' => 2000000,
        'notes' => 'Test double naming',
        'created_by' => 1,
        'cash_account_id' => 1,
        'purchased_at' => date('Y-m-d H:i:s')
    ];
    $purchaseModel->create($purchaseData);
    $purchaseId = $db->lastInsertId();

    // 3. Update purchase with breed='domba' and livestock_name='domba garut'
    $updateData = [
        'livestock_name' => 'domba garut',
        'breed' => 'domba',
        'weight' => 50.0,
        'qty' => 1,
        'purchase_price' => 2100000,
        'selling_price' => 3200000,
        'notes' => 'Updated double naming'
    ];
    $purchaseModel->update($purchaseId, $updateData);

    $liveAfter = $livestockModel->getById($livestockId);
    if ($liveAfter['breed'] !== 'Domba garut') {
        throw new Exception("Livestock breed should be 'Domba garut', got: " . $liveAfter['breed']);
    }
});
