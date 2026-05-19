<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Detail Transaksi';
    $topbarSubtitle = 'Detail status pesanan dan verifikasi transfer pelanggan';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="w-full space-y-8">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <a href="/lautan-ternak-pantura/order/transactions"
                    class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Detail <span
                            class="text-brand-primary">Transaksi #<?php echo $order['order_code']; ?></span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Kelola status pesanan dan
                        verifikasi transfer pelanggan</p>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left 2 Cols: Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Customer Information Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Informasi Pelanggan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Nama Lengkap
                                </p>
                                <p class="text-sm font-bold text-gray-800 mt-1">
                                    <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Email</p>
                                <p class="text-sm font-bold text-gray-800 mt-1">
                                    <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Telepon</p>
                                <p class="text-sm font-bold text-gray-800 mt-1">
                                    <?php echo htmlspecialchars($order['customer_phone'] ?: '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Alamat
                                    Pengiriman</p>
                                <p class="text-sm font-bold text-gray-800 mt-1">
                                    <?php echo htmlspecialchars($order['customer_address'] ?: '-'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Animal Detail Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Detail Hewan</h3>
                        <div class="flex flex-col sm:flex-row gap-6">
                            <div
                                class="w-full sm:w-28 h-28 rounded-2xl overflow-hidden bg-gray-100 border border-gray-100 shrink-0">
                                <img src="<?php echo $order['livestock_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'; ?>"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow flex flex-col justify-between py-1">
                                <div>
                                    <h4 class="text-lg font-black text-gray-900 capitalize mt-3">
                                        <?php echo htmlspecialchars($order['livestock_name']); ?></h4>
                                    <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">Breed:
                                        <?php echo htmlspecialchars($order['livestock_breed']); ?> &middot; Kode:
                                        <?php echo htmlspecialchars($order['livestock_code']); ?></p>
                                </div>
                                <div class="text-xs font-bold text-gray-500 mt-2">
                                    <span class="mr-4"><i
                                            class="fas fa-weight-hanging text-brand-primary mr-1.5"></i><?php echo $order['livestock_weight']; ?>
                                        kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Review and Approval Area -->
                    <?php if ($payment): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-black text-gray-900 tracking-tight">Bukti Pembayaran Pelanggan</h3>
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php
                                echo $payment['payment_status'] === 'verified' ? 'bg-emerald-50 text-emerald-600' : ($payment['payment_status'] === 'rejected' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600');
                                ?>">
                                    Status Bukti:
                                    <?php echo $payment['payment_status'] === 'verified' ? 'Diterima' : ($payment['payment_status'] === 'rejected' ? 'Ditolak' : 'Pending Review'); ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Metode
                                            Pembayaran</p>
                                        <p class="text-sm font-bold text-gray-800 mt-1">
                                            <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Jumlah
                                            Ditransfer</p>
                                        <p class="text-base font-black text-brand-primary mt-1">Rp
                                            <?php echo number_format($payment['amount'], 0, ',', '.'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Tanggal
                                            Upload</p>
                                        <p class="text-xs font-bold text-gray-600 mt-1">
                                            <?php echo date('d M Y, H:i', strtotime($payment['created_at'])); ?></p>
                                    </div>
                                    <?php if ($payment['verified_by']): ?>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">
                                                Diverifikasi Oleh</p>
                                            <p class="text-xs font-bold text-gray-600 mt-1">
                                                <?php echo htmlspecialchars($payment['verifier_name']); ?> pada
                                                <?php echo date('d M Y', strtotime($payment['verified_at'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div
                                    class="w-full h-52 rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 relative group cursor-zoom-in">
                                    <img src="<?php echo $payment['payment_proof']; ?>" class="w-full h-full object-cover">
                                    <button type="button"
                                        onclick="openImageModal('<?php echo $payment['payment_proof']; ?>')"
                                        class="absolute inset-0 w-full h-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white font-black text-xs uppercase tracking-widest outline-none">
                                        <i class="fas fa-search-plus mr-2"></i> Perbesar Bukti
                                    </button>
                                </div>
                            </div>

                            <!-- Verification Actions -->
                            <?php if ($payment['payment_status'] === 'pending'): ?>
                                <form method="POST"
                                    action="/lautan-ternak-pantura/order/transaction_detail/<?php echo $order['id']; ?>"
                                    class="flex gap-4 border-t border-gray-50 pt-6">
                                    <input type="hidden" name="action" value="verify">
                                    <button type="submit" name="status" value="rejected"
                                        class="flex-1 bg-red-50 hover:bg-red-100 text-red-500 py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-times-circle"></i> Tolak Pembayaran
                                    </button>
                                    <button type="submit" name="status" value="verified"
                                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-sm shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i> Terima & Lunas
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-amber-50 rounded-2xl border border-amber-100 p-8 text-center text-amber-800">
                            <i class="fas fa-exclamation-triangle text-2xl mb-3"></i>
                            <p class="text-sm font-bold">Pelanggan belum mengunggah bukti transfer pembayaran.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right 1 Col: Status & Controls -->
                <div class="space-y-6">
                    <!-- Status & Workflow Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Status & Alur Kerja</h3>

                        <form method="POST"
                            action="/lautan-ternak-pantura/order/transaction_detail/<?php echo $order['id']; ?>"
                            class="space-y-4">
                            <input type="hidden" name="action" value="update_order_status">

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tahapan
                                    Transaksi</label>
                                <div class="relative">
                                    <select name="order_status"
                                        class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-black text-sm appearance-none">
                                        <?php
                                        $states = [
                                            'pending' => 'Pending',
                                            'waiting_payment' => 'Menunggu Pembayaran',
                                            'payment_review' => 'Verifikasi Pembayaran',
                                            'paid' => 'Lunas (Paid)',
                                            'processing' => 'Diproses',
                                            'delivered' => 'Dikirim',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Batal'
                                        ];
                                        foreach ($states as $key => $lbl):
                                            $selected = $order['status'] === $key ? 'selected' : '';
                                            echo "<option value='{$key}' {$selected}>{$lbl}</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-brand-primary text-white py-4 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> Perbarui Status
                            </button>
                        </form>
                    </div>

                    <!-- Snapshot Invoice Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-sm font-black text-gray-900 tracking-tight">Ringkasan Nilai</h3>
                        <div class="space-y-4 border-b border-gray-100 pb-6 text-sm font-bold text-gray-500">
                            <div class="flex justify-between">
                                <span>Harga</span>
                                <span>Rp
                                    <?php echo number_format($order['livestock_price_snapshot'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Kuantitas</span>
                                <span><?php echo $order['qty']; ?> ekor</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-gray-900">Total Harga</span>
                            <span class="text-lg font-black text-brand-primary">Rp
                                <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Image Preview Modal -->
<div id="image-modal-overlay"
    class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0"
    onclick="closeImageModal()">
    <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center"
        onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageModal()"
            class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="image-modal-preview" src="" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
    </div>
</div>

<script>
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
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }, 300);
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>