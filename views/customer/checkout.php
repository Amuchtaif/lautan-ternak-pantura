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
            <a href="/lautan-ternak-pantura/livestock/detail/<?php echo $livestock['id']; ?>"
                class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Checkout <span
                        class="text-brand-primary">Hewan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Lengkapi data diri dan pilih
                    metode pembayaran</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Panel: Data Diri & Order Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Animal Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col sm:flex-row gap-6">
                    <div
                        class="w-full sm:w-40 h-40 rounded-3xl overflow-hidden bg-gray-100 border border-gray-100 shrink-0">
                        <img src="<?php echo $livestock['image'] ?: ($livestock['image_url'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'); ?>"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="flex-grow flex flex-col justify-between">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 capitalize mt-0 leading-tight">
                                <?php echo htmlspecialchars($livestock['name'] ?? $livestock['type']); ?>
                            </h2>
                            <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">
                                <?php echo htmlspecialchars($livestock['breed']); ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-6 mt-4 sm:mt-0">
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Berat</p>
                                <p class="text-sm font-bold text-gray-700 mt-0.5"><i
                                        class="fas fa-weight-hanging mr-2 text-brand-primary"></i><?php echo $livestock['weight']; ?>
                                    kg</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Jenis Kelamin
                                </p>
                                <p class="text-sm font-bold text-gray-700 mt-0.5"><i
                                        class="fas fa-venus-mars mr-2 text-brand-primary"></i><?php echo $livestock['gender'] === 'male' ? 'Jantan' : 'Betina'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Form: Personal Details & Proof -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2"><i
                            class="fas fa-user-edit text-brand-primary"></i> Formulir Data Diri</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama
                                Penerima / Pekurban <span class="text-red-500">*</span></label>
                            <input type="text" id="name-input" required
                                value="<?php echo htmlspecialchars($userData['name'] ?? $_SESSION['user_name'] ?? ''); ?>"
                                placeholder="Nama Lengkap"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>

                        <!-- Phone / WhatsApp -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nomor
                                WhatsApp / HP <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone-input" required
                                value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Alamat
                            Pengiriman / Penyaluran <span class="text-red-500">*</span></label>
                        <textarea id="address-input" required placeholder="Masukkan alamat lengkap pengantaran hewan"
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[100px]"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2 border-t border-gray-50">
                        <!-- Quantity Selector -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah
                                (Qty)</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="changeQty(-1)"
                                    class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center font-black text-gray-600 hover:bg-gray-100 transition-all">-</button>
                                <input type="number" id="qty-input" value="1" min="1"
                                    max="<?php echo $livestock['stock']; ?>" readonly
                                    class="w-16 h-12 bg-gray-50 border border-transparent rounded-2xl outline-none text-center font-black text-lg">
                                <button type="button" onclick="changeQty(1)"
                                    class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center font-black text-gray-600 hover:bg-gray-100 transition-all">+</button>
                            </div>
                            <p class="text-[10px] font-bold text-gray-400 mt-1">Stok tersedia:
                                <?php echo $livestock['stock']; ?> ekor
                            </p>
                        </div>

                        <!-- Payment Method Picker Grid -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Metode
                                Pembayaran</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method_opt" value="transfer" checked
                                        class="sr-only peer" onchange="togglePaymentMethod('transfer')">
                                    <div
                                        class="w-full h-12 bg-gray-50 border-2 border-transparent peer-checked:border-brand-primary peer-checked:bg-brand-primary/5 rounded-2xl font-black text-[11px] flex items-center justify-center gap-1.5 text-gray-700 peer-checked:text-brand-primary transition-all">
                                        <i class="fas fa-university text-xs"></i> Transfer Bank
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method_opt" value="cash" class="sr-only peer"
                                        onchange="togglePaymentMethod('cash')">
                                    <div
                                        class="w-full h-12 bg-gray-50 border-2 border-transparent peer-checked:border-brand-primary peer-checked:bg-brand-primary/5 rounded-2xl font-black text-[11px] flex items-center justify-center gap-1.5 text-gray-700 peer-checked:text-brand-primary transition-all">
                                        <i class="fas fa-money-bill-wave text-xs"></i> Bayar Cash
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan
                            Tambahan (Opsional)</label>
                        <textarea id="notes-input" placeholder="Masukkan Catatan Tambahan (Opsional)"
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[80px]"></textarea>
                    </div>
                </div>

                <!-- Proof of Payment Upload Block -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6"
                    id="payment-upload-section">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2"><i
                            class="fas fa-file-invoice-dollar text-brand-primary"></i> Bukti Pembayaran</h3>

                    <!-- Bank Accounts -->
                    <div class="bg-brand-light/30 border border-brand-primary/10 p-6 rounded-3xl space-y-4">
                        <p class="text-[10px] text-brand-primary font-black uppercase tracking-widest">Tujuan Transfer
                            Bank</p>
                        <div class="flex items-center gap-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                class="h-6 w-auto">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-base font-black text-gray-900" id="bank-number">8610 9928 11</p>
                                    <button type="button" onclick="copyBankNumber()"
                                        class="text-xs text-brand-primary font-black hover:underline focus:outline-none"><i
                                            class="far fa-copy"></i> Salin</button>
                                </div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">A/N LAUTAN TERNAK
                                    PANTURA</p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 leading-relaxed font-semibold">
                            *Harap transfer nominal tepat sebesar <strong class="text-brand-primary text-sm font-black"
                                id="total-transfer-text">Rp
                                <?php echo number_format($livestock['price'], 0, ',', '.'); ?></strong> ke rekening di
                            atas, lalu lampirkan foto struk bukti transfer Anda di bawah.
                        </div>
                    </div>

                    <!-- Drag and Drop Zone -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Unggah Bukti
                            Transfer <span class="text-red-500">*</span></label>
                        <div id="drop-zone" class="relative group cursor-pointer">
                            <input type="file" id="proof-input" accept="image/*" required class="hidden">
                            <div id="upload-ui"
                                class="w-full py-12 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-3 group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5 transition-all">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-brand-primary transition-all">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all"
                                    id="filename-text">Klik atau seret bukti transfer ke sini</p>
                                <p class="text-[10px] font-bold text-gray-300">Format: JPG, PNG, WEBP (Maks 2MB)</p>
                            </div>
                            <div id="preview-container"
                                class="hidden absolute inset-0 rounded-3xl overflow-hidden bg-white border border-gray-100">
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

            <!-- Right Panel: Summary & Button -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6 sticky top-24">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Ringkasan Pembayaran</h3>

                    <div class="space-y-4 border-b border-gray-100 pb-6">
                        <div class="flex justify-between items-center text-sm font-bold text-gray-500">
                            <span>Harga Satuan</span>
                            <span>Rp <?php echo number_format($livestock['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold text-gray-500">
                            <span>Kuantitas</span>
                            <span id="qty-summary">1 ekor</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-900">Total Tagihan</span>
                        <span class="text-xl font-black text-brand-primary" id="total-summary">Rp
                            <?php echo number_format($livestock['price'], 0, ',', '.'); ?></span>
                    </div>

                    <button type="button" onclick="handleCheckoutSubmit()"
                        class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                        <span id="btn-spinner" class="hidden"><i
                                class="fas fa-circle-notch animate-spin mr-2"></i></span>
                        <i class="fas fa-shopping-bag"></i> Buat Pesanan Sekarang
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Status Overlay Modal for Error Handling -->
<div id="status-modal"
    class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl w-full max-w-sm p-10 overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0"
        id="status-modal-content">
        <div class="text-center">
            <div id="modal-icon" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight" id="modal-title"></h3>
            <p class="text-sm text-gray-400 font-bold mt-4" id="modal-desc"></p>
            <button id="modal-btn"
                class="mt-8 w-full py-4 rounded-2xl font-black text-sm transition-all shadow-md"></button>
        </div>
    </div>
</div>

<script>
    const priceUnit = <?php echo floatval($livestock['price']); ?>;
    const maxStock = <?php echo intval($livestock['stock']); ?>;

    const qtyInput = document.getElementById('qty-input');
    const qtySummary = document.getElementById('qty-summary');
    const totalSummary = document.getElementById('total-summary');
    const totalTransferText = document.getElementById('total-transfer-text');

    const proofInput = document.getElementById('proof-input');
    const dropZone = document.getElementById('drop-zone');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const filenameText = document.getElementById('filename-text');
    const paymentUploadSection = document.getElementById('payment-upload-section');

    let currentPaymentMethod = 'transfer'; // default

    function changeQty(amount) {
        let current = parseInt(qtyInput.value);
        let next = current + amount;
        if (next >= 1 && next <= maxStock) {
            qtyInput.value = next;
            qtySummary.innerText = next + ' ekor';
            let total = priceUnit * next;
            totalSummary.innerText = 'Rp ' + total.toLocaleString('id-ID');
            totalTransferText.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    }

    function togglePaymentMethod(method) {
        currentPaymentMethod = method;
        if (method === 'transfer') {
            paymentUploadSection.classList.remove('hidden');
            proofInput.setAttribute('required', 'required');
        } else {
            paymentUploadSection.classList.add('hidden');
            proofInput.removeAttribute('required');
        }
    }

    if (dropZone) {
        dropZone.addEventListener('click', () => proofInput.click());

        // Drag events
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.add('opacity-70');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropZone.classList.remove('opacity-70');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                proofInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        proofInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handleFileSelect(this.files[0]);
            }
        });
    }

    function handleFileSelect(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
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
        filenameText.innerText = 'Klik atau seret bukti transfer ke sini';
    }

    function copyBankNumber() {
        const bankNum = document.getElementById('bank-number').innerText;
        navigator.clipboard.writeText(bankNum.replace(/\s/g, '')).then(() => {
            showToast('Nomor rekening berhasil disalin!', 'success');
        });
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

    function hideStatusModal() {
        const overlay = document.getElementById('status-modal');
        const content = document.getElementById('status-modal-content');
        overlay.classList.remove('opacity-100');
        content.classList.add('scale-90', 'opacity-0');
        setTimeout(() => { overlay.classList.replace('flex', 'hidden'); }, 300);
    }

    function handleCheckoutSubmit() {
        const nameVal = document.getElementById('name-input').value.trim();
        const phoneVal = document.getElementById('phone-input').value.trim();
        const addressVal = document.getElementById('address-input').value.trim();
        const additionalNotes = document.getElementById('notes-input').value.trim();
        const spinner = document.getElementById('btn-spinner');

        // Validation
        if (!nameVal || !phoneVal || !addressVal) {
            showStatusModal('error', 'Formulir Belum Lengkap', 'Mohon lengkapi seluruh formulir data diri bertanda bintang (*) sebelum melanjutkan.', 'Tutup', hideStatusModal);
            return;
        }

        // Only validate proof upload if payment method is bank transfer
        if (currentPaymentMethod === 'transfer' && (!proofInput.files || !proofInput.files[0])) {
            showStatusModal('error', 'Bukti Transfer Kosong', 'Mohon lampirkan struk atau foto bukti transfer pembayaran Anda untuk memverifikasi order.', 'Tutup', hideStatusModal);
            return;
        }

        // Show spinner
        spinner.classList.remove('hidden');

        // Build composite notes to store recipient info gracefully in standard Order DB structure
        const paymentLabel = currentPaymentMethod === 'cash' ? 'BAYAR TUNAI (CASH)' : 'TRANSFER BANK (MANUAL)';
        const compositeNotes = `NAMA PENERIMA: ${nameVal}\nWHATSAPP: ${phoneVal}\nALAMAT PENYALURAN: ${addressVal}\nMETODE PEMBAYARAN: ${paymentLabel}\n-------------------------------\nCATATAN KHUSUS: ${additionalNotes || '-'}`;

        // Step 1: Create Order
        const orderFormData = new FormData();
        orderFormData.append('livestock_id', <?php echo $livestock['id']; ?>);
        orderFormData.append('qty', qtyInput.value);
        orderFormData.append('notes', compositeNotes);
        orderFormData.append('guest_name', nameVal);
        orderFormData.append('guest_phone', phoneVal);
        orderFormData.append('guest_address', addressVal);

        fetch('/lautan-ternak-pantura/api/orders/create', {
            method: 'POST',
            body: orderFormData
        })
            .then(response => response.json())
            .then(orderData => {
                if (orderData.success) {
                    if (currentPaymentMethod === 'cash') {
                        // Cash Method: No receipt upload needed. Instantly transition!
                        spinner.classList.add('hidden');
                        renderThankYouPage(orderData.order_code, nameVal, phoneVal, 'cash', orderData.order_id);
                    } else {
                        // Transfer Method: Upload receipt
                        const paymentFormData = new FormData();
                        paymentFormData.append('order_id', orderData.order_id);
                        paymentFormData.append('payment_method', 'Transfer Bank Manual');
                        paymentFormData.append('proof', proofInput.files[0]);

                        fetch('/lautan-ternak-pantura/api/orders/upload_payment', {
                            method: 'POST',
                            body: paymentFormData
                        })
                            .then(res => res.json())
                            .then(paymentData => {
                                spinner.classList.add('hidden');
                                if (paymentData.success) {
                                    renderThankYouPage(orderData.order_code, nameVal, phoneVal, 'transfer', orderData.order_id);
                                } else {
                                    showStatusModal('error', 'Order Berhasil, Upload Gagal', 'Pesanan Anda berhasil dibuat dengan invoice ' + orderData.order_code + ', namun bukti pembayaran gagal diunggah: ' + paymentData.message + '. Anda dapat mengunggah ulang melalui detail pesanan.', 'Buka Detail Pesanan', () => {
                                        window.location.href = '/lautan-ternak-pantura/order/order_detail/' + orderData.order_id;
                                    });
                                }
                            })
                            .catch(err => {
                                spinner.classList.add('hidden');
                                showStatusModal('error', 'Koneksi Pembayaran Gagal', 'Pesanan berhasil dibuat (' + orderData.order_code + '), namun gagal mengirim berkas bukti transfer ke server. Silakan lengkapi di detail pesanan.', 'Buka Detail Pesanan', () => {
                                    window.location.href = '/lautan-ternak-pantura/order/order_detail/' + orderData.order_id;
                                });
                            });
                    }
                } else {
                    spinner.classList.add('hidden');
                    showStatusModal('error', 'Checkout Gagal', orderData.message || 'Terjadi gangguan sistem saat membuat order.', 'Coba Lagi', hideStatusModal);
                }
            })
            .catch(err => {
                spinner.classList.add('hidden');
                showStatusModal('error', 'Koneksi Gagal', 'Gagal menghubungi server untuk membuat pesanan.', 'Tutup', hideStatusModal);
            });
    }

    function downloadInvoiceImage(invoiceCode, name, phone, method) {
        if (!window.html2canvas) {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = () => doDownload();
            document.head.appendChild(script);
        } else {
            doDownload();
        }

        function doDownload() {
            const invoiceContainer = document.createElement('div');
            invoiceContainer.style.position = 'absolute';
            invoiceContainer.style.left = '-9999px';
            invoiceContainer.style.top = '-9999px';

            const animalImg = document.querySelector('#checkout-flow-container img') ? document.querySelector('#checkout-flow-container img').src : '';
            const animalName = document.querySelector('#checkout-flow-container h2') ? document.querySelector('#checkout-flow-container h2').innerText : 'Hewan Ternak';
            const breed = document.querySelector('#checkout-flow-container p.tracking-widest') ? document.querySelector('#checkout-flow-container p.tracking-widest').innerText : '';

            const qtyText = document.getElementById('qty-summary') ? document.getElementById('qty-summary').innerText : '1 ekor';
            const totalText = document.getElementById('total-summary') ? document.getElementById('total-summary').innerText : '';
            const paymentLabel = method === 'cash' ? 'Bayar Tunai (Cash)' : 'Transfer Bank Manual';

            invoiceContainer.innerHTML = `
                <div id="invoice-download-card" style="width: 550px; padding: 40px; background: #ffffff; font-family: 'Inter', sans-serif;" class="rounded-3xl border border-gray-100 shadow-2xl">
                    <!-- Header with logo and brand -->
                    <div class="flex justify-between items-center border-b border-gray-100 pb-6 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-xl font-black">L</div>
                            <div>
                                <h1 class="text-xl font-black text-gray-900 tracking-tight" style="color: #047857;">Lautan Ternak Pantura</h1>
                                <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Qurban & Aqiqah Premium</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">PESANAN TERKIRIM</span>
                        </div>
                    </div>
                    
                    <!-- Invoice details -->
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div>
                            <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Kode Invoice</p>
                            <p class="font-black" style="color: #047857;">${invoiceCode}</p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Metode Pembayaran</p>
                            <p class="font-bold text-gray-700">${paymentLabel}</p>
                        </div>
                    </div>

                    <!-- Customer details -->
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 mb-6 text-sm">
                        <h4 class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-3">Detail Penerima</h4>
                        <div class="space-y-2 font-bold text-gray-700">
                            <p><span class="text-gray-400 font-medium">Nama:</span> ${name}</p>
                            <p><span class="text-gray-400 font-medium">WhatsApp:</span> ${phone}</p>
                        </div>
                    </div>

                    <!-- Animal details -->
                    <div class="border border-gray-100 rounded-2xl p-5 mb-6 flex gap-4 bg-white items-center">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-50 border shrink-0">
                            <img src="${animalImg}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-black text-gray-900 text-base leading-tight capitalize">${animalName}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">${breed}</p>
                            <div class="flex gap-4 mt-2 text-xs font-bold text-gray-500">
                                <span>Jumlah: ${qtyText}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Price summary -->
                    <div class="border-t border-gray-100 pt-6 flex justify-between items-center">
                        <div>
                            <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Total Pembayaran</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black" style="color: #047857;">${totalText}</p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(invoiceContainer);

            setTimeout(() => {
                html2canvas(document.getElementById('invoice-download-card'), {
                    useCORS: true,
                    scale: 2
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = `Invoice-${invoiceCode}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    document.body.removeChild(invoiceContainer);
                }).catch(err => {
                    console.error("Canvas export failed:", err);
                    document.body.removeChild(invoiceContainer);
                });
            }, 300);
        }
    }

    function renderThankYouPage(invoiceCode, name, phone, method, orderId) {
        const container = document.getElementById('checkout-flow-container');

        let thankYouDesc = '';
        if (method === 'cash') {
            thankYouDesc = `
                Yth. <strong class="text-gray-900">${name}</strong>,
                <p class="mt-3">Pesanan Anda telah berhasil tercatat di sistem kami dengan metode <strong class="text-brand-primary">Pembayaran Tunai (Cash)</strong>.</p>
                <p class="mt-3">Tim admin <span class="text-brand-primary font-black">Lautan Ternak Pantura</span> akan segera menghubungi Anda di nomor WhatsApp <strong class="text-brand-primary">${phone}</strong> untuk koordinasi penjemputan/penyaluran hewan ternak serta penyelesaian pembayaran tunai Anda.</p>
            `;
        } else {
            thankYouDesc = `
                Yth. <strong class="text-gray-900">${name}</strong>,
                <p class="mt-3">Pembayaran dan data diri pesanan Anda telah tersimpan dengan aman di sistem kami.</p> 
                <p class="mt-3">Tim admin <span class="text-brand-primary font-black">Lautan Ternak Pantura</span> akan segera melakukan verifikasi pembayaran transfer Anda dan menghubungi Anda di nomor WhatsApp <strong class="text-brand-primary">${phone}</strong> untuk koordinasi pengiriman atau penyaluran hewan ternak Anda.</p>
            `;
        }

        container.innerHTML = `
            <div class="max-w-2xl mx-auto text-center py-16 px-6 sm:px-12 bg-white rounded-3xl shadow-xl border border-gray-100/50 relative overflow-hidden mt-8">
                <!-- Decorative background elements -->
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-primary/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand-secondary/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <!-- Success Pulsing Checkmark -->
                    <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner animate-bounce">
                        <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center text-white text-3xl shadow-lg">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Terima Kasih Atas Pembelian Anda!</h2>
                    <p class="text-emerald-600 font-bold text-xs tracking-wider uppercase mb-8">Pesanan Berhasil Diterima</p>
                    
                    <div class="bg-gray-50 p-6 sm:p-8 rounded-3xl border border-gray-100 text-left space-y-4 mb-10">
                        <div class="flex justify-between items-center text-xs font-black text-gray-400 uppercase tracking-widest">
                            <span>Kode Invoice</span>
                            <span class="text-brand-primary font-black text-sm">${invoiceCode}</span>
                        </div>
                        <div class="border-t border-gray-200/60 pt-4 text-sm font-bold text-gray-600 leading-relaxed">
                            ${thankYouDesc}
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button onclick="downloadInvoiceImage('${invoiceCode}', '${name.replace(/'/g, "\\'")}', '${phone}', '${method}')" class="px-8 py-4 bg-brand-primary text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 flex items-center justify-center gap-2">
                            <i class="fas fa-download"></i> Download Invoice
                        </button>
                        <a href="/lautan-ternak-pantura/marketplace" class="px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center">
                            Kembali ke Katalog
                        </a>
                    </div>
                </div>
            </div>
        `;

        // Scroll to top to focus on success screen
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>