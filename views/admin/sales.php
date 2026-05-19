<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Sales Data (Orders)
try {
    $stmt = $conn->query("
        SELECT o.*, o.created_at as order_date, u.name as customer_name, l.name as livestock_type, l.price as livestock_price, p.payment_proof
        FROM orders o
        JOIN users u ON o.customer_id = u.id
        JOIN livestock l ON o.livestock_id = l.id
        LEFT JOIN payments p ON p.order_id = o.id
        ORDER BY o.created_at DESC
    ");
    $salesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
    $salesList = [];
}

// Fetch Customers (for dropdown)
$customers = [];
try {
    $stmtCust = $conn->query("SELECT id, name, email, address FROM users WHERE role != 'admin' ORDER BY name ASC");
    $customers = $stmtCust->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}

// Fetch Livestock (for dropdown)
$livestockDropdown = [];
try {
    $stmtLive = $conn->query("SELECT id, name as type, code, price, status FROM livestock ORDER BY status ASC, name ASC");
    $livestockDropdown = $stmtLive->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan Hewan - Admin Dashboard</title>
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
    <link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <!-- Top Navigation -->
        <?php include 'includes/topbar.php'; ?>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kelola <span class="text-brand-primary">Penjualan Hewan</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Monitor & kelola transaksi penjualan ke pelanggan</p>
                </div>
            </div>


            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-50 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-2xl">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
                        <h3 class="text-2xl font-black text-gray-900" id="stats-total-orders"><?php echo count($salesList); ?></h3>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-50 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Penjualan</p>
                        <h3 class="text-2xl font-black text-gray-900" id="stats-total-sales">Rp <?php 
                            $total = array_sum(array_column($salesList, 'total_price'));
                            echo number_format($total, 0, ',', '.'); 
                        ?></h3>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-50 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pending</p>
                        <h3 class="text-2xl font-black text-gray-900" id="stats-pending-orders"><?php 
                            $pending = array_filter($salesList, function($s) { return $s['status'] === 'pending'; });
                            echo count($pending); 
                        ?></h3>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="relative w-full sm:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-input" placeholder="Cari penjualan (Nama Pelanggan, Hewan...)" 
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-100 rounded-2xl outline-none focus:border-brand-primary transition-all text-sm font-medium shadow-sm" onkeyup="filterSalesTable()">
                </div>
                <button onclick="openModal('add')" class="bg-brand-primary text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center gap-3">
                    <i class="fas fa-plus"></i> Tambah Penjualan Baru
                </button>
            </div>

            <!-- Sales Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full" id="sales-table">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pesanan / Pelanggan</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Hewan & Total Harga</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Bukti Bayar</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tanggal</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($salesList)): ?>
                                <tr id="no-data-row">
                                    <td colspan="7" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Belum ada data penjualan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($salesList as $i => $item): ?>
                                <tr class="hover:bg-brand-light/20 transition-colors group sale-row" id="sale-row-<?php echo $item['id']; ?>">
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-gray-400 index-column"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div>
                                            <p class="text-sm font-black text-gray-900 leading-none">#<?php echo htmlspecialchars($item['order_code']); ?></p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1.5 uppercase tracking-widest customer-name-column"><?php echo htmlspecialchars($item['customer_name']); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                                                <p class="text-xs font-bold text-gray-700 capitalize livestock-info-column"><?php echo htmlspecialchars($item['livestock_type']); ?></p>
                                            </div>
                                            <p class="text-xs font-bold text-brand-primary mt-1">Rp <?php echo number_format($item['total_price'], 0, ',', '.'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php 
                                            $statusClasses = [
                                                'pending' => 'bg-amber-50 text-amber-600',
                                                'waiting_payment' => 'bg-orange-50 text-orange-600',
                                                'payment_review' => 'bg-indigo-50 text-indigo-600',
                                                'paid' => 'bg-blue-50 text-blue-600',
                                                'processing' => 'bg-violet-50 text-violet-600',
                                                'delivered' => 'bg-green-50 text-green-600',
                                                'completed' => 'bg-emerald-50 text-emerald-600',
                                                'cancelled' => 'bg-red-50 text-red-600'
                                            ];
                                            $statusText = [
                                                'pending' => 'Menunggu',
                                                'waiting_payment' => 'Menunggu Pembayaran',
                                                'payment_review' => 'Review Bayar',
                                                'paid' => 'Dibayar',
                                                'processing' => 'Diproses',
                                                'delivered' => 'Dikirim',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Batal'
                                            ];
                                            $class = $statusClasses[$item['status']] ?? 'bg-gray-50 text-gray-400';
                                            $text = $statusText[$item['status']] ?? $item['status'];
                                        ?>
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full <?php echo $class; ?> uppercase status-badge">
                                            <?php echo $text; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-xs font-bold text-gray-400"><?php echo date('d M Y H:i', strtotime($item['order_date'])); ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <?php if (!empty($item['payment_proof'])): ?>
                                            <button onclick="openImageModal('<?php echo htmlspecialchars($item['payment_proof']); ?>')" class="inline-flex px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-[10px] uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-all shadow-sm gap-2 items-center" title="Lihat Bukti Pembayaran">
                                                <i class="fas fa-image"></i> Lihat
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-gray-300">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/lautan-ternak-pantura/order/transaction_detail/<?php echo $item['id']; ?>" class="inline-flex w-10 h-10 rounded-xl bg-gray-50 hover:bg-brand-primary text-gray-400 hover:text-white items-center justify-center transition-all shadow-sm">
                                                <i class="fas fa-search-plus"></i>
                                            </a>
                                            <button onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($item)); ?>)" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $item['id']; ?>, '#<?php echo htmlspecialchars($item['order_code']); ?>')" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer with Pagination -->
                <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tampilkan</span>
                        <div class="relative">
                            <select id="entries-per-page" onchange="changeEntriesPerPage(this.value)" class="pl-4 pr-10 py-2 bg-white border border-gray-100 rounded-lg outline-none focus:border-brand-primary text-xs font-bold text-gray-700 appearance-none cursor-pointer shadow-sm">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">data</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="prevPage()" id="prev-btn" class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i class="fas fa-chevron-left text-xs"></i></button>
                        <div id="page-numbers" class="flex items-center gap-1.5">
                            <!-- Dynamic page numbers -->
                        </div>
                        <button onclick="nextPage()" id="next-btn" class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i class="fas fa-chevron-right text-xs"></i></button>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="entries-info"></span>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form (Add / Edit) -->
    <div id="modal-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="modal-content" class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 flex flex-col">
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 shrink-0">
                <h3 id="modal-title" class="text-xl font-black text-brand-dark tracking-tight">Tambah Penjualan Baru</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
            </div>
            <div class="flex-grow overflow-y-auto custom-scrollbar">
                <form id="sale-form" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <!-- Customer Selection -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pelanggan (Customer)</label>
                        <div class="relative">
                            <select name="customer_id" id="edit-customer" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                <option value="" disabled selected>Pilih Pelanggan</option>
                                <option value="manual">[ + Pelanggan Baru / Walk-in ]</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" data-address="<?php echo htmlspecialchars($c['address']); ?>"><?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['email']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Customer Manual Fields (Hidden by default) -->
                    <div id="customer-manual-fields" class="hidden md:col-span-2 bg-brand-light/30 p-6 rounded-xl border border-brand-primary/10 space-y-4">
                        <p class="text-xs font-black text-brand-primary uppercase tracking-wider mb-2"><i class="fas fa-user-plus mr-2"></i>Informasi Pelanggan Baru</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Lengkap</label>
                                <input type="text" name="manual_customer_name" id="manual-customer-name" placeholder="Nama lengkap..." class="w-full px-4 py-3 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1">Nomor WhatsApp/HP</label>
                                <input type="text" name="manual_customer_phone" placeholder="08123456789..." class="w-full px-4 py-3 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold">
                            </div>
                            <div class="space-y-2 col-span-1 md:col-span-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1">Email Pelanggan (Opsional)</label>
                                <input type="email" name="manual_customer_email" placeholder="email@contoh.com..." class="w-full px-4 py-3 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold">
                            </div>
                            <div class="space-y-2 col-span-1 md:col-span-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1">Alamat Pengiriman</label>
                                <input type="text" name="manual_customer_address" placeholder="Kecamatan / Kabupaten / Provinsi..." class="w-full px-4 py-3 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- Existing Customer Address Field (Hidden by default) -->
                    <div id="existing-customer-address-field" class="hidden md:col-span-2 space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Alamat Pengiriman Pelanggan (Jika Kosong / Ubah)</label>
                        <input type="text" name="existing_customer_address" id="existing-customer-address" placeholder="Masukkan alamat pengiriman..." class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                    </div>

                    <!-- Livestock Selection -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Hewan Ternak</label>
                        <div class="relative">
                            <select name="livestock_id" id="edit-livestock" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                <option value="" disabled selected>Pilih Hewan Ternak</option>
                                <?php foreach ($livestockDropdown as $l): ?>
                                    <?php 
                                        $statusLabel = [
                                            'available' => 'Tersedia',
                                            'booked' => 'Dipesan',
                                            'sold' => 'Terjual'
                                        ][$l['status']] ?? $l['status'];
                                    ?>
                                    <option value="<?php echo $l['id']; ?>" data-price="<?php echo $l['price']; ?>">
                                        <?php echo htmlspecialchars($l['code']); ?>: <?php echo htmlspecialchars($l['type']); ?> - Rp <?php echo number_format($l['price'], 0, ',', '.'); ?> [<?php echo $statusLabel; ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Total Price -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Total Harga Transaksi (Rp)</label>
                        <input type="number" name="total_price" id="edit-price" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm" placeholder="Masukkan total harga">
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Status Transaksi</label>
                        <div class="relative">
                            <select name="status" id="edit-status" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                <option value="pending">Menunggu Pembayaran</option>
                                <option value="waiting_payment">Menunggu Bukti Transfer</option>
                                <option value="payment_review">Review Bukti Bayar</option>
                                <option value="paid">Sudah Dibayar</option>
                                <option value="processing">Sedang Diproses</option>
                                <option value="delivered">Hewan Dikirim</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Metode Pembayaran</label>
                        <div class="relative">
                            <select name="payment_method" id="edit-payment-method" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Payment Proof Upload -->
                    <div class="space-y-2" id="payment-proof-field" style="display: none;">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Upload Bukti Transfer (Opsional)</label>
                        <input type="file" name="payment_proof" id="edit-payment-proof" accept="image/*" class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                    </div>

                    <!-- Order Date -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tanggal Transaksi</label>
                        <input type="datetime-local" name="order_date" id="edit-date" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                    </div>

                    <div class="md:col-span-2 pt-6 flex gap-4">
                        <button type="button" onclick="closeModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                        <button type="submit" id="submit-btn" class="flex-1 px-6 py-4 bg-brand-primary text-white rounded-2xl font-black text-sm hover:bg-brand-dark shadow-xl shadow-brand-primary/20 transition-all flex items-center justify-center gap-2">
                            <span>Simpan Transaksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-modal-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0" onclick="closeImageModal()">
        <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
            <button type="button" onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <img id="image-modal-preview" src="" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="delete-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="delete-content" class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 p-10 text-center">
            <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Hapus Transaksi?</h3>
            <p id="delete-message" class="text-sm text-gray-400 font-bold mb-8">Apakah Anda yakin ingin menghapus data penjualan ini? Status hewan ternak akan dikembalikan menjadi tersedia.</p>
            <input type="hidden" id="delete-id">
            <div class="flex gap-4">
                <button onclick="closeDeleteModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button onclick="executeDelete()" id="delete-btn" class="flex-1 px-6 py-4 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 shadow-xl shadow-red-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> <span>Ya, Hapus</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function showSuccessNotification(message) { showToast(message, 'success'); }
        function showErrorNotification(message) { showToast(message, 'error'); }

        function filterSalesTable() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('.sale-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const customer = row.querySelector('.customer-name-column').innerText.toLowerCase();
                const livestock = row.querySelector('.livestock-info-column').innerText.toLowerCase();
                const orderCode = row.querySelector('td:nth-child(2) p').innerText.toLowerCase();

                if (customer.includes(query) || livestock.includes(query) || orderCode.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle no data search result fallback
            let noDataRow = document.getElementById('no-search-results-row');
            if (visibleCount === 0) {
                if (!noDataRow) {
                    noDataRow = document.createElement('tr');
                    noDataRow.id = 'no-search-results-row';
                    noDataRow.innerHTML = `
                        <td colspan="7" class="px-8 py-10 text-center text-gray-400 font-bold">
                            Tidak ada penjualan yang cocok dengan pencarian Anda.
                        </td>
                    `;
                    document.querySelector('#sales-table tbody').appendChild(noDataRow);
                }
            } else {
                if (noDataRow) noDataRow.remove();
            }
        }

        // Toggles manual custom fields in UI
        document.getElementById('edit-customer').addEventListener('change', function() {
            const manualFields = document.getElementById('customer-manual-fields');
            const existingAddressField = document.getElementById('existing-customer-address-field');
            if (this.value === 'manual') {
                manualFields.classList.remove('hidden');
                existingAddressField.classList.add('hidden');
                document.getElementById('manual-customer-name').required = true;
            } else if (this.value) {
                manualFields.classList.add('hidden');
                document.getElementById('manual-customer-name').required = false;
                
                const selectedOption = this.options[this.selectedIndex];
                const address = selectedOption.getAttribute('data-address');
                existingAddressField.classList.remove('hidden');
                document.getElementById('existing-customer-address').value = address || '';
            } else {
                manualFields.classList.add('hidden');
                existingAddressField.classList.add('hidden');
            }
        });

        document.getElementById('edit-payment-method').addEventListener('change', function() {
            if (this.value === 'Transfer Bank') {
                document.getElementById('payment-proof-field').style.display = 'block';
            } else {
                document.getElementById('payment-proof-field').style.display = 'none';
            }
        });

        document.getElementById('edit-livestock').addEventListener('change', function() {
            // Auto-fill total price with selected livestock price
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const price = selectedOption.getAttribute('data-price');
                document.getElementById('edit-price').value = price || '';
            }
        });

        function handleLivestockChange() {
            const select = document.getElementById('edit-livestock');
            if (select.value === 'manual') return;

            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption) {
                const price = selectedOption.getAttribute('data-price');
                if (price) {
                    document.getElementById('edit-price').value = price;
                }
            }
        }

        function openModal(type, data = null) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('sale-form');
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
            
            document.getElementById('customer-manual-fields').classList.add('hidden');
            document.getElementById('existing-customer-address-field').classList.add('hidden');
            document.getElementById('payment-proof-field').style.display = 'none';
            document.getElementById('manual-customer-name').required = false;

            if (type === 'edit' && data) {
                title.innerText = 'Edit Transaksi Penjualan';
                document.getElementById('edit-id').value = data.id;
                document.getElementById('edit-customer').value = data.customer_id;
                document.getElementById('edit-customer').dispatchEvent(new Event('change'));
                document.getElementById('edit-livestock').value = data.livestock_id;
                document.getElementById('edit-price').value = data.total_price;
                document.getElementById('edit-status').value = data.status;
                
                if (data.order_date) {
                    const date = new Date(data.order_date);
                    const tzoffset = date.getTimezoneOffset() * 60000; 
                    const localISOTime = (new Date(date - tzoffset)).toISOString().slice(0, 16);
                    document.getElementById('edit-date').value = localISOTime;
                } else {
                    document.getElementById('edit-date').value = '';
                }
            } else {
                title.innerText = 'Tambah Penjualan Baru';
                form.reset();
                document.getElementById('edit-id').value = '';
                
                const now = new Date();
                const tzoffset = now.getTimezoneOffset() * 60000;
                const localISOTime = (new Date(now - tzoffset)).toISOString().slice(0, 16);
                document.getElementById('edit-date').value = localISOTime;
            }
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        function openDeleteModal(id, code) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-message').innerText = `Apakah Anda yakin ingin menghapus transaksi "${code}"? Tindakan ini tidak bisa dibatalkan dan status hewan terkait akan dikembalikan menjadi 'Tersedia'.`;
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
        }

        function closeDeleteModal() {
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function executeDelete() {
            const id = document.getElementById('delete-id').value;
            const btn = document.getElementById('delete-btn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menghapus...</span>';
            btn.disabled = true;
            try {
                const res = await fetch(`/lautan-ternak-pantura/api/admin/delete_sale?id=${id}`);
                const data = await res.json();
                if (data.success) {
                    closeDeleteModal();
                    showSuccessNotification(data.message);
                    const row = document.getElementById(`sale-row-${id}`);
                    row.classList.add('opacity-0', '-translate-y-4');
                    setTimeout(() => {
                        row.remove();
                        location.reload();
                    }, 1000);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Gagal menghubungi server');
            }
            btn.innerHTML = '<i class="fas fa-trash"></i> <span>Ya, Hapus</span>';
            btn.disabled = false;
        }

        document.getElementById('sale-form').onsubmit = async function(e) {
            e.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            const formData = new FormData(this);
            const isEdit = formData.get('id') && formData.get('id') !== '';
            const url = isEdit ? '/lautan-ternak-pantura/api/admin/update_sale' : '/lautan-ternak-pantura/api/admin/add_sale';

            const btn = document.getElementById('submit-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menyimpan...</span>';
            btn.disabled = true;

            try {
                const res = await fetch(url, { 
                    method: 'POST', 
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    showSuccessNotification(data.message);
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorNotification(data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                showErrorNotification('Koneksi bermasalah atau data tidak valid');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        // Client-side pagination engine
        let currentPage = 1;
        let rowsPerPage = 10;
        let tableRows = [];

        function initPagination() {
            tableRows = Array.from(document.querySelectorAll('#sales-table tbody tr')).filter(row => row.id !== 'no-data-row');
            showPage(1);
        }

        function showPage(page) {
            currentPage = page;
            const totalRows = tableRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
            
            if (currentPage < 1) currentPage = 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * rowsPerPage;
            const endIdx = Math.min(startIdx + rowsPerPage, totalRows);

            tableRows.forEach((row, idx) => {
                if (idx >= startIdx && idx < endIdx) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });

            updatePaginationUI(totalPages, totalRows, startIdx, endIdx);
        }

        function updatePaginationUI(totalPages, totalRows, startIdx, endIdx) {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const pageNumbers = document.getElementById('page-numbers');
            const infoText = document.getElementById('entries-info');

            if (prevBtn) prevBtn.disabled = currentPage === 1;
            if (nextBtn) nextBtn.disabled = currentPage === totalPages;

            // Apply opacity styles for disabled state
            if (prevBtn) {
                if (currentPage === 1) prevBtn.classList.add('opacity-40', 'cursor-not-allowed');
                else prevBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            }
            if (nextBtn) {
                if (currentPage === totalPages) nextBtn.classList.add('opacity-40', 'cursor-not-allowed');
                else nextBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            }

            if (infoText) {
                if (totalRows === 0) {
                    infoText.innerText = "Tidak ada data";
                } else {
                    infoText.innerText = `Menampilkan ${startIdx + 1}-${endIdx} dari ${totalRows} data`;
                }
            }

            if (pageNumbers) {
                pageNumbers.innerHTML = "";
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const btn = document.createElement('button');
                    btn.innerText = i;
                    btn.onclick = () => showPage(i);
                    btn.className = `w-8 h-8 rounded-lg text-xs font-black transition-all shadow-sm ${
                        currentPage === i 
                            ? 'bg-brand-primary text-white shadow-brand-primary/20' 
                            : 'border border-gray-100 bg-white text-gray-500 hover:text-brand-primary hover:border-brand-primary/20'
                    }`;
                    pageNumbers.appendChild(btn);
                }
            }
        }

        function prevPage() {
            if (currentPage > 1) showPage(currentPage - 1);
        }

        function nextPage() {
            const totalPages = Math.ceil(tableRows.length / rowsPerPage) || 1;
            if (currentPage < totalPages) showPage(currentPage + 1);
        }

        function changeEntriesPerPage(val) {
            rowsPerPage = parseInt(val);
            showPage(1);
        }

        window.addEventListener('DOMContentLoaded', () => {
            initPagination();
        });

        function openImageModal(src) {
            const overlay = document.getElementById('image-modal-overlay');
            const img = document.getElementById('image-modal-preview');
            img.src = src;
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
        }

        function closeImageModal() {
            const overlay = document.getElementById('image-modal-overlay');
            if (overlay) {
                overlay.classList.add('opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }, 300);
            }
        }
    </script>
</body>
</html>
