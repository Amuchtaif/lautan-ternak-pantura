<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/header.php'; 
?>

<style>
@media print {
    /* Hide global web elements */
    header, footer, nav, .no-print, .no-print * {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        background-color: white !important;
    }
    body * {
        visibility: hidden;
    }
    #invoice-print-area, #invoice-print-area * {
        visibility: visible;
    }
    #invoice-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
}
</style>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" id="invoice-print-area">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div class="flex items-center gap-4">
                <a href="/lautan-ternak-pantura/sales/my_orders" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all shrink-0">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Detail <span class="text-brand-primary">Transaksi</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Invoice: #<?php echo htmlspecialchars($sale['invoice_code']); ?></p>
                </div>
            </div>
            <button onclick="window.print()" class="bg-white text-gray-700 border border-gray-200 px-6 py-3.5 rounded-2xl shadow-sm hover:bg-gray-50 hover:shadow-md transition-all text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-download text-brand-primary"></i> Cetak / Download Invoice
            </button>
        </div>

        <!-- Print Header (Only visible on print) -->
        <div class="hidden print:block border-b-2 border-gray-200 pb-5 mb-8">
            <h1 class="text-3xl font-black text-gray-900">INVOICE PENJUALAN</h1>
            <p class="text-sm font-bold text-gray-500 mt-1">Lautan Ternak Pantura &bull; #<?php echo htmlspecialchars($sale['invoice_code']); ?></p>
        </div>

        <!-- Session Message Alerts -->
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
            <!-- Left Side: Order & Timeline Ledger (2 Columns) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Order Delivery Status alert -->
                <?php 
                    $saleClasses = [
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'processing' => 'bg-blue-50 text-blue-700 border-blue-100',
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'cancelled' => 'bg-red-50 text-red-600 border-red-100'
                    ][$sale['sale_status']] ?? 'bg-gray-50 text-gray-500 border-gray-100';

                    $saleLabels = [
                        'pending' => 'Menunggu Konfirmasi Admin',
                        'processing' => 'Hewan Sedang Dipersiapkan oleh Peternak',
                        'completed' => 'Pesanan Selesai / Hewan Diterima',
                        'cancelled' => 'Pesanan Dibatalkan / Gagal'
                    ][$sale['sale_status']] ?? $sale['sale_status'];
                ?>
                <div class="rounded-3xl border p-6 flex items-center gap-5 <?php echo $saleClasses; ?>">
                    <div class="w-12 h-12 rounded-2xl bg-white/80 backdrop-blur-md flex items-center justify-center text-xl shrink-0">
                        <i class="fas <?php echo $sale['sale_status'] === 'completed' ? 'fa-check-circle' : ($sale['sale_status'] === 'cancelled' ? 'fa-xmark' : 'fa-info-circle'); ?>"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60">Status Pesanan / Pengiriman</p>
                        <p class="text-base font-black mt-0.5"><?php echo $saleLabels; ?></p>
                    </div>
                </div>

                <!-- Animal Detail Info Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <i class="fas fa-paw text-brand-primary"></i> Detail Hewan Ternak
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="w-full sm:w-32 h-32 rounded-3xl overflow-hidden bg-gray-100 border border-gray-100 shrink-0 shadow-inner">
                            <img src="<?php echo $sale['livestock_image'] ?: '/lautan-ternak-pantura/assets/images/default_animal.jpg'; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow flex flex-col justify-between py-1">
                            <div>
                                <h4 class="text-lg font-black text-gray-900 capitalize"><?php echo htmlspecialchars($sale['livestock_name']); ?></h4>
                                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
                                    Pemasok / Peternak: <strong class="text-gray-700"><?php echo htmlspecialchars($sale['peternak_name']); ?></strong>
                                </p>
                            </div>
                            <div class="text-xs font-bold text-gray-500 mt-4 flex items-center gap-4">
                                <span class="bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-calculator text-brand-primary mr-1.5"></i>Qty: <?php echo $sale['qty']; ?> ekor</span>
                                <span class="bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100"><i class="fas fa-shopping-tag text-brand-primary mr-1.5"></i>Rp <?php echo number_format($sale['selling_price_snapshot'], 0, ',', '.'); ?> / ekor</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments Timeline / Ledger -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm space-y-6">
                    <h3 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <i class="fas fa-receipt text-brand-primary"></i> Histori Pembayaran & Ledger
                    </h3>

                    <?php if (empty($payments)): ?>
                        <div class="text-center py-10 text-gray-400 font-bold">
                            <i class="fas fa-wallet text-3xl mb-3 block text-gray-200"></i>
                            Belum ada rekaman bukti pembayaran masuk.
                        </div>
                    <?php else: ?>
                        <div class="relative border-l-2 border-gray-100 pl-6 ml-4 space-y-6">
                            <?php foreach ($payments as $pay): ?>
                                <div class="relative">
                                    <!-- Timeline Dot -->
                                    <span class="absolute -left-[33px] top-1.5 w-4 h-4 rounded-full border-2 border-white bg-brand-primary shadow-sm"></span>
                                    
                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2.5">
                                                <h4 class="text-xs font-black text-gray-800">#<?php echo htmlspecialchars($pay['payment_code']); ?></h4>
                                                <?php
                                                $payStatusBadge = [
                                                    'pending' => 'bg-amber-50 text-amber-600 border border-amber-200/50',
                                                    'verified' => 'bg-emerald-50 text-emerald-600 border border-emerald-200/50 font-bold',
                                                    'rejected' => 'bg-red-50 text-red-500 border border-red-200/50'
                                                ][$pay['payment_status']] ?? 'bg-gray-50 text-gray-400';
                                                
                                                $payStatusLabel = [
                                                    'pending' => 'Menunggu Verifikasi',
                                                    'verified' => 'Terverifikasi / Masuk',
                                                    'rejected' => 'Ditolak / Gagal'
                                                ][$pay['payment_status']] ?? $pay['payment_status'];
                                                ?>
                                                <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded <?php echo $payStatusBadge; ?>">
                                                    <?php echo $payStatusLabel; ?>
                                                </span>
                                            </div>
                                            <p class="text-base font-black text-gray-900">
                                                Rp <?php echo number_format($pay['payment_amount'], 0, ',', '.'); ?>
                                            </p>
                                            <div class="text-[9px] text-gray-400 font-bold uppercase space-x-2">
                                                <span>Metode: <?php echo htmlspecialchars($pay['payment_method']); ?></span>
                                                <span>&bull;</span>
                                                <span>Waktu: <?php echo date('d M Y, H:i', strtotime($pay['payment_date'])); ?></span>
                                            </div>
                                            <p class="text-xs text-gray-500 italic mt-1">
                                                "<?php echo htmlspecialchars($pay['payment_note'] ?: '-'); ?>"
                                            </p>
                                        </div>

                                        <!-- Proof Button -->
                                        <?php if ($pay['payment_proof']): ?>
                                            <div class="shrink-0 pt-2 md:pt-0">
                                                <button onclick="openImageModal('<?php echo htmlspecialchars($pay['payment_proof']); ?>')"
                                                    class="inline-flex px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-brand-primary font-black text-[10px] uppercase tracking-widest shadow-sm gap-2 items-center transition-all">
                                                    <i class="fas fa-image text-xs"></i> Bukti Transfer
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add New Payment Form (Installment / Settlement) -->
                <?php if ($remaining > 0): ?>
                    <div class="no-print bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <i class="fas fa-paper-plane text-brand-primary"></i> Unggah Bukti Cicilan / Pelunasan
                        </h3>
                        
                        <!-- Bank Destination Info -->
                        <div class="bg-brand-light/20 border border-brand-primary/10 p-6 rounded-2xl space-y-4">
                            <p class="text-[10px] text-brand-primary font-black uppercase tracking-widest">Tujuan Transfer Bank LTP</p>
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" class="h-6 w-auto">
                                <div>
                                    <p class="text-base font-black text-gray-900">1341699695</p>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">A/N Shohibudin</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 leading-relaxed font-semibold">
                                *Sisa tagihan transaksi ini adalah <strong class="text-brand-primary text-sm font-black">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></strong>. Silakan transfer nominal cicilan/pelunasan Anda ke rekening di atas dan konfirmasi di bawah.
                            </div>
                        </div>

                        <!-- Installment Payment Form -->
                        <form action="/lautan-ternak-pantura/sales/record_payment" method="POST" enctype="multipart/form-data" onsubmit="return validatePaymentForm(event)" class="space-y-5">
                            <input type="hidden" name="sale_id" value="<?php echo $sale['id']; ?>">
                            
                            <div class="bg-brand-light/10 p-4 rounded-xl text-xs font-bold text-brand-primary flex justify-between items-center">
                                <span>Maksimal Setoran:</span>
                                <span id="max_allowed_amt" data-val="<?php echo $remaining; ?>" class="font-black">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Jumlah Nominal Cicilan / Transfer (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                                    <input type="text" id="payment_amount" name="payment_amount" required oninput="formatCurrency(this)" placeholder="Contoh: 1.500.000"
                                        class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white text-sm font-black text-gray-700 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Metode Pembayaran</label>
                                <div class="relative">
                                    <select name="payment_method" class="w-full pl-4 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                                        <option value="Transfer Bank Manual">Transfer Bank Manual</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Catatan Setoran</label>
                                <textarea name="payment_note" rows="2" placeholder="Contoh: Pembayaran cicilan kedua..."
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Unggah Foto Bukti Transfer</label>
                                <div id="drop-zone" class="relative group cursor-pointer">
                                    <input type="file" name="payment_proof" id="proof-input" accept="image/jpeg,image/png,image/webp" required class="hidden">
                                    <div id="upload-ui" class="w-full py-10 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-3 group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5 transition-all">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-brand-primary transition-all">
                                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all" id="filename-text">Klik atau tarik gambar bukti transfer ke sini</p>
                                        <p class="text-[9px] font-bold text-gray-300">Format: JPG, JPEG, PNG, WEBP (Maksimal 2MB)</p>
                                    </div>
                                    <div id="preview-container" class="hidden absolute inset-0 rounded-3xl overflow-hidden bg-white">
                                        <img id="image-preview" class="w-full h-full object-cover">
                                        <button type="button" onclick="resetImage(event)" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-black/40 backdrop-blur-md text-white flex items-center justify-center hover:bg-black/60 transition-all">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-3">
                                <i class="fas fa-check-circle"></i> Kirim Konfirmasi Pembayaran
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: Invoice Financial Summary (1 Column) -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest border-b border-gray-50 pb-4">Ringkasan Invoice</h3>
                    
                    <div class="space-y-4 text-xs font-bold text-gray-500">
                        <div class="flex justify-between items-center">
                            <span>Tipe Checkout</span>
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded <?php echo ($sale['payment_type'] === 'dp') ? 'bg-amber-50 text-amber-600 border border-amber-200/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-200/50'; ?>">
                                <?php echo ($sale['payment_type'] === 'dp') ? 'DOWN PAYMENT' : 'LUNAS'; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Status Bayar</span>
                            <?php
                            $pClasses = [
                                'unpaid' => 'bg-red-50 text-red-600 border border-red-200/50',
                                'partial' => 'bg-amber-50 text-amber-600 border border-amber-200/50',
                                'paid' => 'bg-emerald-50 text-emerald-600 border border-emerald-200/50 font-bold'
                            ][$sale['payment_status']] ?? 'bg-gray-50 text-gray-400';
                            
                            $pLabel = [
                                'unpaid' => 'Belum Bayar',
                                'partial' => 'DP / Sebagian',
                                'paid' => 'Lunas'
                            ][$sale['payment_status']] ?? $sale['payment_status'];
                            ?>
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded <?php echo $pClasses; ?>">
                                <?php echo $pLabel; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-50 pt-4">
                            <span>Harga Hewan</span>
                            <span class="text-gray-800">Rp <?php echo number_format($sale['selling_price_snapshot'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Kuantitas</span>
                            <span class="text-gray-800"><?php echo $sale['qty']; ?> ekor</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Waktu Order</span>
                            <span class="text-xs text-gray-400 font-bold"><?php echo date('d M Y', strtotime($sale['created_at'])); ?></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Tagihan</span>
                            <span class="text-sm font-black text-gray-800">Rp <?php echo number_format($sale['total_price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase">Total Terbayar</span>
                            <span class="text-sm font-black text-emerald-600">Rp <?php echo number_format($totalPaid, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-t border-dashed border-gray-100 pt-3">
                            <span class="text-sm font-black text-gray-900">Sisa Tagihan</span>
                            <span class="text-lg font-black text-brand-primary">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Notes Card if exists -->
                <?php if ($sale['notes']): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-3">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest font-bold">Catatan Pembeli</h4>
                        <p class="text-sm font-semibold text-gray-600 leading-relaxed italic bg-gray-50 p-4 rounded-2xl border border-gray-100/50">"<?php echo htmlspecialchars($sale['notes']); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Image Modal View Overlay -->
<div id="image-modal-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0" onclick="closeImageModal()">
    <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center animate-fade-in" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors bg-white/10 w-10 h-10 rounded-full flex items-center justify-center">
            <i class="fas fa-times text-xl"></i>
        </button>
        <img id="image-modal-preview" src="" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl border border-white/15">
    </div>
</div>

<script>
    const proofInput = document.getElementById('proof-input');
    const dropZone = document.getElementById('drop-zone');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const filenameText = document.getElementById('filename-text');

    if (dropZone) {
        dropZone.addEventListener('click', () => proofInput.click());

        // Drag & Drop events
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.add('border-brand-primary');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-brand-primary');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                proofInput.files = files;
                handleImageFile(files[0]);
            }
        });

        proofInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleImageFile(this.files[0]);
            }
        });
    }

    function handleImageFile(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
            filenameText.innerText = file.name;
        }
    }

    function resetImage(e) {
        e.stopPropagation();
        proofInput.value = '';
        previewContainer.classList.add('hidden');
        filenameText.innerText = 'Klik atau tarik gambar bukti transfer ke sini';
    }

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function validatePaymentForm(event) {
        const amtInput = document.getElementById('payment_amount');
        const rawAmt = parseFloat(amtInput.value.replace(/\D/g, "")) || 0;
        const maxAmt = parseFloat(document.getElementById('max_allowed_amt').getAttribute('data-val')) || 0;

        if (rawAmt <= 0) {
            showToast('Nominal cicilan pembayaran harus lebih dari 0!', 'error');
            event.preventDefault();
            return false;
        }

        if (rawAmt > maxAmt) {
            showToast('Nominal cicilan Rp ' + new Intl.NumberFormat("id-ID").format(rawAmt) + ' melebihi sisa tagihan tagihan Rp ' + new Intl.NumberFormat("id-ID").format(maxAmt) + '!', 'error');
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
