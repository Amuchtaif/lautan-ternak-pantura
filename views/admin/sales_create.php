<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Catat Penjualan Baru';
    $topbarSubtitle = 'Log transaksi penjualan manual dari pelanggan walk-in';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="max-w-4xl mx-auto space-y-8">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <a href="/lautan-ternak-pantura/sales/index"
                    class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-400 hover:text-gray-700 hover:shadow-md transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Catat <span
                            class="text-brand-primary">Transaksi Penjualan</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Registrasi penjualan ternak langsung secara manual oleh Admin</p>
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
                <form method="POST" action="/lautan-ternak-pantura/sales/create" onsubmit="return validatePricingForm(event)" class="space-y-6">
                    
                    <!-- Customer Details Section -->
                    <div class="bg-brand-light/20 p-6 rounded-2xl border border-brand-primary/10 space-y-4">
                        <h3 class="text-sm font-black text-brand-primary uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-user"></i> Informasi Pelanggan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Pelanggan</label>
                                <input type="text" name="customer_name" required placeholder="Nama lengkap pelanggan..." 
                                    class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold text-gray-700">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">No. WhatsApp / HP</label>
                                <input type="text" name="customer_phone" required placeholder="Contoh: 08123456789..." 
                                    class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold text-gray-700">
                            </div>
                        </div>
                    </div>

                    <!-- Order Details Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Select Livestock -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Hewan Ternak</label>
                            <div class="relative">
                                <select name="livestock_id" id="livestock_id" required onchange="handleLivestockChange()"
                                    class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                                    <option value="">-- Pilih Hewan --</option>
                                    <?php if (!empty($livestockList)): ?>
                                        <?php foreach ($livestockList as $live): ?>
                                            <option value="<?php echo $live['id']; ?>" data-price="<?php echo $live['selling_price']; ?>" data-stock="<?php echo $live['stock']; ?>">
                                                [<?php echo htmlspecialchars($live['livestock_code']); ?>] <?php echo htmlspecialchars($live['breed']); ?> - Rp <?php echo number_format($live['selling_price'], 0, ',', '.'); ?> (Stok: <?php echo $live['stock']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas / Jumlah (Ekor)</label>
                            <input type="number" name="qty" id="qty" value="1" min="1" required oninput="calculateTotal()"
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>
                    </div>

                    <!-- Payment Specifications -->
                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-wallet text-brand-primary"></i> Detail Pembayaran
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Type Selection -->
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Pembayaran</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                        <div class="text-left">
                                            <p class="text-xs font-black text-gray-900 leading-none">Bayar DP</p>
                                            <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Uang Muka</p>
                                        </div>
                                        <input type="radio" name="payment_type" value="dp" checked
                                            class="accent-brand-primary h-4 w-4" onchange="togglePaymentType('dp')">
                                    </label>
                                    <label class="relative flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                        <div class="text-left">
                                            <p class="text-xs font-black text-gray-900 leading-none">Beli Lunas</p>
                                            <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Bayar Penuh</p>
                                        </div>
                                        <input type="radio" name="payment_type" value="lunas"
                                            class="accent-brand-primary h-4 w-4" onchange="togglePaymentType('lunas')">
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Amount -->
                            <div class="space-y-1.5" id="amount_input_container">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Uang Muka (DP) (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                                    <input type="text" id="payment_amount" name="payment_amount" required placeholder="0"
                                        oninput="formatCurrency(this)"
                                        class="w-full pl-12 pr-5 py-3.5 bg-white border border-gray-200 rounded-xl outline-none focus:border-brand-primary transition-all font-bold text-xs">
                                </div>
                            </div>
                        </div>

                        <!-- Calculations Summary -->
                        <div class="border-t border-gray-200 pt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs font-bold text-gray-500">
                            <div>
                                <span>Harga per Ekor: <span id="label_price" class="text-gray-800">Rp 0</span></span>
                            </div>
                            <div class="sm:text-right">
                                <span class="text-sm font-black text-gray-900">Total Tagihan: <span id="label_total" class="text-brand-primary text-base font-extrabold">Rp 0</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Penjualan (Opsional)</label>
                        <textarea name="notes" placeholder="Catatan opsional mengenai pengiriman atau request khusus..."
                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white transition-all font-bold text-xs min-h-[80px]"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex gap-4 border-t border-gray-50 pt-6">
                        <a href="/lautan-ternak-pantura/sales/index"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-4 rounded-xl font-black text-xs uppercase tracking-widest transition-all text-center flex items-center justify-center">
                            Batal
                        </a>
                        <button type="submit"
                            class="flex-1 bg-brand-primary text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Catat Transaksi
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<script>
    let activePrice = 0;
    let activeStock = 0;

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function formatNumber(num) {
        if (!num) return "Rp 0";
        return "Rp " + new Intl.NumberFormat("id-ID").format(Math.round(num));
    }

    function handleLivestockChange() {
        const select = document.getElementById('livestock_id');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && select.value !== "") {
            activePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            activeStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
            
            document.getElementById('label_price').innerText = formatNumber(activePrice);
            
            const qtyInput = document.getElementById('qty');
            qtyInput.max = activeStock;
        } else {
            activePrice = 0;
            activeStock = 0;
            document.getElementById('label_price').innerText = "Rp 0";
        }
        
        calculateTotal();
    }

    function calculateTotal() {
        const qty = parseInt(document.getElementById('qty').value) || 1;
        const total = activePrice * qty;
        
        document.getElementById('label_total').innerText = formatNumber(total);
        
        // If lunas is selected, sync the input automatically
        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        if (payType === 'lunas') {
            const cleanVal = Math.round(total).toString();
            const payAmtInput = document.getElementById('payment_amount');
            payAmtInput.value = new Intl.NumberFormat("id-ID").format(cleanVal);
        }
    }

    function togglePaymentType(type) {
        const container = document.getElementById('amount_input_container');
        const label = container.querySelector('label');
        const payAmtInput = document.getElementById('payment_amount');
        
        if (type === 'lunas') {
            container.classList.add('opacity-60');
            payAmtInput.readOnly = true;
            label.innerText = "Jumlah Pembayaran Lunas (Otomatis) (Rp)";
        } else {
            container.classList.remove('opacity-60');
            payAmtInput.readOnly = false;
            payAmtInput.value = "";
            label.innerText = "Jumlah Uang Muka (DP) (Rp)";
        }
        
        calculateTotal();
    }

    function validatePricingForm(event) {
        const select = document.getElementById('livestock_id');
        if (select.value === "") {
            showToast('Silakan pilih hewan terlebih dahulu!', 'error');
            event.preventDefault();
            return false;
        }

        const qty = parseInt(document.getElementById('qty').value) || 0;
        if (qty > activeStock) {
            showToast('Jumlah melebihi stok yang tersedia!', 'error');
            event.preventDefault();
            return false;
        }

        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        const total = activePrice * qty;
        
        const payAmtInput = document.getElementById('payment_amount');
        const rawAmt = parseFloat(payAmtInput.value.replace(/\D/g, "")) || 0;

        if (payType === 'dp') {
            if (rawAmt <= 0) {
                showToast('Pembayaran DP harus diisi dan lebih dari 0!', 'error');
                event.preventDefault();
                return false;
            }
            if (rawAmt >= total) {
                showToast('Pembayaran DP tidak boleh melebihi atau sama dengan total harga. Gunakan tipe Lunas.', 'error');
                event.preventDefault();
                return false;
            }
        } else {
            if (rawAmt < total) {
                showToast('Nominal pelunasan tidak mencukupi total harga transaksi!', 'error');
                event.preventDefault();
                return false;
            }
        }

        // Set raw numeric values back to inputs before submission
        payAmtInput.value = rawAmt;
        return true;
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
