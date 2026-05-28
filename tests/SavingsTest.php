<?php
// Qurban Savings Plan Integration Tests
global $db;

it('dapat membuat rencana tabungan qurban baru', function() use ($db) {
    // Arrange
    // Create a customer
    $customer = new User($db);
    $customer->name = "Eko Prasetyo";
    $customer->email = "eko@email.com";
    $customer->password = "password123";
    $customer->role = "customer";
    $customer->create();

    $stmtC = $db->query("SELECT id FROM users WHERE email = 'eko@email.com'");
    $customerId = $stmtC->fetchColumn();

    // Create a livestock to save for
    $livestock = new Livestock($db);
    $livestock->create([
        'code' => 'LTP-SAPI-SAVE',
        'peternak_name' => 'Pak Joko Breeder',
        'name' => 'Sapi Bali',
        'breed' => 'Bali',
        'age' => 20,
        'weight' => 380.00,
        'gender' => 'male',
        'selling_price' => 20000000.00,
        'purchase_price' => 16000000.00,
        'stock' => 1,
        'status' => 'available'
    ]);

    $stmtL = $db->query("SELECT id FROM livestock WHERE livestock_code = 'LTP-SAPI-SAVE'");
    $livestockId = $stmtL->fetchColumn();

    // We insert directly to verify initial table creation state
    $stmt = $db->prepare("INSERT INTO savings_plans (plan_code, customer_id, livestock_target, target_amount, current_amount, monthly_target, duration_month, start_date, target_date, status) VALUES (?, ?, ?, ?, 0, ?, ?, DATE('now'), DATE('now', '+10 months'), 'active')");
    $result = $stmt->execute(['TQ-TEST-0001', $customerId, 'Sapi Bali', 20000000.00, 2000000.00, 10]);

    if (!$result) {
        throw new Exception("Gagal menyimpan rencana tabungan baru");
    }

    $planId = $db->lastInsertId();

    // Assert
    $stmtPlan = $db->prepare("SELECT * FROM savings_plans WHERE id = ?");
    $stmtPlan->execute([$planId]);
    $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

    if ($plan['target_amount'] != 20000000.00) {
        throw new Exception("Target tabungan tidak cocok");
    }
});

// 🔴 RED TEST 1: Menguji method getVerifiedTotal() yang belum ada di model Savings
it('dapat menghitung total tabungan yang sudah diverifikasi (verified)', function() use ($db) {
    // Arrange
    $stmtPlan = $db->query("SELECT id FROM savings_plans ORDER BY id DESC LIMIT 1");
    $planId = $stmtPlan->fetchColumn();

    // Insert multiple transactions (verified, pending, rejected)
    $stmtTx = $db->prepare("INSERT INTO savings_transactions (savings_plan_id, amount, payment_method, payment_proof, transaction_status) VALUES (?, ?, 'transfer_bank', ?, ?)");
    $stmtTx->execute([$planId, 5000000.00, 'proof1.jpg', 'verified']);
    $stmtTx->execute([$planId, 10000000.00, 'proof2.jpg', 'verified']);
    $stmtTx->execute([$planId, 2000000.00, 'proof3.jpg', 'pending']);  // Pending should NOT be summed
    $stmtTx->execute([$planId, 3000000.00, 'proof4.jpg', 'rejected']); // Rejected should NOT be summed

    // Act
    $savings = new Savings($db);
    $totalVerified = $savings->getVerifiedTotal($planId); // Memanggil method yang belum ada

    // Assert
    // Total verified should be 5M + 10M = 15M
    if ($totalVerified != 15000000.00) {
        throw new Exception("Total tabungan verified salah. Terhitung: " . $totalVerified);
    }
});

// 🔴 RED TEST 2: Menguji autocompletion rencana tabungan jika target terpenuhi
it('secara otomatis mengubah status tabungan menjadi completed jika target nominal terpenuhi', function() use ($db) {
    // Arrange
    $stmtPlan = $db->query("SELECT id FROM savings_plans ORDER BY id DESC LIMIT 1");
    $planId = $stmtPlan->fetchColumn();

    // Insert another verified transaction of 5M (Bringing total verified to 20M, meeting the 20M target!)
    $stmtTx = $db->prepare("INSERT INTO savings_transactions (savings_plan_id, amount, payment_method, payment_proof, transaction_status) VALUES (?, ?, 'transfer_bank', ?, 'verified')");
    $stmtTx->execute([$planId, 5000000.00, 'proof5.jpg']);

    // Act
    $savings = new Savings($db);
    $completed = $savings->checkAndCompletePlan($planId); // Memanggil method yang belum ada

    // Assert
    if (!$completed) {
        throw new Exception("checkAndCompletePlan mengembalikan nilai false");
    }

    // Verify database status changed to 'completed'
    $stmtCheck = $db->prepare("SELECT status FROM savings_plans WHERE id = ?");
    $stmtCheck->execute([$planId]);
    $status = $stmtCheck->fetchColumn();

    if ($status !== 'completed') {
        throw new Exception("Status rencana tabungan tidak berubah menjadi 'completed', status saat ini: " . $status);
    }
});
