<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Detail Transaksi Penjualan';
    $topbarSubtitle = 'Invoice #' . htmlspecialchars($sale['invoice_code']);
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">

        <!-- Navigation & Actions Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="/lautan-ternak-pantura/sales/index"
                    class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Detail <span class="text-brand-primary">Invoice Penjualan</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Invoice: #<?php echo htmlspecialchars($sale['invoice_code']); ?></p>
                </div>
            </div>
            
            <?php if ($remaining > 0): ?>
                <button onclick="openPaymentModal()"
                    class="bg-brand-primary text-white px-6 py-3 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                    <i class="fas fa-plus"></i> Catat Pembayaran / Cicilan
                </button>
            <?php endif; ?>
        </div>

        <!-- Success/Error Alerts -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['success_msg'])); ?>", "success");
                });
            </script>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['error'])); ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Detail & Ledger (2 Columns) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Info Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Customer Card -->
                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-user text-brand-primary"></i> Data Pelanggan
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Nama Lengkap</p>
                                <p class="text-sm font-black text-gray-800 capitalize"><?php echo htmlspecialchars($sale['customer_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Kontak / WhatsApp</p>
                                <p class="text-sm font-bold text-gray-700">
                                    <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $sale['customer_phone']); ?>" target="_blank" class="hover:underline hover:text-emerald-500">
                                        <i class="fab fa-whatsapp text-emerald-500 mr-1"></i><?php echo htmlspecialchars($sale['customer_phone'] ?: '-'); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Livestock Card -->
                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-paw text-brand-primary"></i> Hewan Ternak
                        </h3>
                        <div class="flex gap-4">
                            <?php if ($sale['livestock_image']): ?>
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                                    <img src="<?php echo htmlspecialchars($sale['livestock_image']); ?>" class="w-full h-full object-cover">
                                </div>
                            <?php endif; ?>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-gray-800 capitalize"><?php echo htmlspecialchars($sale['livestock_name']); ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Kode: <?php echo htmlspecialchars($sale['livestock_code'] ?: '-'); ?></p>
                                <p class="text-[10px] text-brand-primary font-bold uppercase">Pemasok: <?php echo htmlspecialchars($sale['peternak_name'] ?: '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger History (Payments Timeline) -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-6">
                    <h3 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <i class="fas fa-receipt text-brand-primary"></i> Histori Pembayaran & Ledger
                    </h3>

                    <?php if (empty($payments)): ?>
                        <div class="text-center py-10 text-gray-400 font-bold">
                            <i class="fas fa-wallet text-3xl mb-3 block text-gray-200"></i>
                            Belum ada rekaman pembayaran untuk transaksi ini.
                        </div>
                    <?php else: ?>
                        <div class="relative border-l-2 border-gray-100 pl-6 ml-4 space-y-8">
                            <?php foreach ($payments as $pay): ?>
                                <div class="relative">
                                    <!-- Timeline Dot -->
                                    <span class="absolute -left-[33px] top-1.5 w-4.5 h-4.5 rounded-full border-2 border-white bg-brand-primary shadow-sm"></span>
                                    
                                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-3">
                                                <h4 class="text-xs font-black text-gray-800">#<?php echo htmlspecialchars($pay['payment_code']); ?></h4>
                                                <?php
                                                $statusBadge = [
                                                    'pending' => 'bg-amber-50 text-amber-600',
                                                    'verified' => 'bg-emerald-50 text-emerald-600 font-bold',
                                                    'rejected' => 'bg-red-50 text-red-500'
                                                ][$pay['payment_status']] ?? 'bg-gray-50 text-gray-400';
                                                ?>
                                                <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded <?php echo $statusBadge; ?>">
                                                    <?php echo $pay['payment_status']; ?>
                                                </span>
                                            </div>
                                            <p class="text-base font-black text-gray-900">
                                                Rp <?php echo number_format($pay['payment_amount'], 0, ',', '.'); ?>
                                            </p>
                                            <div class="text-[10px] text-gray-400 font-bold uppercase space-x-2">
                                                <span>Method: <?php echo htmlspecialchars($pay['payment_method']); ?></span>
                                                <span>&bull;</span>
                                                <span>Date: <?php echo date('d M Y, H:i', strtotime($pay['payment_date'])); ?></span>
                                            </div>
                                            <p class="text-xs text-gray-500 italic">
                                                "<?php echo htmlspecialchars($pay['payment_note'] ?: '-'); ?>"
                                            </p>
                                        </div>

                                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto border-t md:border-t-0 pt-4 md:pt-0 shrink-0">
                                            <!-- Payment Proof -->
                                            <?php if ($pay['payment_proof']): ?>
                                                <button onclick="openImageModal('<?php echo htmlspecialchars($pay['payment_proof']); ?>')"
                                                    class="inline-flex px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-brand-primary font-bold text-[10px] uppercase tracking-widest shadow-sm gap-2 items-center transition-all">
                                                    <i class="fas fa-image"></i> Lihat Bukti
                                                </button>
                                            <?php endif; ?>

                                            <!-- Admin Verification Action -->
                                            <?php if ($pay['payment_status'] === 'pending'): ?>
                                                <div class="flex gap-2">
                                                    <form action="/lautan-ternak-pantura/sales/verifyPayment" method="POST">
                                                        <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                        <input type="hidden" name="status" value="verified">
                                                        <button type="submit" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-black text-[10px] uppercase tracking-widest transition-all shadow-md shadow-emerald-500/10">
                                                            Terima
                                                        </button>
                                                    </form>
                                                    <form action="/lautan-ternak-pantura/sales/verifyPayment" method="POST">
                                                        <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-black text-[10px] uppercase tracking-widest transition-all shadow-md shadow-red-500/10">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side: Status Updates & Financial Summary (1 Column) -->
            <div class="space-y-8">
                
                <!-- Financial Ledger Card -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest border-b border-gray-50 pb-4">
                        Ringkasan Transaksi
                    </h3>
                    
                    <div class="space-y-4 text-xs font-bold text-gray-500">
                        <div class="flex justify-between items-center">
                            <span>Metode Order</span>
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded <?php echo ($sale['payment_type'] === 'dp') ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'; ?>">
                                <?php echo strtoupper($sale['payment_type']); ?>
                            </span>
                        </div>
                        <?php if (!empty($sale['payment_method'])): ?>
                        <div class="flex justify-between items-center">
                            <span>Metode Pembayaran</span>
                            <span class="text-gray-800 font-black capitalize"><?php echo htmlspecialchars($sale['payment_method']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center">
                            <span>Status Bayar</span>
                            <?php
                            $pClasses = [
                                'unpaid' => 'bg-red-50 text-red-500',
                                'partial' => 'bg-amber-50 text-amber-600 border border-amber-100',
                                'paid' => 'bg-emerald-50 text-emerald-600 font-bold'
                            ][$sale['payment_status']] ?? 'bg-gray-50 text-gray-400';
                            ?>
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded <?php echo $pClasses; ?>">
                                <?php echo $sale['payment_status']; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Kuantitas</span>
                            <span class="text-gray-800"><?php echo $sale['qty']; ?> ekor</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Harga Hewan</span>
                            <span class="text-gray-800">Rp <?php echo number_format($sale['selling_price_snapshot'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-50 pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Tagihan</span>
                            <span class="text-sm font-extrabold text-gray-700">Rp <?php echo number_format($sale['total_price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Terbayar</span>
                            <span class="text-sm font-extrabold text-emerald-600">Rp <?php echo number_format($totalPaid, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-t border-dashed border-gray-100 pt-3">
                            <span class="text-sm font-black text-gray-900">Sisa Tagihan</span>
                            <span class="text-lg font-black text-brand-primary">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Update delivery status Card -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-4">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-truck text-brand-primary"></i> Status Pengiriman / Pesanan
                    </h3>
                    <form action="/lautan-ternak-pantura/sales/updateStatus" method="POST" class="space-y-4">
                        <input type="hidden" name="sale_id" value="<?php echo $sale['id']; ?>">
                        
                        <div class="relative">
                            <select name="sale_status" class="w-full pl-4 pr-10 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                                <option value="pending" <?php echo ($sale['sale_status'] === 'pending') ? 'selected' : ''; ?>>Menunggu (Pending)</option>
                                <option value="processing" <?php echo ($sale['sale_status'] === 'processing') ? 'selected' : ''; ?>>Diproses (Processing)</option>
                                <option value="completed" <?php echo ($sale['sale_status'] === 'completed') ? 'selected' : ''; ?>>Selesai (Completed)</option>
                                <option value="cancelled" <?php echo ($sale['sale_status'] === 'cancelled') ? 'selected' : ''; ?>>Batal (Cancelled)</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>

                        <button type="submit" class="w-full bg-brand-primary hover:bg-brand-dark text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-md shadow-brand-primary/20">
                            Perbarui Status
                        </button>
                    </form>
                </div>

                <!-- Notes Card -->
                <?php if ($sale['notes']): ?>
                    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-3">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Catatan Transaksi</h4>
                        <p class="text-sm font-bold text-gray-600 italic bg-gray-50 p-4 rounded-xl border border-gray-100/50">"<?php echo htmlspecialchars($sale['notes']); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Floating Installment Modal -->
<div id="payment-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-wallet text-brand-primary"></i> Catat Pembayaran Baru
            </h3>
            <button onclick="closeModal('payment')"
                class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/sales/record_payment" method="POST" enctype="multipart/form-data" onsubmit="return validatePaymentForm(event)" class="space-y-4">
            <input type="hidden" name="sale_id" value="<?php echo $sale['id']; ?>">

            <div class="bg-brand-light/20 p-4 rounded-xl text-xs font-bold text-brand-primary flex justify-between items-center mb-2">
                <span>Maksimal Cicilan:</span>
                <span id="max_allowed_amt" data-val="<?php echo $remaining; ?>">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></span>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah Nominal Pembayaran (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="text" id="payment_amount" name="payment_amount" required oninput="formatCurrency(this)"
                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Metode Pembayaran</label>
                <div class="relative">
                    <select name="payment_method" class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 appearance-none">
                        <option value="Tunai / Cash">Tunai / Cash</option>
                        <option value="Transfer Bank Manual">Transfer Bank Manual</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Catatan Pembayaran (Internal)</label>
                <textarea name="payment_note" rows="2" placeholder="Contoh: Pembayaran cicilan tahap 2..."
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Upload Bukti Bayar (Opsional)</label>
                <input type="file" name="payment_proof" accept="image/*"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('payment')"
                    class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Batal</button>
                <button type="submit"
                    class="px-6 py-3 bg-brand-primary text-white rounded-xl text-sm font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20">Catat</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Modal Overlay -->
<div id="image-modal-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0" onclick="closeImageModal()">
    <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="image-modal-preview" src="" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
    </div>
</div>

<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function openPaymentModal() {
        const modal = document.getElementById('payment-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closeModal(type) {
        const modal = document.getElementById(type + '-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function validatePaymentForm(event) {
        const amtInput = document.getElementById('payment_amount');
        const rawAmt = parseFloat(amtInput.value.replace(/\D/g, "")) || 0;
        const maxAmt = parseFloat(document.getElementById('max_allowed_amt').getAttribute('data-val')) || 0;

        if (rawAmt <= 0) {
            showToast('Nominal pembayaran harus diisi dan lebih dari 0!', 'error');
            event.preventDefault();
            return false;
        }

        if (rawAmt > maxAmt) {
            showToast('Nominal pembayaran melebihi sisa tagihan transaksi!', 'error');
            event.preventDefault();
            return false;
        }

        // Set raw numeric values back before submission
        amtInput.value = rawAmt;
        return true;
    }

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

<?php require_once 'views/admin/includes/footer.php'; ?>
