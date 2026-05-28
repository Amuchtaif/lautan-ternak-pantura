<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat <span class="text-brand-primary">Transaksi</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Histori pembelian langsung hewan qurban & aqiqah Anda</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/lautan-ternak-pantura/customer/dashboard" class="bg-white text-gray-700 px-5 py-3 rounded-2xl shadow-sm border border-gray-100 hover:bg-gray-50 transition-all text-sm font-black flex items-center gap-2">
                    <i class="fas fa-th-large text-brand-primary"></i> Dashboard
                </a>
                <a href="/lautan-ternak-pantura/marketplace" class="bg-brand-primary text-white px-5 py-3 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                    <i class="fas fa-plus"></i> Belanja Hewan
                </a>
            </div>
        </div>

        <?php if (empty($ordersList)): ?>
            <div class="bg-white rounded-3xl border border-gray-100 p-16 text-center shadow-sm">
                <div class="w-24 h-24 rounded-full bg-blue-50 text-brand-primary flex items-center justify-center mx-auto mb-6 text-4xl">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Belum ada transaksi</h3>
                <p class="text-sm text-gray-400 font-bold max-w-sm mx-auto mb-8">Anda belum pernah melakukan pembelian langsung. Silakan cari hewan qurban atau aqiqah impian Anda.</p>
                <a href="/lautan-ternak-pantura/marketplace" class="inline-flex items-center gap-2 bg-brand-primary/10 text-brand-primary px-6 py-3.5 rounded-2xl font-black text-sm hover:bg-brand-primary/20 transition-all">
                    Kunjungi Katalog <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        <?php else: ?>
            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Total Transaksi</p>
                        <h3 class="text-xl font-black text-gray-900"><?php echo count($ordersList); ?> Pesanan</h3>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Total Pengeluaran</p>
                        <h3 class="text-xl font-black text-gray-900">Rp <?php 
                            $totalSpent = array_sum(array_column($ordersList, 'total_price'));
                            echo number_format($totalSpent, 0, ',', '.'); 
                        ?></h3>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Transaksi Aktif</p>
                        <h3 class="text-xl font-black text-gray-900"><?php 
                            $activeCount = count(array_filter($ordersList, function($o) {
                                return in_array($o['sale_status'], ['pending', 'processing']);
                            }));
                            echo $activeCount; 
                        ?> Proses</h3>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-6">
                <?php foreach ($ordersList as $ord): ?>
                    <?php 
                        // Sale/Delivery Status
                        $saleClasses = [
                            'pending' => 'bg-amber-50 text-amber-600 border-amber-200/60',
                            'processing' => 'bg-blue-50 text-blue-600 border-blue-200/60',
                            'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-200/60',
                            'cancelled' => 'bg-red-50 text-red-500 border-red-200/60'
                        ][$ord['sale_status']] ?? 'bg-gray-50 text-gray-500 border-gray-200/60';

                        $saleLabels = [
                            'pending' => 'Menunggu Konfirmasi',
                            'processing' => 'Diproses Peternak',
                            'completed' => 'Pesanan Selesai',
                            'cancelled' => 'Pesanan Dibatalkan'
                        ][$ord['sale_status']] ?? $ord['sale_status'];

                        // Payment Status
                        $payClasses = [
                            'unpaid' => 'bg-red-50 text-red-600 border-red-200/60',
                            'partial' => 'bg-amber-50 text-amber-600 border-amber-200/60',
                            'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-200/60'
                        ][$ord['payment_status']] ?? 'bg-gray-50 text-gray-400';
                        
                        $payLabels = [
                            'unpaid' => 'Belum Bayar',
                            'partial' => 'DP / Sebagian',
                            'paid' => 'Lunas'
                        ][$ord['payment_status']] ?? $ord['payment_status'];
                    ?>
                    <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center gap-6 w-full lg:w-auto">
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 overflow-hidden border border-gray-100 shrink-0 shadow-inner">
                                <img src="<?php echo $ord['livestock_image'] ?: '/lautan-ternak-pantura/assets/images/default_animal.jpg'; ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h4 class="text-sm font-black text-gray-900 leading-none">#<?php echo htmlspecialchars($ord['invoice_code']); ?></h4>
                                    
                                    <!-- Payment Type Badge -->
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black uppercase rounded-md tracking-wider border <?php echo ($ord['payment_type'] === 'dp') ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'; ?>">
                                        <?php echo ($ord['payment_type'] === 'dp') ? 'DOWN PAYMENT (DP)' : 'LUNAS'; ?>
                                    </span>
                                    
                                    <!-- Payment Status Badge -->
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black uppercase rounded-md tracking-wider border <?php echo $payClasses; ?>">
                                        Bayar: <?php echo $payLabels; ?>
                                    </span>

                                    <!-- Sale Status Badge -->
                                    <span class="inline-block px-2.5 py-0.5 text-[9px] font-black uppercase rounded-md tracking-wider border <?php echo $saleClasses; ?>">
                                        <?php echo $saleLabels; ?>
                                    </span>
                                </div>
                                <h3 class="text-base font-black text-gray-900 capitalize"><?php echo htmlspecialchars($ord['livestock_name']); ?></h3>
                                <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest flex items-center gap-2">
                                    <span><i class="far fa-calendar-alt text-brand-primary mr-1"></i><?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?></span>
                                    <span>&bull;</span>
                                    <span>Qty: <strong class="text-gray-700"><?php echo $ord['qty']; ?></strong> ekor</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex sm:items-center justify-between lg:justify-end w-full lg:w-auto gap-8 border-t lg:border-t-0 pt-4 lg:pt-0">
                            <div class="lg:text-right">
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Total Tagihan</p>
                                <p class="text-xl font-black text-brand-primary mt-0.5">Rp <?php echo number_format($ord['total_price'], 0, ',', '.'); ?></p>
                            </div>
                            <a href="/lautan-ternak-pantura/sales/order_detail/<?php echo $ord['id']; ?>" class="bg-gray-50 hover:bg-brand-primary hover:text-white text-gray-700 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shrink-0 shadow-sm hover:shadow-md">
                                Detail Transaksi <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
