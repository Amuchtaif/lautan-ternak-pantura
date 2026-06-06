<?php
require_once '../../config/database.php';
require_once '../../helpers/AuthHelper.php';
require_once '../../models/User.php';
require_once '../../models/SavingsPlan.php';

AuthHelper::start();

function redirectTabungan($error) {
    header('Location: /lautan-ternak-pantura/tabungan?error=' . urlencode($error) . '#form-registrasi');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /lautan-ternak-pantura/tabungan');
    exit;
}

if (!AuthHelper::validateCsrf($_POST['csrf_token'] ?? '')) {
    redirectTabungan('csrf');
}

if (!isset($conn)) {
    redirectTabungan('database');
}

// Extract and sanitize inputs
$namaPequrban = trim($_POST['nama_pequrban'] ?? '');
$binBinti = trim($_POST['bin_binti'] ?? '');
$noWa = preg_replace('/[^0-9]/', '', $_POST['no_wa'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirmPassword = (string)($_POST['password_confirm'] ?? '');

$jenisQurban = trim($_POST['jenis_qurban'] ?? '');
$paketQurban = trim($_POST['paket_qurban'] ?? '');
$hargaTarget = floatval($_POST['harga_target'] ?? 0);
$polaTabungan = trim($_POST['pola_tabungan'] ?? '');
$nominalTargetSetoran = floatval($_POST['nominal_target_setoran'] ?? 0);
$targetLunasBulan = trim($_POST['target_lunas_bulan'] ?? '');
$targetLunasTahun = intval($_POST['target_lunas_tahun'] ?? 2027);

$opsiPenyaluran = trim($_POST['opsi_penyaluran'] ?? '');
$alamatPengiriman = trim($_POST['alamat_pengiriman'] ?? '');
$hadirPenyembelihan = isset($_POST['hadir_penyembelihan']) ? 1 : 0;

$namaSertifikat = trim($_POST['nama_sertifikat'] ?? '');
if (empty($namaSertifikat)) {
    $namaSertifikat = $namaPequrban;
}

$catatan = trim($_POST['catatan'] ?? '');
$persetujuan = isset($_POST['persetujuan']) ? 1 : 0;

// Basic validation
if ($namaPequrban === '' || $binBinti === '' || $alamat === '' || $paketQurban === '' || $hargaTarget <= 0 || $polaTabungan === '' || $targetLunasBulan === '') {
    redirectTabungan('invalid_data');
}

// Validate WhatsApp (Only numbers, start with 08 or 62)
if (!preg_match('/^(08|62)[0-9]{8,14}$/', $noWa)) {
    redirectTabungan('wa_invalid');
}

if ($opsiPenyaluran === 'Hewan hidup dikirim ke alamat pequrban' && $alamatPengiriman === '') {
    redirectTabungan('alamat_pengiriman_wajib');
}

if (!$persetujuan) {
    redirectTabungan('persetujuan_wajib');
}

// Check target date & duration
$monthMap = [
    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
    'Dzulhijjah' => '05' // Idul Adha 1448 H is in May 2027
];
$monthNum = $monthMap[$targetLunasBulan] ?? '05';
$targetDateStr = "{$targetLunasTahun}-{$monthNum}-25";

$today = new DateTimeImmutable('today');
try {
    $targetDateObj = new DateTimeImmutable($targetDateStr);
    if ($targetDateObj <= $today) {
        $durationMonth = 1;
    } else {
        $diff = $today->diff($targetDateObj);
        $durationMonth = max(1, ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0));
    }
} catch (Throwable $e) {
    redirectTabungan('invalid_date');
}

try {
    $conn->beginTransaction();

    $userModel = new User($conn);
    $userId = null;

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } else {
        // Customer account creation required
        if ($email === '') {
            redirectTabungan('email_invalid');
        }
        if ($userModel->emailExists($email)) {
            redirectTabungan('email_taken');
        }

        // Generate username
        $username = strstr($email, '@', true);
        if (!$username) {
            $username = 'user_' . time() . '_' . rand(10, 99);
        } else {
            $username = preg_replace('/[^a-z0-9._-]/', '', $username);
            if (strlen($username) < 4) {
                $username = str_pad($username, 4, '0', STR_PAD_RIGHT);
            }
            if (strlen($username) > 40) {
                $username = substr($username, 0, 40);
            }
        }
        if ($userModel->usernameExists($username)) {
            $baseUsername = $username;
            $suffix = 1;
            while ($userModel->usernameExists($username)) {
                $username = substr($baseUsername, 0, 45) . $suffix;
                $suffix++;
            }
        }

        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            redirectTabungan('password_weak');
        }
        if ($password !== $confirmPassword) {
            redirectTabungan('password_mismatch');
        }

        $userId = $userModel->createCustomer([
            'full_name' => $namaPequrban,
            'username' => $username,
            'email' => $email,
            'phone' => $noWa,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'gender' => null,
            'address' => $alamat,
            'city' => null,
            'province' => null
        ]);
    }

    // Auto-generate Registration Number (LTP-YYYY-XXXXX)
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM qurban_registrations WHERE nomor_registrasi LIKE ?");
    $stmtCount->execute(["LTP-{$targetLunasTahun}-%"]);
    $count = $stmtCount->fetchColumn();
    $nextSeq = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    $nomorRegistrasi = "LTP-{$targetLunasTahun}-{$nextSeq}";

    // Insert into qurban_registrations
    $stmtReg = $conn->prepare("
        INSERT INTO qurban_registrations (
            customer_id, nomor_registrasi, nama_pequrban, bin_binti, no_wa, alamat,
            jenis_qurban, paket_qurban, harga_target, pola_tabungan, nominal_target_setoran,
            target_lunas_bulan, target_lunas_tahun, opsi_penyaluran, alamat_pengiriman,
            hadir_penyembelihan, nama_sertifikat, catatan, persetujuan, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')
    ");
    $stmtReg->execute([
        $userId, $nomorRegistrasi, $namaPequrban, $binBinti, $noWa, $alamat,
        $jenisQurban, $paketQurban, $hargaTarget, $polaTabungan, $nominalTargetSetoran,
        $targetLunasBulan, $targetLunasTahun, $opsiPenyaluran, $alamatPengiriman,
        $hadirPenyembelihan, $namaSertifikat, $catatan, $persetujuan
    ]);

    // Calculate monthly equivalent target for savings plan
    $monthlyTarget = 0;
    if ($polaTabungan === 'Harian') {
        $monthlyTarget = $nominalTargetSetoran * 30;
    } elseif ($polaTabungan === 'Mingguan') {
        $monthlyTarget = $nominalTargetSetoran * 4.3;
    } elseif ($polaTabungan === 'Bulanan') {
        $monthlyTarget = $nominalTargetSetoran;
    } else {
        $monthlyTarget = $hargaTarget / $durationMonth;
    }
    $monthlyTarget = round($monthlyTarget, 2);

    // Insert into savings_plans
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

    // Insert into sohibul_qurban
    $stmtSohibul = $conn->prepare("
        INSERT INTO sohibul_qurban (plan_id, name, phone, address, relationship)
        VALUES (?, ?, ?, ?, 'self')
    ");
    $stmtSohibul->execute([$planId, $namaSertifikat, $noWa, $alamat]);

    // Add user notification if table exists
    try {
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
        $stmtNotif->execute([$userId, "Pendaftaran berhasil! Nomor Registrasi Anda: {$nomorRegistrasi}."]);
    } catch (Throwable $e) {
        // Table notification might be absent in some DB states, safe ignore
    }

    $conn->commit();

    // Log the user in if they were not logged in
    if (!isset($_SESSION['user_id'])) {
        $user = $userModel->findArrayById($userId);
        AuthHelper::login($user);
        $userModel->updateLastLogin($userId);
    }

    header('Location: /lautan-ternak-pantura/customer/dashboard?success=plan_created');
    exit;

} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    redirectTabungan($e->getMessage());
}
exit;

