<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" id="checkout-flow-container">

        <!-- Header -->
        <div class="mb-8 flex items-center gap-4">
            <a href="/lautan-ternak-pantura/marketplace"
                class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Checkout <span class="text-brand-primary">Hewan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Lengkapi data diri dan konfirmasi pembayaran Anda</p>
            </div>
        </div>

        <!-- Notification Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-5 text-red-700 font-bold flex items-center gap-3">
                <i class="fas fa-circle-xmark text-lg shrink-0"></i>
                <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="/lautan-ternak-pantura/sales/processCheckout" method="POST" enctype="multipart/form-data" onsubmit="return handleCheckoutSubmit(event)">
            <input type="hidden" name="livestock_id" value="<?php echo $livestock['id']; ?>">
            <input type="hidden" name="qty" id="hidden-qty" value="1">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Panel: Data Diri & Order Form (7 Columns of 12) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Animal Card -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col sm:flex-row gap-6">
                        <div class="w-full sm:w-36 h-36 rounded-3xl overflow-hidden bg-gray-100 border border-gray-100 shrink-0 shadow-inner">
                            <img src="<?php echo $livestock['image_url'] ?: '/lautan-ternak-pantura/assets/images/default_animal.jpg'; ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow flex flex-col justify-between py-1">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 capitalize leading-tight">
                                    <?php echo htmlspecialchars($livestock['breed'] ?? $livestock['name']); ?>
                                </h2>
                                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
                                    Kode: #<?php echo htmlspecialchars($livestock['livestock_code']); ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-6 mt-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Berat</p>
                                    <p class="text-sm font-bold text-gray-700 mt-0.5">
                                        <i class="fas fa-weight-hanging mr-1.5 text-brand-primary"></i><?php echo $livestock['weight']; ?> kg
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Jenis Kelamin</p>
                                    <p class="text-sm font-bold text-gray-700 mt-0.5">
                                        <i class="fas fa-venus-mars mr-1.5 text-brand-primary"></i><?php echo $livestock['gender'] === 'male' ? 'Jantan' : 'Betina'; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Umur</p>
                                    <p class="text-sm font-bold text-gray-700 mt-0.5">
                                        <i class="fas fa-birthday-cake mr-1.5 text-brand-primary"></i><?php echo $livestock['age']; ?> Bulan
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form: Personal Details -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <i class="fas fa-user-edit text-brand-primary"></i> Data Penerima & Penyaluran
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="space-y-2">
                                 <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Lengkap Penerima <span class="text-red-500">*</span></label>
                                 <input type="text" name="customer_name" required <?php echo !isset($_SESSION['user_id']) ? '' : 'readonly'; ?>
                                     value="<?php echo htmlspecialchars($userData['full_name'] ?? $userData['name'] ?? $_SESSION['full_name'] ?? ''); ?>"
                                     class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm <?php echo !isset($_SESSION['user_id']) ? 'text-gray-700' : 'text-gray-500 cursor-not-allowed'; ?>">
                            </div>

                            <!-- Phone / WhatsApp -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nomor WhatsApp Aktif <span class="text-red-500">*</span></label>
                                <input type="tel" name="customer_phone" required
                                    value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                    placeholder="Contoh: 081234567890"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm text-gray-700">
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="space-y-2 pt-2 border-t border-gray-50">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas Pembelian</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="changeQty(-1)"
                                    class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center font-black text-gray-600 hover:bg-gray-100 transition-all shadow-sm">-</button>
                                <input type="number" id="qty-input" value="1" min="1" max="<?php echo $livestock['stock']; ?>" readonly
                                    class="w-16 h-12 bg-gray-50 border border-transparent rounded-2xl outline-none text-center font-black text-lg">
                                <button type="button" onclick="changeQty(1)"
                                    class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center font-black text-gray-600 hover:bg-gray-100 transition-all shadow-sm">+</button>
                                <span class="text-xs font-bold text-gray-400 ml-2">Stok tersedia: <?php echo $livestock['stock']; ?> ekor</span>
                            </div>
                        </div>

                        <!-- Tipe Pembayaran (Lunas / DP) -->
                        <div class="space-y-3 pt-4 border-t border-gray-50">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Skema Pembayaran</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_type" value="lunas" checked
                                        class="sr-only peer" onchange="togglePaymentType('lunas')">
                                    <div class="w-full p-5 bg-gray-50 border-2 border-transparent peer-checked:border-brand-primary peer-checked:bg-brand-primary/5 rounded-2xl font-black flex flex-col items-center justify-center text-center gap-1.5 text-gray-700 peer-checked:text-brand-primary transition-all shadow-sm">
                                        <i class="fas fa-check-double text-lg mb-1"></i>
                                        <span class="text-sm">Bayar Lunas</span>
                                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Langsung Melunasi Tagihan</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_type" value="dp"
                                        class="sr-only peer" onchange="togglePaymentType('dp')">
                                    <div class="w-full p-5 bg-gray-50 border-2 border-transparent peer-checked:border-brand-primary peer-checked:bg-brand-primary/5 rounded-2xl font-black flex flex-col items-center justify-center text-center gap-1.5 text-gray-700 peer-checked:text-brand-primary transition-all shadow-sm">
                                        <i class="fas fa-wallet text-lg mb-1"></i>
                                        <span class="text-sm">Uang Muka (DP)</span>
                                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Pembayaran Bertahap / Cicil</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Custom DP Amount Input -->
                        <div class="space-y-2 hidden" id="dp-amount-section">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nominal Uang Muka / DP (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-black text-sm">Rp</span>
                                <input type="text" id="payment_amount_input" name="payment_amount" oninput="formatCurrency(this)" placeholder="Masukkan nominal DP..."
                                    class="w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-black text-lg text-gray-700">
                            </div>
                            <p class="text-[9px] text-gray-400 font-bold px-1">*DP minimal harus lebih dari 0 dan kurang dari total tagihan.</p>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2 pt-2 border-t border-gray-50">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Alamat Penyaluran & Catatan Khusus</label>
                            <textarea name="notes" placeholder="Masukkan alamat pengiriman lengkap, instruksi penyaluran qurban, atau catatan tambahan lain..."
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[90px]"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Proof of Payment Upload Box -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-brand-primary"></i> Konfirmasi Pembayaran Awal
                        </h3>

                        <!-- Destination Bank Accounts -->
                        <div class="bg-brand-light/20 border border-brand-primary/10 p-6 rounded-2xl space-y-4">
                            <p class="text-[10px] text-brand-primary font-black uppercase tracking-widest">Tujuan Transfer Bank LTP</p>
                            <div class="flex items-center gap-4">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" class="h-6 w-auto">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-base font-black text-gray-900" id="bank-number">1341699695</p>
                                        <button type="button" onclick="copyBankNumber()"
                                            class="text-xs text-brand-primary font-black hover:underline focus:outline-none"><i class="far fa-copy"></i> Salin</button>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">A/N Shohibudin</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 leading-relaxed font-semibold">
                                *Silakan transfer pembayaran awal Anda sebesar <strong class="text-brand-primary text-sm font-black" id="total-transfer-text">Rp <?php echo number_format($livestock['selling_price'], 0, ',', '.'); ?></strong> ke rekening di atas, lalu lampirkan gambar bukti transfernya di bawah.
                            </div>
                        </div>

                        <!-- File upload dropzone -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Unggah Foto Bukti Transfer <span class="text-red-500">*</span></label>
                            <div id="drop-zone" class="relative group cursor-pointer">
                                <input type="file" name="payment_proof" id="proof-input" accept="image/jpeg,image/png,image/webp" required class="hidden">
                                <div id="upload-ui" class="w-full py-12 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-3 group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5 transition-all">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-brand-primary transition-all">
                                        <i class="fas fa-cloud-upload-alt text-xl"></i>
                                    </div>
                                    <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all" id="filename-text">Klik atau tarik gambar bukti transfer ke sini</p>
                                    <p class="text-[9px] font-bold text-gray-300">Format: JPG, JPEG, PNG, WEBP (Maksimal 2MB)</p>
                                </div>
                                <div id="preview-container" class="hidden absolute inset-0 rounded-3xl overflow-hidden bg-white border border-gray-100">
                                    <img id="image-preview" class="w-full h-full object-cover">
                                    <button type="button" onclick="resetImage(event)"
                                        class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-black/40 backdrop-blur-md text-white flex items-center justify-center hover:bg-black/60 transition-all">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Summary & Button (5 Columns of 12) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6 sticky top-24">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Ringkasan Tagihan</h3>

                        <div class="space-y-4 border-b border-gray-100 pb-6 text-sm font-bold text-gray-500">
                            <div class="flex justify-between items-center">
                                <span>Harga Hewan</span>
                                <span class="font-extrabold text-gray-800">Rp <?php echo number_format($livestock['selling_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Kuantitas</span>
                                <span id="qty-summary" class="font-extrabold text-gray-800">1 ekor</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Skema Bayar</span>
                                <span id="payment-scheme-summary" class="text-brand-primary uppercase font-black text-xs bg-brand-light/30 px-3 py-1 rounded-full">LUNAS</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pembelian</span>
                                <span class="text-base font-black text-gray-900" id="total-summary">Rp <?php echo number_format($livestock['selling_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between items-center border-t border-dashed border-gray-100 pt-4">
                                <span class="text-sm font-black text-gray-950">Setoran Awal (Transfer)</span>
                                <span class="font-black text-brand-primary" id="total-transfer-summary">Rp <?php echo number_format($livestock['selling_price'], 0, ',', '.'); ?></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                            <span id="btn-spinner" class="hidden"><i class="fas fa-circle-notch animate-spin mr-2"></i></span>
                            <i class="fas fa-shopping-bag"></i> Buat Pesanan & Bayar Awal
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    const priceUnit = <?php echo floatval($livestock['selling_price']); ?>;
    const maxStock = <?php echo intval($livestock['stock']); ?>;

    const qtyInput = document.getElementById('qty-input');
    const hiddenQty = document.getElementById('hidden-qty');
    const qtySummary = document.getElementById('qty-summary');
    const totalSummary = document.getElementById('total-summary');
    const totalTransferSummary = document.getElementById('total-transfer-summary');
    const totalTransferText = document.getElementById('total-transfer-text');
    const paymentSchemeSummary = document.getElementById('payment-scheme-summary');
    
    const dpAmountSection = document.getElementById('dp-amount-section');
    const dpAmountInput = document.getElementById('payment_amount_input');

    const proofInput = document.getElementById('proof-input');
    const dropZone = document.getElementById('drop-zone');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const filenameText = document.getElementById('filename-text');

    let selectedScheme = 'lunas';

    function changeQty(amount) {
        let current = parseInt(qtyInput.value);
        let next = current + amount;
        if (next >= 1 && next <= maxStock) {
            qtyInput.value = next;
            hiddenQty.value = next;
            qtySummary.innerText = next + ' ekor';
            
            recalculateSummary();
        }
    }

    function togglePaymentType(scheme) {
        selectedScheme = scheme;
        if (scheme === 'dp') {
            dpAmountSection.classList.remove('hidden');
            dpAmountInput.setAttribute('required', 'required');
            paymentSchemeSummary.innerText = 'DOWN PAYMENT (DP)';
            paymentSchemeSummary.className = 'text-amber-600 uppercase font-black text-xs';
            
            // prefill minimum default DP (e.g. 30% of price or 2,000,000)
            const qty = parseInt(qtyInput.value);
            const total = priceUnit * qty;
            const defaultDp = Math.min(2000000, total - 100000);
            dpAmountInput.value = new Intl.NumberFormat("id-ID").format(defaultDp);
        } else {
            dpAmountSection.classList.add('hidden');
            dpAmountInput.removeAttribute('required');
            paymentSchemeSummary.innerText = 'LUNAS';
            paymentSchemeSummary.className = 'text-emerald-600 uppercase font-black text-xs';
        }
        recalculateSummary();
    }

    function recalculateSummary() {
        const qty = parseInt(qtyInput.value);
        const total = priceUnit * qty;
        totalSummary.innerText = 'Rp ' + total.toLocaleString('id-ID');

        if (selectedScheme === 'lunas') {
            totalTransferSummary.innerText = 'Rp ' + total.toLocaleString('id-ID');
            totalTransferText.innerText = 'Rp ' + total.toLocaleString('id-ID');
        } else {
            // Update Transfer Summary dynamically if DP is edited
            updateDpTransferValue();
        }
    }

    function updateDpTransferValue() {
        if (selectedScheme === 'dp') {
            const rawDp = parseFloat(dpAmountInput.value.replace(/\D/g, "")) || 0;
            totalTransferSummary.innerText = 'Rp ' + rawDp.toLocaleString('id-ID');
            totalTransferText.innerText = 'Rp ' + rawDp.toLocaleString('id-ID');
        }
    }

    dpAmountInput.addEventListener('input', updateDpTransferValue);

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    if (dropZone) {
        dropZone.addEventListener('click', () => proofInput.click());

        // Drag & Drop handlers
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
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
        filenameText.innerText = file.name;
    }

    function resetImage(e) {
        e.stopPropagation();
        proofInput.value = '';
        previewContainer.classList.add('hidden');
        filenameText.innerText = 'Klik atau tarik gambar bukti transfer ke sini';
    }

    function copyBankNumber() {
        const bankNum = document.getElementById('bank-number').innerText;
        navigator.clipboard.writeText(bankNum.replace(/\s/g, '')).then(() => {
            showToast('Nomor rekening berhasil disalin!', 'success');
        });
    }

    function handleCheckoutSubmit(event) {
        const qtyVal = parseInt(qtyInput.value);
        const totalPrice = priceUnit * qtyVal;

        if (selectedScheme === 'dp') {
            const rawDp = parseFloat(dpAmountInput.value.replace(/\D/g, "")) || 0;
            if (rawDp <= 0) {
                showToast('Nominal DP harus diisi dan lebih besar dari 0!', 'error');
                event.preventDefault();
                return false;
            }
            if (rawDp >= totalPrice) {
                showToast('Nominal DP tidak boleh melebihi atau sama dengan total harga! Silakan gunakan tipe bayar Lunas.', 'error');
                event.preventDefault();
                return false;
            }
            // Set raw numeric values
            dpAmountInput.value = rawDp;
        } else {
            // Lock Lunas payment amount to total price
            dpAmountInput.value = totalPrice;
        }

        // Show loading spinner
        document.getElementById('btn-spinner').classList.remove('hidden');
        return true;
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>