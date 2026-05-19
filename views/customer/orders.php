<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat <span class="text-brand-primary">Transaksi</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Histori pembelian hewan ternak langsung Anda</p>
            </div>
            <a href="/lautan-ternak-pantura/marketplace" class="bg-brand-primary text-white px-6 py-3 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                <i class="fas fa-plus"></i> Belanja Hewan
            </a>
        </div>

        <?php if (empty($ordersList)): ?>
            <div class="bg-white rounded-3xl border border-gray-100 p-12 text-center shadow-sm">
                <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-6 text-gray-300 text-3xl">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-2">Belum ada transaksi</h3>
                <p class="text-sm text-gray-400 font-bold max-w-sm mx-auto mb-8">Anda belum pernah melakukan pembelian langsung. Silakan cari hewan qurban atau aqiqah impian Anda.</p>
                <a href="/lautan-ternak-pantura/marketplace" class="inline-flex items-center gap-2 bg-brand-primary/10 text-brand-primary px-6 py-3 rounded-2xl font-black text-sm hover:bg-brand-primary/20 transition-all">
                    Kunjungi Marketplace <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($ordersList as $ord): ?>
                    <?php 
                        $statusClasses = [
                            'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                            'waiting_payment' => 'bg-orange-50 text-orange-600 border-orange-100',
                            'payment_review' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'paid' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                            'processing' => 'bg-purple-50 text-purple-600 border-purple-100',
                            'delivered' => 'bg-teal-50 text-teal-600 border-teal-100',
                            'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            'cancelled' => 'bg-red-50 text-red-500 border-red-100'
                        ][$ord['status']] ?? 'bg-gray-50 text-gray-500 border-gray-100';

                        $statusLabels = [
                            'pending' => 'Pending',
                            'waiting_payment' => 'Menunggu Pembayaran',
                            'payment_review' => 'Menunggu Verifikasi',
                            'paid' => 'Lunas / Dibayar',
                            'processing' => 'Diproses',
                            'delivered' => 'Dikirim',
                            'completed' => 'Selesai',
                            'cancelled' => 'Batal'
                        ][$ord['status']] ?? $ord['status'];
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-8 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 overflow-hidden border border-gray-100 shrink-0">
                                <img src="<?php echo $ord['livestock_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'; ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 class="text-sm font-black text-gray-900 leading-none">#<?php echo $ord['order_code']; ?></h4>
                                    <span class="inline-block px-2.5 py-1 text-[9px] font-black uppercase rounded-full border <?php echo $statusClasses; ?>">
                                        <?php echo $statusLabels; ?>
                                    </span>
                                </div>
                                <h3 class="text-base font-black text-gray-900 capitalize mt-3"><?php echo htmlspecialchars($ord['livestock_name']); ?></h3>
                                <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">
                                    <?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?> &middot; Qty: <?php echo $ord['qty']; ?> ekor
                                </p>
                            </div>
                        </div>

                        <div class="flex sm:items-center justify-between md:justify-end w-full md:w-auto gap-8 border-t md:border-t-0 pt-4 md:pt-0">
                            <div class="md:text-right">
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Total Bayar</p>
                                <p class="text-lg font-black text-brand-primary mt-0.5">Rp <?php echo number_format($ord['total_price'], 0, ',', '.'); ?></p>
                            </div>
                            <a href="/lautan-ternak-pantura/order/order_detail/<?php echo $ord['id']; ?>" class="bg-gray-50 hover:bg-brand-primary hover:text-white text-gray-700 px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                Detail Transaksi
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
