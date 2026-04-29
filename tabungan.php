<?php 
require_once 'config/database.php';
require_once 'includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Tabungan Qurban</h1>
            <p class="mt-4 text-lg text-gray-500">Rencanakan ibadah qurban Anda dengan lebih ringan melalui sistem tabungan cicilan bulanan yang transparan dan sesuai syariat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            
            <!-- Kalkulator & Form -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Kalkulator & Mulai Menabung</h2>
                    
                    <form action="/lautan-ternak-pantura/api/savings/plan.php" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Target Qurban</label>
                            <select id="target_type" class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-brand-green focus:border-brand-green sm:text-sm rounded-md border shadow-sm">
                                <option value="3500000">Kambing Standar (± Rp 3.500.000)</option>
                                <option value="4500000">Kambing Super (± Rp 4.500.000)</option>
                                <option value="3000000">Sapi 1/7 Bagian (± Rp 3.000.000)</option>
                                <option value="21000000">Sapi Utuh (± Rp 21.000.000)</option>
                                <option value="custom">Nominal Custom</option>
                            </select>
                        </div>

                        <div id="custom_amount_div" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Target (Rp)</label>
                            <input type="number" id="target_amount" name="target_amount" value="3500000" class="focus:ring-brand-green focus:border-brand-green block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-3 px-4 border">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Menabung (Bulan)</label>
                            <input type="number" id="duration" name="duration" min="1" max="12" value="10" class="focus:ring-brand-green focus:border-brand-green block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-3 px-4 border">
                        </div>

                        <div class="bg-brand-light p-5 rounded-xl border border-brand-green border-opacity-20 mt-6 shadow-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-brand-dark">Estimasi Cicilan per Bulan</span>
                                <span id="monthly_result" class="text-2xl font-bold text-brand-green">Rp 350.000</span>
                            </div>
                        </div>

                        <div class="pt-4">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-medium text-white bg-brand-green hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green transition">
                                    Buat Rencana Tabungan
                                </button>
                            <?php else: ?>
                                <a href="/lautan-ternak-pantura/views/auth/login.php" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 transition">
                                    Login untuk Menabung
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informasi -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-50 flex gap-4 hover:shadow-lg transition">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-green">
                            <i class="fas fa-lock text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Aman & Transparan</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">Dana tabungan Anda disimpan dengan aman dan riwayat transaksi dapat dipantau setiap saat melalui dashboard.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-50 flex gap-4 hover:shadow-lg transition">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-green">
                            <i class="fas fa-coins text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Tanpa Bunga (Bebas Riba)</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">Sistem murni tabungan tanpa ada penambahan bunga atau denda keterlambatan. Sepenuhnya sesuai syariat Islam.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-50 flex gap-4 hover:shadow-lg transition">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-green">
                            <i class="fas fa-handshake text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Jaminan Hewan Qurban</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">Ketika tabungan mencapai target, Anda langsung dapat memilih hewan qurban yang tersedia di marketplace kami.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const targetTypeSelect = document.getElementById('target_type');
    const targetAmountInput = document.getElementById('target_amount');
    const durationInput = document.getElementById('duration');
    const customAmountDiv = document.getElementById('custom_amount_div');
    const monthlyResult = document.getElementById('monthly_result');

    function calculate() {
        let amount = targetTypeSelect.value === 'custom' ? parseInt(targetAmountInput.value) : parseInt(targetTypeSelect.value);
        if(isNaN(amount)) amount = 0;
        
        let duration = parseInt(durationInput.value);
        if(isNaN(duration) || duration < 1) duration = 1;

        if(targetTypeSelect.value === 'custom') {
            customAmountDiv.classList.remove('hidden');
        } else {
            customAmountDiv.classList.add('hidden');
            targetAmountInput.value = amount;
        }

        let monthly = amount / duration;
        monthlyResult.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(monthly);
    }

    targetTypeSelect.addEventListener('change', calculate);
    targetAmountInput.addEventListener('input', calculate);
    durationInput.addEventListener('input', calculate);

    // Initial calculation
    calculate();
</script>

<?php require_once 'includes/footer.php'; ?>
