<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Pending Transactions
try {
    $stmt = $conn->query("
        SELECT st.*, sp.target_amount, u.name as customer_name, u.email as customer_email 
        FROM savings_transactions st
        JOIN savings_plans sp ON st.plan_id = sp.id
        JOIN users u ON sp.customer_id = u.id
        ORDER BY st.created_at DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Transfer - Admin Dashboard</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/ico" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar-link.active { background-color: rgba(13, 91, 181, 0.1); color: #0d5bb5; border-right: 4px solid #0d5bb5; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <!-- Top Navigation -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-gray-100 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="lg:hidden text-gray-500 text-xl"><i class="fas fa-bars"></i></button>
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Verifikasi Transfer</h2>
                    <p class="text-xs text-gray-400 font-bold">Validasi bukti pembayaran tabungan pelanggan</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-gray-900 leading-none"><?php echo $_SESSION['name']; ?></p>
                    <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Super Admin</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-primary border-2 border-brand-primary/20">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">
            
            <!-- Notification Container -->
            <div id="notification-container" class="fixed top-8 right-8 z-[100] flex flex-col gap-3"></div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pelanggan</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Jumlah</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Bukti</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tanggal</th>
                                <th class="px-6 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="7" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Belum ada transaksi transfer.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $i => $tx): ?>
                                <tr class="hover:bg-brand-light/20 transition-colors group" id="tx-row-<?php echo $tx['id']; ?>">
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div>
                                            <p class="text-sm font-black text-gray-900 leading-none"><?php echo $tx['customer_name']; ?></p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1"><?php echo $tx['customer_email']; ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-brand-primary">Rp <?php echo number_format($tx['amount']); ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <button onclick="viewProof('<?php echo $tx['proof_of_payment']; ?>')" class="text-brand-secondary hover:text-brand-primary flex items-center gap-2 font-bold text-xs">
                                            <i class="fas fa-image"></i> Lihat Bukti
                                        </button>
                                    </td>
                                    <td class="px-6 py-6">
                                        <?php 
                                            $statusClasses = [
                                                'pending' => 'bg-amber-50 text-amber-600',
                                                'verified' => 'bg-green-50 text-green-600',
                                                'rejected' => 'bg-red-50 text-red-600'
                                            ];
                                            $statusText = [
                                                'pending' => 'Menunggu',
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak'
                                            ];
                                            $class = $statusClasses[$tx['status']] ?? 'bg-gray-50 text-gray-400';
                                            $text = $statusText[$tx['status']] ?? $tx['status'];
                                        ?>
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full <?php echo $class; ?> uppercase tracking-wider">
                                            <?php echo $text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-xs font-bold text-gray-400"><?php echo date('d M Y H:i', strtotime($tx['created_at'])); ?></p>
                                    </td>
                                    <td class="px-6 py-6 text-right">
                                        <?php if ($tx['status'] === 'pending'): ?>
                                        <div class="flex justify-end gap-2">
                                            <button onclick="updateStatus(<?php echo $tx['id']; ?>, 'verified')" title="Verifikasi" class="w-9 h-9 rounded-xl bg-green-50 text-green-500 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                            <button onclick="updateStatus(<?php echo $tx['id']; ?>, 'rejected')" title="Tolak" class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-xmark text-xs"></i>
                                            </button>
                                        </div>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-gray-300 italic">Selesai</span>
                                        <?php endif; ?>
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

    <!-- Modal View Proof -->
    <div id="proof-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="proof-content" class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0">
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Bukti Transfer</h3>
                <button onclick="closeProof()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
            </div>
            <div class="p-8 text-center">
                <img id="proof-img" src="" alt="Bukti Transfer" class="max-w-full h-auto rounded-2xl mx-auto shadow-lg">
            </div>
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50 flex justify-end">
                <button onclick="closeProof()" class="px-6 py-3 bg-brand-primary text-white rounded-2xl font-black text-sm hover:bg-brand-dark transition-all">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function showSuccessNotification(message) { createNotification('success', message); }
        function showErrorNotification(message) { createNotification('error', message); }

        function createNotification(type, message) {
            const container = document.getElementById('notification-container');
            const id = 'notif-' + Date.now();
            const bgColor = type === 'success' ? 'bg-[#0f965d]' : 'bg-[#dc2626]';
            const icon = type === 'success' ? 'fa-check' : 'fa-xmark';
            const title = type === 'success' ? 'BERHASIL!' : 'GAGAL!';

            const notification = document.createElement('div');
            notification.id = id;
            notification.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-5 min-w-[320px] transition-all duration-500 transform translate-x-10 opacity-0`;
            notification.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0"><i class="fas ${icon} text-sm"></i></div>
                <div class="flex-grow">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1">${title}</p>
                    <p class="text-sm font-bold">${message}</p>
                </div>
                <button onclick="removeNotification('${id}')" class="text-white/50 hover:text-white transition-colors"><i class="fas fa-xmark text-lg"></i></button>
            `;
            container.appendChild(notification);

            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('translate-x-10', 'opacity-0');
            }, 10);

            setTimeout(() => removeNotification(id), 4000);
        }

        function removeNotification(id) {
            const el = document.getElementById(id);
            if (el) { el.classList.add('opacity-0', 'translate-x-10'); setTimeout(() => el.remove(), 300); }
        }

        function viewProof(url) {
            const overlay = document.getElementById('proof-overlay');
            const content = document.getElementById('proof-content');
            const img = document.getElementById('proof-img');
            
            // Handle placeholder or actual URL
            if (!url.startsWith('http') && !url.startsWith('/')) {
                img.src = '/lautan-ternak-pantura/assets/uploads/proofs/' + url;
            } else {
                img.src = url;
            }
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
        }

        function closeProof() {
            const overlay = document.getElementById('proof-overlay');
            const content = document.getElementById('proof-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function updateStatus(id, status) {
            const action = status === 'verified' ? 'memverifikasi' : 'menolak';
            if (!confirm(`Apakah Anda yakin ingin ${action} transaksi ini?`)) return;

            try {
                const res = await fetch('/lautan-ternak-pantura/api/admin/verify_transfer', {
                    method: 'POST',
                    body: JSON.stringify({ id, status }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    showSuccessNotification(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Koneksi bermasalah atau data tidak valid');
            }
        }
    </script>
</body>
</html>
