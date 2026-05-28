<?php
// Comprehensive Programmatic Verification Script
// Location: scratch/test_payment_calculations.php

define('ANSI_RED', "\033[31m");
define('ANSI_GREEN', "\033[32m");
define('ANSI_YELLOW', "\033[33m");
define('ANSI_RESET', "\033[0m");

echo "=========================================================\n";
echo "🧪 PROGRAMMATIC VERIFICATION: PAYMENT & LIVESTOCK LEDGER 🧪\n";
echo "=========================================================\n\n";

// Require database config & models
require_once __DIR__ . '/../config/database.php';

if (!isset($conn)) {
    echo ANSI_RED . "❌ Database connection ($conn) is unreachable. Make sure MySQL is running!" . ANSI_RESET . "\n";
    exit(1);
}

require_once __DIR__ . '/../models/Livestock.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/SalePayment.php';

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

// Clean up test data from database before starting
$conn->beginTransaction();
try {
    $conn->exec("DELETE FROM sale_payments WHERE payment_note LIKE '%[TEST_PAYMENT]%'");
    $conn->exec("DELETE FROM sales WHERE notes LIKE '%[TEST_SALE]%'");
    $conn->exec("DELETE FROM livestock WHERE description LIKE '%[TEST_LIVESTOCK]%'");
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    echo "Warning during clean up: " . $e->getMessage() . "\n";
}

// 1. Model Calculations & Stock Auto-adjustment Test
it("should reduce stock to 0 and shift status to 'sold' when livestock is purchased completely, and auto-restore to available on deletion", function() use ($conn) {
    $livestockModel = new Livestock($conn);
    $saleModel = new Sale($conn);
    
    // Create test livestock with stock = 1
    $livestockData = [
        'livestock_code' => 'TEST-LIV-001-' . time(),
        'peternak_name' => 'Pak Slamet Test',
        'category' => 'qurban',
        'breed' => 'Sapi Madura Super',
        'age' => 24,
        'weight' => 380.00,
        'gender' => 'male',
        'purchase_price' => 15000000,
        'selling_price' => 18000000,
        'stock' => 1,
        'status' => 'available',
        'image' => null,
        'description' => '[TEST_LIVESTOCK] Test cow'
    ];
    
    $livestockModel->create($livestockData);
    $livestock = $livestockModel->getByCode($livestockData['livestock_code']);
    
    if (!$livestock) {
        throw new Exception("Failed to create test livestock.");
    }
    
    if ($livestock['status'] !== 'available') {
        throw new Exception("Initial status should be available, got: " . $livestock['status']);
    }
    
    // Purchase this cow completely (qty = 1)
    $saleData = [
        'invoice_code' => 'TEST-INV-001-' . time(),
        'customer_name' => 'Pelanggan Test',
        'customer_phone' => '081234567890',
        'livestock_id' => $livestock['id'],
        'qty' => 1,
        'payment_type' => 'lunas',
        'payment_status' => 'unpaid',
        'sale_status' => 'pending',
        'notes' => '[TEST_SALE] Test sale complete stock reduction',
        'created_by' => 1, // Assume Admin/User ID 1 exists
        'payment_amount' => 0 // Unpaid initially
    ];
    
    $saleId = $saleModel->create($saleData);
    
    // Fetch livestock again and verify stock is 0 and status is 'sold'
    $updatedLivestock = $livestockModel->getById($livestock['id']);
    if ($updatedLivestock['stock'] != 0) {
        throw new Exception("Stock was not reduced to 0. Stock: " . $updatedLivestock['stock']);
    }
    if ($updatedLivestock['status'] !== 'sold') {
        throw new Exception("Status did not auto-update to 'sold'. Status: " . $updatedLivestock['status']);
    }
    
    // Delete the sale, verifying stock is restored back to 1 and status back to 'available'
    $saleModel->delete($saleId);
    $restoredLivestock = $livestockModel->getById($livestock['id']);
    if ($restoredLivestock['stock'] != 1) {
        throw new Exception("Stock was not restored. Stock: " . $restoredLivestock['stock']);
    }
    if ($restoredLivestock['status'] !== 'available') {
        throw new Exception("Status did not revert to 'available'. Status: " . $restoredLivestock['status']);
    }
    
    // Clean up
    $livestockModel->delete($livestock['id']);
});

// 2. Verified Payments Sum & Remaining Balance Test
it("should compute remaining balance and payment status correctly based on VERIFIED payments only", function() use ($conn) {
    $livestockModel = new Livestock($conn);
    $saleModel = new Sale($conn);
    $paymentModel = new SalePayment($conn);
    
    // Create test livestock
    $livestockData = [
        'livestock_code' => 'TEST-LIV-002-' . time(),
        'peternak_name' => 'Pak Slamet Test',
        'category' => 'qurban',
        'breed' => 'Kambing Etawa Test',
        'age' => 12,
        'weight' => 45.00,
        'gender' => 'male',
        'purchase_price' => 3000000,
        'selling_price' => 4000000,
        'stock' => 5,
        'status' => 'available',
        'image' => null,
        'description' => '[TEST_LIVESTOCK] Test goat'
    ];
    $livestockModel->create($livestockData);
    $livestock = $livestockModel->getByCode($livestockData['livestock_code']);
    
    // Create Sale under DP scheme
    $saleData = [
        'invoice_code' => 'TEST-INV-002-' . time(),
        'customer_name' => 'Pelanggan Test',
        'customer_phone' => '081234567890',
        'livestock_id' => $livestock['id'],
        'qty' => 1,
        'payment_type' => 'dp',
        'payment_status' => 'unpaid',
        'sale_status' => 'pending',
        'notes' => '[TEST_SALE] Test sale remaining calculations',
        'created_by' => 1,
        'payment_amount' => 1500000, // DP amount
        'payment_method' => 'Transfer Bank Manual',
        'payment_note' => '[TEST_PAYMENT] Initial DP payment',
        'initial_payment_status' => 'pending' // Initial status is pending
    ];
    
    $saleId = $saleModel->create($saleData);
    
    // Verify remaining balance when initial payment is PENDING
    // Since payment is pending, verified paid should be 0, remaining should be total price (4,000,000)
    $remaining = $saleModel->getRemainingBalance($saleId);
    if ($remaining != 4000000) {
        throw new Exception("Expected remaining balance to be 4000000 because DP is pending. Got: " . $remaining);
    }
    
    $sale = $saleModel->getById($saleId);
    if ($sale['payment_status'] !== 'unpaid') {
        throw new Exception("Expected payment status to be 'unpaid' because DP is pending. Got: " . $sale['payment_status']);
    }
    
    // Verify the payment record itself is pending
    $payments = $saleModel->getPayments($saleId);
    if (count($payments) !== 1) {
        throw new Exception("Expected 1 payment record, got: " . count($payments));
    }
    $dpPayment = $payments[0];
    if ($dpPayment['payment_status'] !== 'pending') {
        throw new Exception("Expected DP payment status to be 'pending', got: " . $dpPayment['payment_status']);
    }
    
    // Admin verifies the DP
    $paymentModel->updateStatus($dpPayment['id'], 'verified');
    
    // Verify balance is now updated to 2,500,000 and status is partial
    $remainingAfterVerify = $saleModel->getRemainingBalance($saleId);
    if ($remainingAfterVerify != 2500000) {
        throw new Exception("Expected remaining balance to be 2500000 after DP verification. Got: " . $remainingAfterVerify);
    }
    
    $saleAfterVerify = $saleModel->getById($saleId);
    if ($saleAfterVerify['payment_status'] !== 'partial') {
        throw new Exception("Expected payment status to be 'partial' after DP verification. Got: " . $saleAfterVerify['payment_status']);
    }
    
    // Add subsequent installment as PENDING
    $instData = [
        'sale_id' => $saleId,
        'payment_code' => 'TEST-PAY-003-' . time(),
        'payment_method' => 'Transfer Bank Manual',
        'payment_amount' => 1000000,
        'payment_note' => '[TEST_PAYMENT] First installment',
        'payment_status' => 'pending',
        'created_by' => 1
    ];
    $instId = $paymentModel->create($instData);
    
    // Remaining balance should still be 2,500,000 since installment is pending
    $remainingAfterInstPending = $saleModel->getRemainingBalance($saleId);
    if ($remainingAfterInstPending != 2500000) {
        throw new Exception("Remaining balance changed with pending installment: " . $remainingAfterInstPending);
    }
    
    // Verify the installment
    $paymentModel->updateStatus($instId, 'verified');
    
    // Remaining balance should now be 1,500,000
    $remainingAfterInstVerify = $saleModel->getRemainingBalance($saleId);
    if ($remainingAfterInstVerify != 1500000) {
        throw new Exception("Expected remaining balance to be 1500000, got: " . $remainingAfterInstVerify);
    }
    
    // Clean up
    $saleModel->delete($saleId);
    $livestockModel->delete($livestock['id']);
});

// 3. Transaction Gating (Ceiling Verification) Test
it("should prevent recording subsequent payments that exceed the remaining balance limit", function() use ($conn) {
    $livestockModel = new Livestock($conn);
    $saleModel = new Sale($conn);
    $paymentModel = new SalePayment($conn);
    
    // Create test livestock
    $livestockData = [
        'livestock_code' => 'TEST-LIV-003-' . time(),
        'peternak_name' => 'Pak Slamet Test',
        'category' => 'qurban',
        'breed' => 'Kambing Etawa Test 2',
        'age' => 12,
        'weight' => 45.00,
        'gender' => 'male',
        'purchase_price' => 3000000,
        'selling_price' => 4000000,
        'stock' => 5,
        'status' => 'available',
        'image' => null,
        'description' => '[TEST_LIVESTOCK] Test goat ceiling'
    ];
    $livestockModel->create($livestockData);
    $livestock = $livestockModel->getByCode($livestockData['livestock_code']);
    
    // Create Sale under DP scheme
    $saleData = [
        'invoice_code' => 'TEST-INV-003-' . time(),
        'customer_name' => 'Pelanggan Test',
        'customer_phone' => '081234567890',
        'livestock_id' => $livestock['id'],
        'qty' => 1,
        'payment_type' => 'dp',
        'payment_status' => 'unpaid',
        'sale_status' => 'pending',
        'notes' => '[TEST_SALE] Test sale ceiling gating',
        'created_by' => 1,
        'payment_amount' => 2000000, // DP amount
        'payment_method' => 'Transfer Bank Manual',
        'payment_note' => '[TEST_PAYMENT] Initial DP payment',
        'initial_payment_status' => 'verified' // Verify directly
    ];
    $saleId = $saleModel->create($saleData);
    
    // Remaining balance is 2,000,000
    $remaining = $saleModel->getRemainingBalance($saleId);
    if ($remaining != 2000000) {
        throw new Exception("Expected remaining balance to be 2000000. Got: " . $remaining);
    }
    
    // Attempt to add a verified payment of 2,500,000 (which exceeds the remaining 2,000,000)
    $excessPaymentData = [
        'sale_id' => $saleId,
        'payment_code' => 'TEST-PAY-004-' . time(),
        'payment_method' => 'Transfer Bank Manual',
        'payment_amount' => 2500000, // Exceeds remaining
        'payment_note' => '[TEST_PAYMENT] Excess payment',
        'payment_status' => 'verified', // Directly verified
        'created_by' => 1
    ];
    
    $caughtException = false;
    try {
        $paymentModel->create($excessPaymentData);
    } catch (Exception $e) {
        $caughtException = true;
        // Verify message indicates payment exceeds remaining debt
        if (strpos($e->getMessage(), 'melebihi sisa kekurangan') === false) {
            throw new Exception("Exception thrown, but message did not mention remaining balance error. Got: " . $e->getMessage());
        }
    }
    
    if (!$caughtException) {
        throw new Exception("Allowed verified payment exceeding remaining balance!");
    }
    
    // Test verification gating: Add a pending payment first of 2,500,000, then verify it.
    // Gating should throw an exception during verification.
    $pendingExcessData = [
        'sale_id' => $saleId,
        'payment_code' => 'TEST-PAY-005-' . time(),
        'payment_method' => 'Transfer Bank Manual',
        'payment_amount' => 2500000, // Exceeds remaining
        'payment_note' => '[TEST_PAYMENT] Pending excess',
        'payment_status' => 'pending', // Pending is allowed
        'created_by' => 1
    ];
    
    $pendingId = $paymentModel->create($pendingExcessData);
    
    $verifyCaught = false;
    try {
        $paymentModel->updateStatus($pendingId, 'verified');
    } catch (Exception $e) {
        $verifyCaught = true;
        if (strpos($e->getMessage(), 'melebihi sisa kekurangan') === false) {
            throw new Exception("Verification exception did not mention remaining balance error. Got: " . $e->getMessage());
        }
    }
    
    if (!$verifyCaught) {
        throw new Exception("Allowed verification of payment exceeding remaining balance!");
    }
    
    // Clean up
    $saleModel->delete($saleId);
    $livestockModel->delete($livestock['id']);
});

// Final report
echo "\n=========================================================\n";
echo "📊 VERIFICATION SUITE REPORT 📊\n";
echo "=========================================================\n";
echo ANSI_GREEN . "  ✔ Passed: " . $testsPassed . ANSI_RESET . "\n";
if ($testsFailed > 0) {
    echo ANSI_RED . "  ✖ Failed: " . $testsFailed . ANSI_RESET . "\n";
    echo ANSI_RED . "🔴 STATUS: RED (Verification failed - check calculation or validation details)\n" . ANSI_RESET;
    exit(1);
} else {
    echo ANSI_GREEN . "🟢 STATUS: GREEN (All payment rules, calculations, and boundaries verified!)\n" . ANSI_RESET;
    exit(0);
}
