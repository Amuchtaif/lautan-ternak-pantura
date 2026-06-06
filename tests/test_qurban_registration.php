<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (!isset($conn)) {
    die("Error: Database connection not available.\n");
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction();

    echo "=== STARTING QURBAN REGISTRATION TESTS ===\n";

    // 1. Create a unique dummy user
    $email = "testpequrban_" . time() . "@example.com";
    $username = "testpequrban_" . time();
    $password = password_hash("password123", PASSWORD_BCRYPT);
    $namaPequrban = "Test Pequrban " . time();
    $binBinti = "bin Test";
    $noWa = "081234567890";
    $alamat = "Jl. Pantura Raya No. 123";

    $userModel = new User($conn);
    $userId = $userModel->createCustomer([
        'full_name' => $namaPequrban,
        'username' => $username,
        'email' => $email,
        'phone' => $noWa,
        'password' => $password,
        'gender' => null,
        'address' => $alamat,
        'city' => null,
        'province' => null
    ]);
    
    echo "1. User Account Created Successfully. User ID: {$userId}\n";

    // 2. Generate Registration Number
    $targetLunasTahun = 2027;
    $targetLunasBulan = "Mei";
    
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM qurban_registrations WHERE nomor_registrasi LIKE ?");
    $stmtCount->execute(["LTP-{$targetLunasTahun}-%"]);
    $count = $stmtCount->fetchColumn();
    $nextSeq = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    $nomorRegistrasi = "LTP-{$targetLunasTahun}-{$nextSeq}";
    
    echo "2. Generated Registration Number: {$nomorRegistrasi}\n";

    // 3. Insert into qurban_registrations
    $jenisQurban = "Domba";
    $paketQurban = "Domba Ekonomis (23-25 Kg)";
    $hargaTarget = 2000000;
    $polaTabungan = "Bulanan";
    $nominalTargetSetoran = 200000;
    $opsiPenyaluran = "Sembelih di LTP, daging diambil seluruhnya";
    $hadirPenyembelihan = 1;
    $namaSertifikat = $namaPequrban;
    $catatan = "Catatan pengujian.";

    $stmtReg = $conn->prepare("
        INSERT INTO qurban_registrations (
            customer_id, nomor_registrasi, nama_pequrban, bin_binti, no_wa, alamat,
            jenis_qurban, paket_qurban, harga_target, pola_tabungan, nominal_target_setoran,
            target_lunas_bulan, target_lunas_tahun, opsi_penyaluran, alamat_pengiriman,
            hadir_penyembelihan, nama_sertifikat, catatan, persetujuan, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, 1, 'Aktif')
    ");
    $stmtReg->execute([
        $userId, $nomorRegistrasi, $namaPequrban, $binBinti, $noWa, $alamat,
        $jenisQurban, $paketQurban, $hargaTarget, $polaTabungan, $nominalTargetSetoran,
        $targetLunasBulan, $targetLunasTahun, $opsiPenyaluran, $hadirPenyembelihan, 
        $namaSertifikat, $catatan
    ]);
    
    $regId = $conn->lastInsertId();
    echo "3. Registration Saved in 'qurban_registrations'. ID: {$regId}\n";

    // 4. Insert into savings_plans
    $durationMonth = 10;
    $targetDateStr = "2027-05-25";
    $monthlyTarget = round($hargaTarget / $durationMonth, 2);
    $notes = "Bin/Binti: {$binBinti}, Pola: {$polaTabungan}, Target Setoran: " . number_format($nominalTargetSetoran, 0, ',', '.');
    
    $stmtPlan = $conn->prepare("
        INSERT INTO savings_plans (
            plan_code, customer_id, livestock_id, target_type, livestock_target,
            target_amount, current_amount, monthly_target, duration_month,
            start_date, target_date, status, notes
        ) VALUES (?, ?, NULL, 'manual', ?, ?, 0, ?, ?, CURRENT_DATE(), ?, 'active', ?)
    ");
    $stmtPlan->execute([
        $nomorRegistrasi, $userId, $paketQurban, $hargaTarget, $monthlyTarget, $durationMonth, $targetDateStr, $notes
    ]);
    $planId = $conn->lastInsertId();
    echo "4. Savings Plan Created. ID: {$planId}\n";

    // 5. Insert into sohibul_qurban
    $stmtSohibul = $conn->prepare("
        INSERT INTO sohibul_qurban (plan_id, name, phone, address, relationship)
        VALUES (?, ?, ?, ?, 'self')
    ");
    $stmtSohibul->execute([$planId, $namaSertifikat, $noWa, $alamat]);
    $sohibulId = $conn->lastInsertId();
    echo "5. Sohibul Qurban Record Saved. ID: {$sohibulId}\n";

    // 6. Verification
    // Verify user info
    $verifyUser = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $verifyUser->execute([$userId]);
    $user = $verifyUser->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['email'] !== $email) {
        throw new Exception("Verification failed: User email mismatch.");
    }
    
    // Verify registration info
    $verifyReg = $conn->prepare("SELECT * FROM qurban_registrations WHERE id = ?");
    $verifyReg->execute([$regId]);
    $reg = $verifyReg->fetch(PDO::FETCH_ASSOC);
    if (!$reg || $reg['nomor_registrasi'] !== $nomorRegistrasi) {
        throw new Exception("Verification failed: Registration number mismatch.");
    }
    
    // Verify savings plan info
    $verifyPlan = $conn->prepare("SELECT * FROM savings_plans WHERE id = ?");
    $verifyPlan->execute([$planId]);
    $plan = $verifyPlan->fetch(PDO::FETCH_ASSOC);
    if (!$plan || (float)$plan['target_amount'] !== (float)$hargaTarget) {
        throw new Exception("Verification failed: Savings plan target amount mismatch.");
    }
    
    // Verify sohibul qurban info
    $verifySohibul = $conn->prepare("SELECT * FROM sohibul_qurban WHERE id = ?");
    $verifySohibul->execute([$sohibulId]);
    $sohibul = $verifySohibul->fetch(PDO::FETCH_ASSOC);
    if (!$sohibul || $sohibul['name'] !== $namaSertifikat) {
        throw new Exception("Verification failed: Sohibul qurban name mismatch.");
    }

    echo "=== ALL TESTS COMPLETED SUCCESSFULLY AND VERIFIED ===\n";
    
    // Rollback so we don't pollute the db
    $conn->rollBack();
    echo "Transaction rolled back cleanly.\n";

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "TEST FAILED: " . $e->getMessage() . "\n";
}
?>
