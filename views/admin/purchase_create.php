<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Catat Pembelian Stok';
    $topbarSubtitle = 'Registrasi pembelian stok hewan baru ke inventori marketplace';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="max-w-4xl mx-auto space-y-8">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <a href="/lautan-ternak-pantura/purchase/index"
                    class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Catat <span
                            class="text-brand-primary">Pembelian Stok</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Registrasi pembelian hewan
                        dari pemasok dan tambahkan ke inventori marketplace</p>
                </div>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        showToast("<?php echo addslashes(htmlspecialchars($errorMsg)); ?>", "error");
                    });
                </script>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl p-10 border border-gray-100 shadow-sm">
                <form method="POST" action="/lautan-ternak-pantura/purchase/create" enctype="multipart/form-data"
                    onsubmit="return validatePricingForm(event)" class="space-y-8">
                    
                    <!-- Pilihan Tipe Pembelian -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tipe Pembelian</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-plus-circle text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Hewan Baru</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Registrasi stok baru</p>
                                    </div>
                                </div>
                                <input type="radio" name="purchase_type" value="new" checked class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('new')">
                            </label>
                            <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-database text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Hewan Terdaftar</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Tambah stok terdaftar</p>
                                    </div>
                                </div>
                                <input type="radio" name="purchase_type" value="existing" class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('existing')">
                            </label>
                        </div>
                    </div>

                    <!-- Pilihan Metode Pembayaran Pemasok -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Metode Pembayaran Pemasok</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-hand-holding-usd text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Uang Muka (DP)</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Pembayaran Bertahap</p>
                                    </div>
                                </div>
                                <input type="radio" name="payment_type" value="dp" required class="accent-brand-primary h-4 w-4" onchange="togglePaymentType('dp')">
                            </label>
                            <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-money-bill-wave text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Lunas / Full</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Bayar Sekaligus</p>
                                    </div>
                                </div>
                                <input type="radio" name="payment_type" value="lunas" required class="accent-brand-primary h-4 w-4" onchange="togglePaymentType('lunas')">
                            </label>
                        </div>
                    </div>

                    <!-- Input Jumlah Uang Muka DP (Hanya Tampil jika DP dipilih) -->
                    <div id="amount_paid_container" class="space-y-2 hidden transition-all duration-300">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Uang Muka (DP) (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                            <input type="text" id="amount_paid" name="amount_paid" oninput="formatCurrency(this)" placeholder="Contoh: 5.000.000..."
                                class="w-full pl-14 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>
                    </div>

                    <!-- Rekening Kas Sumber Pembayaran -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Rekening Kas Sumber Pembayaran</label>
                        <div class="relative">
                            <select name="cash_account_id" required
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none cursor-pointer">
                                <option value="">-- PILIH REKENING KAS / BANK --</option>
                                <?php foreach ($accountsList as $acc): ?>
                                    <option value="<?php echo $acc['id']; ?>">
                                        <?php echo htmlspecialchars($acc['name']); ?> (Saldo: Rp <?php echo number_format($acc['current_balance'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Pilihan Hewan dari Database (Hanya Tampil jika Existing) -->
                    <div id="existing-livestock-field" class="space-y-6 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Select -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Hewan dari Data</label>
                                <div class="relative">
                                    <select name="livestock_id" id="modal_livestock_id"
                                        class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                        <option value="">-- Pilih Hewan --</option>
                                        <?php if (!empty($livestockList)): ?>
                                            <?php foreach ($livestockList as $live): ?>
                                                <option value="<?php echo $live['id']; ?>" data-price="<?php echo $live['price']; ?>">
                                                    <?php echo htmlspecialchars($live['name']); ?> - Rp <?php echo number_format($live['price'], 0, ',', '.'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <!-- Qty -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas / Jumlah (Ekor)</label>
                                <input type="number" name="qty" value="1" min="1" required
                                    class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Pembelian / Pengadaan (Internal)</label>
                            <textarea name="notes" placeholder="Contoh: Dibeli dari supplier Pak Slamet, Cirebon..."
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[80px]"></textarea>
                        </div>
                    </div>

                    <!-- Input Data Hewan Baru (Grid layout) -->
                    <div id="new-livestock-fields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Hewan</label>
                            <input type="text" name="livestock_name" required placeholder="masukkan nama hewan"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>

                        <!-- Peternak Name -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Mitra Peternak / Supplier</label>
                            <input type="text" name="peternak_name" required placeholder="masukkan nama peternak"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>

                        <!-- Breed (replaced with category) -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kategori Hewan</label>
                            <div class="relative">
                                <select name="breed" required
                                    class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none cursor-pointer">
                                    <option value="">-- PILIH KATEGORI --</option>
                                    <option value="kambing">Kambing</option>
                                    <option value="sapi">Sapi</option>
                                    <option value="domba">Domba</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Kelamin</label>
                            <div class="relative">
                                <select name="gender"
                                    class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                                    <option value="male">Jantan</option>
                                    <option value="female">Betina</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Weight -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Berat Hewan (kg)</label>
                            <input type="number" step="0.01" name="weight" required placeholder="Contoh: 350.5"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                        </div>

                        <!-- Qty & Usia (Age) in one row -->
                        <div class="grid grid-cols-2 gap-6 md:col-span-2">
                            <!-- Qty -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas / Jumlah (Ekor)</label>
                                <input type="number" name="qty" value="1" min="1" required
                                    class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                            </div>

                            <!-- Age -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Usia Hewan (Bulan)</label>
                                <input type="number" name="age" required placeholder="Contoh: 24"
                                    class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                            </div>
                        </div>

                        <!-- Harga Beli & Jual in one row -->
                        <div class="grid grid-cols-2 gap-6 md:col-span-2">
                            <!-- Purchase Price -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Beli dari Pemasok (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                                    <input type="text" id="purchase_price" name="purchase_price" required
                                        oninput="formatCurrency(this)"
                                        class="w-full pl-14 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                                </div>
                            </div>

                            <!-- Selling Price -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Jual Marketplace (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                                    <input type="text" id="selling_price" name="selling_price" required
                                        oninput="formatCurrency(this)"
                                        class="w-full pl-14 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi Hewan untuk Marketplace</label>
                            <textarea name="description" placeholder="Contoh: Nafsu makan baik, sehat..."
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[80px]"></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Pembelian / Pengadaan (Internal)</label>
                            <textarea name="notes" placeholder="Contoh: Dibeli dari supplier Pak Slamet, Cirebon..."
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm min-h-[80px]"></textarea>
                        </div>

                        <!-- Upload Gambar Hewan -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Gambar Hewan Ternak</label>
                            <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-gray-200 hover:border-brand-primary/40 hover:bg-brand-light/5 rounded-2xl p-6 transition-all duration-300 group cursor-pointer">
                                <input type="file" name="image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this)">
                                <div class="text-center space-y-2 pointer-events-none">
                                    <div class="w-12 h-12 rounded-full bg-brand-light text-brand-primary flex items-center justify-center mx-auto transition-transform group-hover:scale-110">
                                        <i class="fas fa-image text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-gray-700">Pilih gambar atau drop file di sini</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Format: JPG, PNG, WEBP (Max 2MB)</p>
                                    </div>
                                </div>
                                <!-- Image Preview Container -->
                                <div id="image-preview-container" class="hidden mt-4 w-full flex justify-center">
                                    <img id="image-preview-img" src="#" alt="Preview" class="max-h-40 rounded-xl object-contain border border-gray-100 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 border-t border-gray-50 pt-6">
                        <a href="/lautan-ternak-pantura/purchase/index"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 bg-brand-primary text-white py-4 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Simpan & Tambah Stok
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
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

    function previewImage(input) {
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview-img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImg.src = "#";
            previewContainer.classList.add('hidden');
        }
    }

    function togglePurchaseType(type) {
        const newFields = document.getElementById('new-livestock-fields');
        const existingField = document.getElementById('existing-livestock-field');
        
        const newInputs = newFields.querySelectorAll('input, select, textarea');
        const existingInputs = existingField.querySelectorAll('input, select, textarea');

        if (type === 'existing') {
            newFields.classList.add('hidden');
            existingField.classList.remove('hidden');
            
            newInputs.forEach(input => {
                input.disabled = true;
                if (input.hasAttribute('required')) {
                    input.setAttribute('data-was-required', 'true');
                    input.removeAttribute('required');
                }
            });
            existingInputs.forEach(input => {
                input.disabled = false;
                if (input.getAttribute('data-was-required') === 'true' || input.id === 'modal_livestock_id' || input.name === 'qty') {
                    input.setAttribute('required', 'true');
                }
            });
        } else {
            newFields.classList.remove('hidden');
            existingField.classList.add('hidden');
            
            newInputs.forEach(input => {
                input.disabled = false;
                if (input.getAttribute('data-was-required') === 'true') {
                    input.setAttribute('required', 'true');
                }
            });
            existingInputs.forEach(input => {
                input.disabled = true;
                input.removeAttribute('required');
            });
        }
    }

    // Initialize initial state on load
    document.addEventListener('DOMContentLoaded', function() {
        const checkedType = document.querySelector('input[name="purchase_type"]:checked').value;
        togglePurchaseType(checkedType);
        
        // Reset payment type selection
        document.querySelectorAll('input[name="payment_type"]').forEach(input => input.checked = false);
        togglePaymentType('lunas');
    });

    function togglePaymentType(type) {
        const amountContainer = document.getElementById('amount_paid_container');
        const amountInput = document.getElementById('amount_paid');
        
        if (type === 'dp') {
            amountContainer.classList.remove('hidden');
            amountInput.setAttribute('required', 'true');
        } else {
            amountContainer.classList.add('hidden');
            amountInput.removeAttribute('required');
            amountInput.value = '';
        }
    }

    function validatePricingForm(event) {
        const purchaseType = document.querySelector('input[name="purchase_type"]:checked').value;
        
        const paymentTypeChecked = document.querySelector('input[name="payment_type"]:checked');
        if (!paymentTypeChecked) {
            showToast('Silakan pilih metode pembayaran pemasok terlebih dahulu!', 'error');
            event.preventDefault();
            return false;
        }

        let purchasePrice = 0;
        let qty = 1;

        if (purchaseType === 'new') {
            const purchasePriceInput = document.getElementById('purchase_price');
            const sellingPriceInput = document.getElementById('selling_price');

            purchasePrice = parseFloat(purchasePriceInput.value.replace(/\D/g, "")) || 0;
            const sellingPrice = parseFloat(sellingPriceInput.value.replace(/\D/g, "")) || 0;

            if (sellingPrice < purchasePrice) {
                showToast('Harga jual tidak boleh lebih kecil dari harga beli!', 'error');
                event.preventDefault();
                return false;
            }

            // Set raw numeric values back to inputs before submission
            purchasePriceInput.value = purchasePrice;
            sellingPriceInput.value = sellingPrice;

            const newQtyInput = document.querySelector('input[name="qty"]');
            qty = parseInt(newQtyInput.value) || 1;
        } else {
            const existingSelect = document.getElementById('modal_livestock_id');
            if (existingSelect.value === "") {
                showToast('Silakan pilih hewan terdaftar terlebih dahulu!', 'error');
                event.preventDefault();
                return false;
            }
            const selectedOption = existingSelect.options[existingSelect.selectedIndex];
            purchasePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

            const existingQtyInput = document.querySelector('input[name="qty"]');
            qty = parseInt(existingQtyInput.value) || 1;
        }

        if (paymentTypeChecked.value === 'dp') {
            const amountInput = document.getElementById('amount_paid');
            const amountPaid = parseFloat(amountInput.value.replace(/\D/g, "")) || 0;
            const total = purchasePrice * qty;

            if (amountPaid <= 0) {
                showToast('Pembayaran awal (DP) harus diisi dan lebih besar dari 0!', 'error');
                event.preventDefault();
                return false;
            }
            if (amountPaid >= total) {
                showToast('Pembayaran DP tidak boleh melebihi atau sama dengan total harga beli. Gunakan tipe Lunas.', 'error');
                event.preventDefault();
                return false;
            }

            // Set raw numeric value before submission
            amountInput.value = amountPaid;
        }
        
        return true;
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>