<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/auth/login");
    exit();
}

// Fetch Dynamic Stats
$totalUsers = 0;
$activeLivestock = 0;
$pendingVerifications = 0;
$totalSavings = 0;
$recentTransactions = [];

function tableHasColumn($conn, $table, $column) {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

try {
    $usesNewSavingsTransactions = tableHasColumn($conn, 'savings_transactions', 'savings_plan_id');
    $txPlanColumn = $usesNewSavingsTransactions ? 'savings_plan_id' : 'plan_id';
    $txStatusColumn = $usesNewSavingsTransactions ? 'transaction_status' : 'status';

    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
    $totalUsers = $stmt->fetchColumn();

    // Total Active Livestock
    $stmt = $conn->query("SELECT COUNT(*) FROM livestock WHERE status = 'available'");
    $activeLivestock = $stmt->fetchColumn();

    // Pending Verifications
    $stmt = $conn->query("SELECT COUNT(*) FROM savings_transactions WHERE {$txStatusColumn} = 'pending'");
    $pendingVerifications = $stmt->fetchColumn();

    // Total Savings
    $stmt = $conn->query("SELECT SUM(amount) FROM savings_transactions WHERE {$txStatusColumn} = 'verified'");
    $totalSavings = $stmt->fetchColumn() ?: 0;

    // Recent Transactions for the table
    $stmt = $conn->query("
        SELECT st.*, st.{$txStatusColumn} AS status, u.name as user_name, sp.id as plan_id 
        FROM savings_transactions st
        JOIN savings_plans sp ON st.{$txPlanColumn} = sp.id
        JOIN users u ON sp.customer_id = u.id
        WHERE st.{$txStatusColumn} = 'pending'
        ORDER BY st.created_at DESC
        LIMIT 5
    ");
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch livestock sales per category this month
    $salesSummary = [];
    $stmtCategory = $conn->query("
        SELECT 
            CASE 
                WHEN LOWER(livestock_name) LIKE '%sapi%' THEN 'Sapi'
                WHEN LOWER(livestock_name) LIKE '%kambing%' THEN 'Kambing'
                WHEN LOWER(livestock_name) LIKE '%domba%' THEN 'Domba'
                ELSE 'Lainnya'
            END AS kategori,
            SUM(qty) AS total_terjual
        FROM sales
        WHERE sale_status != 'cancelled'
          AND MONTH(created_at) = MONTH(CURRENT_DATE())
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
        GROUP BY kategori
    ");
    if ($stmtCategory) {
        $salesSummary = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $categorySales = ['Sapi' => 0, 'Kambing' => 0, 'Domba' => 0, 'Lainnya' => 0];
    foreach ($salesSummary as $row) {
        $cat = $row['kategori'];
        if (isset($categorySales[$cat])) {
            $categorySales[$cat] = (int)$row['total_terjual'];
        } else {
            $categorySales['Lainnya'] += (int)$row['total_terjual'];
        }
    }

} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
    $categorySales = ['Sapi' => 0, 'Kambing' => 0, 'Domba' => 0, 'Lainnya' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lautan Ternak Pantura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#0d5bb5',
                            secondary: '#00a3e0',
                            light: '#e0f2fe',
                            dark: '#0a4286',
                            accent: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Toast Container */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">

        <!-- Top Navigation -->
        <?php include 'includes/topbar.php'; ?>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Dashboard <span class="text-brand-primary">Overview</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
                </div>
            </div>



            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-brand-primary/5 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-users"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-green-500 bg-green-50 px-2 py-1 rounded-lg">Aktif</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Pengguna</p>
                    <p class="text-3xl font-black text-gray-900 mt-1"><?php echo number_format($totalUsers); ?></p>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-blue-500/5 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-blue-500 bg-blue-50 px-2 py-1 rounded-lg">Tersedia</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Hewan Ternak</p>
                    <p class="text-3xl font-black text-gray-900 mt-1"><?php echo number_format($activeLivestock); ?>
                        <small class="text-sm font-bold">Ekor</small></p>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-amber-500/5 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="text-[10px] font-black text-red-500 bg-red-50 px-2 py-1 rounded-lg">Pending</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Perlu Verifikasi</p>
                    <p class="text-3xl font-black text-gray-900 mt-1">
                        <?php echo number_format($pendingVerifications); ?> <small
                            class="text-sm font-bold">Data</small></p>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-purple-500/5 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-purple-500 bg-purple-50 px-2 py-1 rounded-lg">Total</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tabungan Masuk</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">Rp <?php echo number_format($totalSavings, 0, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Ringkasan Penjualan Bulan Ini -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-50 space-y-6">
                <div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Penjualan Hewan Bulan Ini</h3>
                    <p class="text-xs text-gray-400 font-bold mt-1">Kuantitas hewan terjual berdasarkan kategori pada bulan berjalan.</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Sapi -->
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 rounded-2xl border border-emerald-100 flex items-center justify-between group hover:shadow-lg hover:shadow-emerald-500/5 transition-all">
                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Kategori Sapi</p>
                            <p class="text-3xl font-black text-emerald-900 mt-2"><?php echo $categorySales['Sapi']; ?> <span class="text-sm font-bold text-emerald-600/70">Ekor</span></p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-all">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                    </div>

                    <!-- Kambing -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 rounded-2xl border border-blue-100 flex items-center justify-between group hover:shadow-lg hover:shadow-blue-500/5 transition-all">
                        <div>
                            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Kategori Kambing</p>
                            <p class="text-3xl font-black text-blue-900 mt-2"><?php echo $categorySales['Kambing']; ?> <span class="text-sm font-bold text-blue-600/70">Ekor</span></p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center text-xl shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-all">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                    </div>

                    <!-- Domba -->
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 rounded-2xl border border-amber-100 flex items-center justify-between group hover:shadow-lg hover:shadow-amber-500/5 transition-all">
                        <div>
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Kategori Domba</p>
                            <p class="text-3xl font-black text-amber-900 mt-2"><?php echo $categorySales['Domba']; ?> <span class="text-sm font-bold text-amber-600/70">Ekor</span></p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-all">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                    </div>

                    <!-- Lainnya -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 p-6 rounded-2xl border border-purple-100 flex items-center justify-between group hover:shadow-lg hover:shadow-purple-500/5 transition-all">
                        <div>
                            <p class="text-[10px] font-black text-purple-600 uppercase tracking-widest">Kategori Lainnya</p>
                            <p class="text-3xl font-black text-purple-900 mt-2"><?php echo $categorySales['Lainnya']; ?> <span class="text-sm font-bold text-purple-600/70">Ekor</span></p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-500 text-white flex items-center justify-center text-xl shadow-lg shadow-purple-500/20 group-hover:scale-110 transition-all">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                <div
                    class="px-8 py-8 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Antrean Verifikasi Pembayaran</h3>
                        <p class="text-xs text-gray-400 font-bold mt-1">Segera periksa bukti transfer untuk memperbarui
                            status pesanan.</p>
                    </div>
                    <button onclick="showSuccessNotification('Sistem sinkron dengan database!')"
                        class="bg-brand-primary/5 text-brand-primary px-5 py-2.5 rounded-2xl text-xs font-black hover:bg-brand-primary hover:text-white transition-all">
                        Refresh Data
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    Pengirim / ID</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    Kategori</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    Nominal</th>
                                <th
                                    class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    Tanggal</th>
                                <th
                                    class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($recentTransactions)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Tidak ada antrean verifikasi saat ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTransactions as $trx): ?>
                                    <tr class="hover:bg-brand-light/20 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-400 text-sm">
                                                    <?php echo substr($trx['user_name'], 0, 1); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black text-gray-900 leading-none">
                                                        <?php echo $trx['user_name']; ?></p>
                                                    <p class="text-[10px] font-bold text-gray-400 mt-1">
                                                        TRX-<?php echo $trx['id']; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span
                                                class="px-3 py-1 text-[10px] font-black rounded-full bg-blue-50 text-blue-600 uppercase tracking-wider">Cicilan
                                                Tabungan</span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <p class="text-sm font-black text-gray-900">Rp
                                                <?php echo number_format($trx['amount']); ?></p>
                                        </td>
                                        <td class="px-8 py-6">
                                            <p class="text-xs font-bold text-gray-400">
                                                <?php echo date('d M Y', strtotime($trx['created_at'])); ?></p>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button onclick="openApprovalModal(<?php echo (int)$trx['id']; ?>, <?php echo htmlspecialchars(json_encode($trx), ENT_QUOTES, 'UTF-8'); ?>)"
                                                    class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all shadow-sm animate-all"
                                                    title="Proses Transaksi">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                                <?php if (!empty($trx['payment_proof'])): ?>
                                                    <button onclick="openProofLightbox('<?php echo htmlspecialchars($trx['payment_proof'], ENT_QUOTES); ?>')"
                                                        class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm"
                                                        title="Lihat Bukti">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button disabled
                                                        class="w-10 h-10 rounded-xl bg-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed shadow-none"
                                                        title="Tidak ada bukti">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Approval Modal -->
    <div id="approval-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[1200] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl transition-all duration-300 scale-90 opacity-0 overflow-hidden flex flex-col max-h-[95vh]" id="approval-modal-content">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Proses Transaksi Tabungan</h3>
                <button type="button" onclick="closeApprovalModal()" class="w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-6 overflow-y-auto">
                <!-- Transaction Details -->
                <div class="grid grid-cols-2 gap-4 p-5 bg-gray-50 border border-gray-100 rounded-2xl">
                    <div class="col-span-2">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Nasabah</p>
                        <p class="text-base font-black text-gray-900" id="modal-customer">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Nominal</p>
                        <p class="text-xl font-black text-brand-primary" id="modal-amount">Rp -</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</p>
                        <p class="text-sm font-bold text-gray-700" id="modal-date">-</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <button type="button" id="modal-reject-btn" onclick="submitApproval('rejected')" class="w-full bg-red-50 text-red-600 border border-red-100 py-3.5 rounded-xl font-bold text-sm hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i> Tolak Transaksi
                    </button>
                    <button type="button" id="modal-approve-btn" onclick="submitApproval('verified')" class="w-full bg-green-500 text-white border border-green-500 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-green-500/20 hover:bg-green-600 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-check"></i> Setujui Valid
                    </button>
                    <button type="button" onclick="closeApprovalModal()" class="col-span-2 w-full bg-gray-50 text-gray-600 border border-gray-200 py-3.5 rounded-xl font-bold text-sm hover:bg-gray-100 transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Proof Lightbox -->
    <div id="proof-lightbox" class="fixed inset-0 z-[1200] hidden bg-gray-900/80 backdrop-blur-sm p-4 opacity-0 transition-all duration-300 items-center justify-center">
        <div class="flex h-full w-full flex-col transform scale-95 transition-all duration-300 max-w-4xl max-h-[90vh]">
            <div class="mb-3 flex items-center justify-between text-white">
                <p class="text-sm font-black uppercase tracking-widest">Preview Bukti Transfer</p>
                <div class="flex gap-2">
                    <button type="button" onclick="closeProofLightbox()" class="h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center">
                <img id="proof-lightbox-img" src="" alt="Preview bukti transfer" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl">
            </div>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            
            let bgColor = 'bg-[#0f965d]'; 
            let icon = 'fa-check';
            let title = 'BERHASIL!';
            
            if (type === 'error') {
                bgColor = 'bg-[#dc2626]'; 
                icon = 'fa-xmark';
                title = 'GAGAL!';
            } else if (type === 'warning') {
                bgColor = 'bg-[#f59e0b]'; 
                icon = 'fa-triangle-exclamation';
                title = 'PERINGATAN!';
            } else if (type === 'info') {
                bgColor = 'bg-[#3b82f6]'; 
                icon = 'fa-info';
                title = 'INFO!';
            }
            
            toast.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-5 min-w-[320px] max-w-[420px] transition-all duration-500 transform translate-x-10 opacity-0 pointer-events-auto`;
            
            toast.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas ${icon} text-sm text-white"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1 text-white/90">${title}</p>
                    <p class="text-sm font-bold leading-tight text-white">${message}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white transition-colors shrink-0">
                    <i class="fas fa-times text-lg"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('translate-x-10', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 4000);
        }

        function showSuccessNotification(message) { showToast(message, 'success'); }
        function showErrorNotification(message) { showToast(message, 'error'); }

        let currentTransactionId = null;

        function openApprovalModal(txId, txData) {
            try {
                const trx = typeof txData === 'string' ? JSON.parse(txData) : txData;
                currentTransactionId = txId;
                
                // Populate modal with transaction details
                document.getElementById('modal-customer').textContent = trx.user_name;
                document.getElementById('modal-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.amount);
                document.getElementById('modal-date').textContent = new Date(trx.created_at).toLocaleDateString('id-ID', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const overlay = document.getElementById('approval-modal');
                const content = document.getElementById('approval-modal-content');
                
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                setTimeout(() => {
                    overlay.classList.add('opacity-100');
                    content.classList.remove('scale-90', 'opacity-0');
                }, 10);
            } catch (e) {
                console.error('Error opening approval modal:', e);
            }
        }

        function closeApprovalModal() {
            const overlay = document.getElementById('approval-modal');
            const content = document.getElementById('approval-modal-content');
            
            overlay.classList.remove('opacity-100');
            content.classList.add('scale-90', 'opacity-0');
            setTimeout(() => {
                overlay.classList.remove('flex');
                overlay.classList.add('hidden');
                currentTransactionId = null;
            }, 300);
        }

        async function submitApproval(status) {
            if (!currentTransactionId) return;
            
            const btnApprove = document.getElementById('modal-approve-btn');
            const btnReject = document.getElementById('modal-reject-btn');
            const originalApproveText = btnApprove.innerHTML;
            const originalRejectText = btnReject.innerHTML;
            
            btnApprove.disabled = true;
            btnReject.disabled = true;
            if (status === 'verified') {
                btnApprove.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
            } else {
                btnReject.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menolak...';
            }
            
            try {
                const res = await fetch('/lautan-ternak-pantura/api/admin/verify_transfer', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: currentTransactionId, status: status})
                });
                const data = await res.json();
                if (data.success) {
                    closeApprovalModal();
                    showSuccessNotification(data.message);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showErrorNotification(data.message);
                    btnApprove.disabled = false;
                    btnReject.disabled = false;
                    btnApprove.innerHTML = originalApproveText;
                    btnReject.innerHTML = originalRejectText;
                }
            } catch (err) {
                showErrorNotification('Gagal memverifikasi transaksi.');
                btnApprove.disabled = false;
                btnReject.disabled = false;
                btnApprove.innerHTML = originalApproveText;
                btnReject.innerHTML = originalRejectText;
            }
        }

        // Close approval modal on backdrop click
        document.getElementById('approval-modal').addEventListener('click', function(e) {
            if (e.target === this) closeApprovalModal();
        });

        // lightbox functions
        function openProofLightbox(src) {
            const modal = document.getElementById('proof-lightbox');
            document.getElementById('proof-lightbox-img').src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.firstElementChild.classList.remove('scale-95');
            }, 10);
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeApprovalModal();
                closeProofLightbox();
            }
        });

        function closeProofLightbox() {
            const modal = document.getElementById('proof-lightbox');
            modal.classList.add('opacity-0');
            modal.firstElementChild.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('proof-lightbox-img').src = '';
            }, 300);
        }

        // Close lightbox on backdrop click
        document.getElementById('proof-lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeProofLightbox();
        });
    </script>
</body>

</html>
