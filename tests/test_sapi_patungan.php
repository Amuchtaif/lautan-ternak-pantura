<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SavingsPlan.php';
require_once __DIR__ . '/../models/SavingsTransaction.php';

if (!isset($conn)) {
    die("Error: Database connection not available.\n");
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== STARTING COOP QURBAN GROUPING TESTS ===\n";

    // 1. Create 7 dummy customer users if they don't exist
    $dummyUserIds = [];
    for ($i = 1; $i <= 7; $i++) {
        $username = "dummysapi{$i}";
        $email = "dummysapi{$i}@test.com";
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $id = $stmt->fetchColumn();
        
        if (!$id) {
            $stmtInsert = $conn->prepare("
                INSERT INTO users (full_name, name, username, email, password, role, status)
                VALUES (?, ?, ?, ?, 'password_hash_dummy', 'customer', 'active')
            ");
            $stmtInsert->execute(["Dummy Sapi {$i}", "Dummy Sapi {$i}", $username, $email]);
            $id = $conn->lastInsertId();
        }
        $dummyUserIds[] = $id;
    }
    echo "Created/Verified 7 dummy customer users.\n";

    // 2. Fetch Sapi Limousin livestock ID & price
    $stmtSapi = $conn->query("SELECT id, selling_price FROM livestock WHERE breed LIKE '%Sapi%' AND status = 'available' LIMIT 1");
    $sapi = $stmtSapi->fetch(PDO::FETCH_ASSOC);
    if (!$sapi) {
        // Create a dummy sapi if not available
        $conn->exec("
            INSERT INTO livestock (livestock_code, peternak_name, breed, gender, age, weight, purchase_price, selling_price, stock, status)
            VALUES ('LTP-LIV-TEST-SAPI', 'Test Peternak', 'Sapi Brahman Test', 'male', 24, 350.00, 18000000, 21000000, 10, 'available')
        ");
        $sapiId = $conn->lastInsertId();
        $sapiPrice = 21000000;
        echo "Created dummy available Cow for testing.\n";
    } else {
        $sapiId = $sapi['id'];
        $sapiPrice = (float)$sapi['selling_price'];
    }

    $targetPerParticipant = round($sapiPrice / 7, 2);
    echo "Sapi Price: Rp " . number_format($sapiPrice) . " -> Target per Participant (1/7): Rp " . number_format($targetPerParticipant) . "\n";

    $planModel = new SavingsPlan($conn);
    $transactionModel = new SavingsTransaction($conn);

    // Clean any prior test groups/plans for clean assertions
    $conn->exec("DELETE FROM notifications WHERE message LIKE '%Dummy Sapi%' OR message LIKE '%SQ-%'");
    $conn->exec("DELETE FROM savings_plans WHERE customer_id IN (" . implode(',', $dummyUserIds) . ")");
    // Note: ON DELETE CASCADE will clean savings_transactions and sohibul_qurban
    $conn->exec("DELETE FROM savings_groups WHERE group_code LIKE 'SQ-%' AND id NOT IN (SELECT DISTINCT group_id FROM savings_plans WHERE group_id IS NOT NULL)");
    echo "Cleaned prior test data.\n";

    // Create a fresh group code sequence test
    $plans = [];
    for ($i = 0; $i < 7; $i++) {
        $planId = $planModel->create([
            'customer_id' => $dummyUserIds[$i],
            'livestock_id' => $sapiId,
            'livestock_target' => 'Sapi Limousin (1/7 Sapi)',
            'target_amount' => $targetPerParticipant,
            'monthly_target' => $targetPerParticipant / 10,
            'duration_month' => 10,
            'notes' => 'Test Sapi Patungan',
            'program_type' => 'sapi_patungan'
        ]);
        $plans[] = $planId;
    }
    echo "Registered 7 savings plans with program_type = 'sapi_patungan'.\n";

    // Check grouping triggers during payments
    for ($i = 0; $i < 7; $i++) {
        $planId = $plans[$i];
        echo "Processing deposit of Rp " . number_format($targetPerParticipant) . " for Plan ID {$planId} (Customer {$dummyUserIds[$i]})...\n";
        
        // Create verified deposit
        $txId = $transactionModel->createDeposit([
            'savings_plan_id' => $planId,
            'amount' => $targetPerParticipant,
            'payment_method' => 'cash',
            'payment_proof' => 'test_proof',
            'notes' => 'Test direct full target payment'
        ]);
        
        // Verify payment
        $transactionModel->verify($txId, 'verified', 1, 'Verified by test runner');
        
        // Apply verified deposit
        $planModel->applyVerifiedDeposit($planId, $targetPerParticipant);
        
        // Fetch plan state after payment
        $updatedPlan = $planModel->getById($planId);
        echo "Plan ID {$planId} status: " . $updatedPlan['status'] . ", Group ID: " . ($updatedPlan['group_id'] ?: 'NULL') . "\n";
    }

    echo "=== POST-TEST CHECK AND ASSERTIONS ===\n";
    
    // Verify the latest created group
    $stmtGroup = $conn->query("
        SELECT g.*, 
               (SELECT COUNT(*) FROM savings_plans WHERE group_id = g.id) AS member_count
        FROM savings_groups g
        ORDER BY g.created_at DESC LIMIT 1
    ");
    $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        throw new Exception("FAIL: No savings group was created.");
    }
    
    echo "Group Code: " . $group['group_code'] . "\n";
    echo "Group Member Count: " . $group['member_count'] . "/7\n";
    echo "Group Status: " . $group['status'] . "\n";

    if ($group['member_count'] != 7) {
        throw new Exception("FAIL: Group should have exactly 7 members.");
    }
    if ($group['status'] !== 'Penuh') {
        throw new Exception("FAIL: Group status should be 'Penuh'.");
    }
    
    // Check notifications
    $stmtNotifCount = $conn->query("SELECT COUNT(*) FROM notifications");
    $notifCount = $stmtNotifCount->fetchColumn();
    echo "Total notifications generated: " . $notifCount . "\n";
    
    $stmtRecentNotifs = $conn->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 5");
    $recentNotifs = $stmtRecentNotifs->fetchAll(PDO::FETCH_ASSOC);
    echo "Recent notifications:\n";
    foreach ($recentNotifs as $n) {
        echo "- User {$n['user_id']}: {$n['message']}\n";
    }

    echo "SUCCESS: All assertions passed!\n";
    
} catch (Exception $e) {
    echo "TEST FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
