<?php
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Dynamic Stats
try {
    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();

    // Total Active Livestock
    $stmt = $conn->query("SELECT COUNT(*) FROM livestock WHERE status = 'available'");
    $activeLivestock = $stmt->fetchColumn();

    // Pending Verifications
    $stmt = $conn->query("SELECT COUNT(*) FROM savings_transactions WHERE status = 'pending'");
    $pendingVerifications = $stmt->fetchColumn();

    // Total Savings
    $stmt = $conn->query("SELECT SUM(amount) FROM savings_transactions WHERE status = 'verified'");
    $totalSavings = $stmt->fetchColumn() ?: 0;

    // Recent Transactions for the table
    $stmt = $conn->query("
        SELECT st.*, u.name as user_name, sp.id as plan_id 
        FROM savings_transactions st
        JOIN savings_plans sp ON st.plan_id = sp.id
        JOIN users u ON sp.customer_id = u.id
        WHERE st.status = 'pending'
        ORDER BY st.created_at DESC
        LIMIT 5
    ");
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
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
    <link rel="icon" type="image/ico" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .sidebar-link.active {
            background-color: rgba(13, 91, 181, 0.1);
            color: #0d5bb5;
            border-right: 4px solid #0d5bb5;
        }
    </style>
</head>

<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">

        <!-- Top Navigation -->
        <header
            class="bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-gray-100 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="lg:hidden text-gray-500 text-xl"><i class="fas fa-bars"></i></button>
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Dashboard Overview</h2>
                    <p class="text-xs text-gray-400 font-bold">Selamat datang kembali, <?php echo $_SESSION['name']; ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <button class="relative p-2 text-gray-400 hover:text-brand-primary transition-colors">
                    <i class="far fa-bell text-xl"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                </button>
                <div class="h-10 w-[1px] bg-gray-100"></div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-black text-gray-900 leading-none"><?php echo $_SESSION['name']; ?></p>
                        <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Super Admin
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-primary border-2 border-brand-primary/20">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">

            <!-- Notification Container (Standard Patokan) -->
            <div id="notification-container" class="fixed top-8 right-8 z-[100] flex flex-col gap-3"></div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-brand-primary/5 transition-all group">
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
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-blue-500/5 transition-all group">
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
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-amber-500/5 transition-all group">
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
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-50 hover:shadow-xl hover:shadow-purple-500/5 transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-purple-500 bg-purple-50 px-2 py-1 rounded-lg">Total</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tabungan Masuk</p>
                    <p class="text-3xl font-black text-gray-900 mt-1"><small class="text-sm font-bold">Rp</small>
                        <?php echo number_format($totalSavings / 1000000, 1); ?><small
                            class="text-sm font-bold">Jt</small></p>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
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
                                                <button onclick="showSuccessNotification('Pembayaran terverifikasi!')"
                                                    class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm"
                                                    title="Terima">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button onclick="showErrorNotification('Pembayaran ditolak')"
                                                    class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm"
                                                    title="Tolak">
                                                    <i class="fas fa-xmark"></i>
                                                </button>
                                                <button
                                                    class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm"
                                                    title="Lihat Bukti">
                                                    <i class="fas fa-eye"></i>
                                                </button>
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

    <script>
        function showSuccessNotification(message) {
            createNotification('success', message);
        }

        function showErrorNotification(message) {
            createNotification('error', message);
        }

        function createNotification(type, message) {
            const container = document.getElementById('notification-container');
            const id = 'notif-' + Date.now();

            const bgColor = type === 'success' ? 'bg-[#0f965d]' : 'bg-[#dc2626]';
            const icon = type === 'success' ? 'fa-check' : 'fa-xmark';
            const title = type === 'success' ? 'BERHASIL!' : 'GAGAL!';

            const notification = document.createElement('div');
            notification.id = id;
            notification.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-5 min-w-[320px] animate-in slide-in-from-right duration-300 transform transition-all`;

            notification.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas ${icon} text-sm"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1">${title}</p>
                    <p class="text-sm font-bold">${message}</p>
                </div>
                <button onclick="removeNotification('${id}')" class="text-white/50 hover:text-white transition-colors">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            `;

            container.appendChild(notification);

            // Auto-hide after 4 seconds
            setTimeout(() => {
                removeNotification(id);
            }, 4000);
        }

        function removeNotification(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('opacity-0', 'translate-x-10');
                setTimeout(() => {
                    el.remove();
                }, 300);
            }
        }
    </script>
</body>

</html>