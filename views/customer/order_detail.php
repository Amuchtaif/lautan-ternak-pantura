<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex items-center gap-4">
            <a href="/lautan-ternak-pantura/order/orders" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Detail <span class="text-brand-primary">Pesanan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Invoice: #<?php echo $order['order_code']; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Invoice Details & Upload Area -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Order Status Card -->
                <?php 
                    $statusClasses = [
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'waiting_payment' => 'bg-orange-50 text-orange-700 border-orange-100',
                        'payment_review' => 'bg-blue-50 text-blue-700 border-blue-100',
                        'paid' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                        'processing' => 'bg-purple-50 text-purple-700 border-purple-100',
                        'delivered' => 'bg-teal-50 text-teal-700 border-teal-100',
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'cancelled' => 'bg-red-50 text-red-600 border-red-100'
                    ][$order['status']] ?? 'bg-gray-50 text-gray-500 border-gray-100';

                    $statusLabels = [
                        'pending' => 'Menunggu Persetujuan',
                        'waiting_payment' => 'Menunggu Pembayaran Transfer',
                        'payment_review' => 'Bukti Pembayaran Sedang Diverifikasi Admin',
                        'paid' => 'Pembayaran Berhasil / Lunas',
                        'processing' => 'Hewan Sedang Dipersiapkan',
                        'delivered' => 'Hewan Sedang Dikirim',
                        'completed' => 'Pesanan Selesai / Diterima',
                        'cancelled' => 'Pesanan Dibatalkan'
                    ][$order['status']] ?? $order['status'];
                ?>
                <div class="rounded-3xl border p-8 flex items-center gap-5 <?php echo $statusClasses; ?>">
                    <div class="w-12 h-12 rounded-2xl bg-white/80 backdrop-blur-md flex items-center justify-center text-xl shrink-0">
                        <i class="fas <?php echo $order['status'] === 'completed' ? 'fa-check-circle' : ($order['status'] === 'waiting_payment' ? 'fa-wallet' : 'fa-info-circle'); ?>"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60">Status Pesanan</p>
                        <p class="text-base font-black mt-0.5"><?php echo $statusLabels; ?></p>
                    </div>
                </div>

                <!-- Animal Detail Info Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Detail Hewan Ternak</h3>
                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="w-full sm:w-32 h-32 rounded-3xl overflow-hidden bg-gray-100 border border-gray-100 shrink-0">
                            <img src="<?php echo $order['livestock_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow flex flex-col justify-between py-1">
                            <div>
                                <h4 class="text-lg font-black text-gray-900 capitalize mt-3"><?php echo htmlspecialchars($order['livestock_name']); ?></h4>
                                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">Breed: <?php echo htmlspecialchars($order['livestock_breed']); ?> &middot; Kode: <?php echo htmlspecialchars($order['livestock_code']); ?></p>
                            </div>
                            <div class="text-xs font-bold text-gray-500 mt-2">
                                <span class="mr-4"><i class="fas fa-weight-hanging text-brand-primary mr-1.5"></i><?php echo $order['livestock_weight']; ?> kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Upload Box -->
                <?php if ($order['status'] === 'waiting_payment'): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Lakukan Transfer Pembayaran</h3>
                        
                        <!-- Bank Destination Info -->
                        <div class="bg-brand-light/30 border border-brand-primary/10 p-6 rounded-3xl space-y-4">
                            <p class="text-[10px] text-brand-primary font-black uppercase tracking-widest">Tujuan Transfer Bank</p>
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" class="h-6 w-auto">
                                <div>
                                    <p class="text-base font-black text-gray-900">8610 9928 11</p>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">A/N LAUTAN TERNAK PANTURA</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 leading-relaxed font-semibold">
                                *Silakan transfer nominal pas sebesar <strong class="text-brand-primary text-sm font-black">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong> ke rekening di atas, kemudian unggah foto bukti transfer di bawah.
                            </div>
                        </div>

                        <!-- File upload form -->
                        <form id="upload-proof-form" class="space-y-6">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Unggah Bukti Transfer</label>
                                <div id="drop-zone" class="relative group cursor-pointer">
                                    <input type="file" name="proof" id="proof-input" accept="image/*" required class="hidden">
                                    <div id="upload-ui" class="w-full py-10 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-3 group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5 transition-all">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-brand-primary transition-all">
                                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all" id="filename-text">Klik atau seret bukti transfer ke sini</p>
                                        <p class="text-[10px] font-bold text-gray-300">Format: JPG, PNG, WEBP (Maks 2MB)</p>
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
                                <i class="fas fa-paper-plane"></i> Kirim Bukti Transfer
                            </button>
                        </form>
                    </div>
                <?php elseif ($payment): ?>
                    <!-- Payment Proof Details -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Bukti Pembayaran Terunggah</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Metode Pembayaran</p>
                                    <p class="text-sm font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Jumlah Ditransfer</p>
                                    <p class="text-base font-black text-brand-primary mt-1">Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Tanggal Upload</p>
                                    <p class="text-xs font-bold text-gray-600 mt-1"><?php echo date('d M Y, H:i', strtotime($payment['created_at'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Status Verifikasi</p>
                                    <span class="inline-block mt-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php 
                                        echo $payment['payment_status'] === 'verified' ? 'bg-emerald-50 text-emerald-600' : ($payment['payment_status'] === 'rejected' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600');
                                    ?>">
                                        <?php echo $payment['payment_status'] === 'verified' ? 'Diterima' : ($payment['payment_status'] === 'rejected' ? 'Ditolak' : 'Pending'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="w-full h-52 rounded-3xl overflow-hidden border border-gray-100 bg-gray-50 shadow-sm relative group cursor-zoom-in">
                                <img src="<?php echo $payment['payment_proof']; ?>" class="w-full h-full object-cover">
                                <a href="<?php echo $payment['payment_proof']; ?>" target="_blank" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white font-black text-xs uppercase tracking-widest">
                                    <i class="fas fa-search-plus mr-2"></i> Perbesar Bukti
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right 1 Col: Summary & Info -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Ringkasan Invoice</h3>
                    
                    <div class="space-y-4 border-b border-gray-100 pb-6 text-sm font-bold text-gray-500">
                        <div class="flex justify-between items-center">
                            <span>Harga snapshot</span>
                            <span>Rp <?php echo number_format($order['livestock_price_snapshot'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Kuantitas</span>
                            <span><?php echo $order['qty']; ?> ekor</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Tanggal Order</span>
                            <span class="text-xs text-gray-400 font-bold"><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-900">Total Tagihan</span>
                        <span class="text-xl font-black text-brand-primary">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <!-- Notes Card if exists -->
                <?php if ($order['notes']): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-4">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Catatan Anda</h4>
                        <p class="text-sm font-bold text-gray-700 leading-relaxed italic bg-gray-50 p-4 rounded-2xl border border-gray-100/50">"<?php echo htmlspecialchars($order['notes']); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Status Overlay Modal -->
<div id="status-modal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl w-full max-w-sm p-10 overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0" id="status-modal-content">
        <div class="text-center">
            <div id="modal-icon" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl"></div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight" id="modal-title"></h3>
            <p class="text-sm text-gray-400 font-bold mt-4" id="modal-desc"></p>
            <button id="modal-btn" class="mt-8 w-full py-4 rounded-2xl font-black text-sm transition-all shadow-md"></button>
        </div>
    </div>
</div>

<script>
    const proofInput = document.getElementById('proof-input');
    const dropZone = document.getElementById('drop-zone');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const filenameText = document.getElementById('filename-text');
    const uploadForm = document.getElementById('upload-proof-form');

    if (dropZone) {
        dropZone.addEventListener('click', () => proofInput.click());

        proofInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(this.files[0]);
                filenameText.innerText = this.files[0].name;
            }
        });
    }

    function resetImage(e) {
        e.stopPropagation();
        proofInput.value = '';
        previewContainer.classList.add('hidden');
        filenameText.innerText = 'Klik atau seret bukti transfer ke sini';
    }

    function showStatusModal(type, title, desc, btnText, btnAction) {
        const overlay = document.getElementById('status-modal');
        const content = document.getElementById('status-modal-content');
        const icon = document.getElementById('modal-icon');
        const btn = document.getElementById('modal-btn');
        
        document.getElementById('modal-title').innerText = title;
        document.getElementById('modal-desc').innerText = desc;
        btn.innerText = btnText;
        
        if (type === 'success') {
            icon.className = "w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-6 text-3xl";
            icon.innerHTML = '<i class="fas fa-check"></i>';
            btn.className = "mt-8 w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-sm transition-all shadow-md";
        } else {
            icon.className = "w-20 h-20 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-6 text-3xl";
            icon.innerHTML = '<i class="fas fa-times"></i>';
            btn.className = "mt-8 w-full bg-red-500 hover:bg-red-600 text-white py-4 rounded-2xl font-black text-sm transition-all shadow-md";
        }

        btn.onclick = btnAction;

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(() => {
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-90', 'opacity-0');
        }, 10);
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/lautan-ternak-pantura/api/orders/upload_payment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatusModal(
                        'success', 
                        'Bukti Terunggah!', 
                        'Bukti transfer Anda berhasil dikirim ke server. Mohon tunggu proses verifikasi oleh tim Admin kami.', 
                        'Segarkan Halaman', 
                        () => { window.location.reload(); }
                    );
                } else {
                    showStatusModal(
                        'error', 
                        'Gagal Mengunggah', 
                        data.message || 'Gagal menyimpan bukti pembayaran.', 
                        'Coba Lagi', 
                        () => {
                            const overlay = document.getElementById('status-modal');
                            overlay.classList.replace('flex', 'hidden');
                        }
                    );
                }
            })
            .catch(err => {
                showStatusModal('error', 'Koneksi Gagal', 'Gagal menghubungi server.', 'Tutup', () => {
                    const overlay = document.getElementById('status-modal');
                    overlay.classList.replace('flex', 'hidden');
                });
            });
        });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
