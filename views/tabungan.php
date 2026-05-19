<?php require_once 'includes/header.php'; ?>

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
                    
                    <form action="/lautan-ternak-pantura/api/savings/plan" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-bullseye text-brand-primary mr-1.5"></i> Pilih Target Qurban</label>
                            <div class="relative">
                                <?php if ($selectedLivestock): ?>
                                    <input type="hidden" name="livestock_id" value="<?php echo $selectedLivestock['id']; ?>">
                                    <div class="w-full pl-4 pr-10 py-3.5 text-base bg-blue-50 border border-brand-primary/30 rounded-xl font-bold text-brand-primary flex items-center justify-between">
                                        <span><?php echo ucfirst($selectedLivestock['name'] ?? $selectedLivestock['type'] ?? $selectedLivestock['category'] ?? 'Hewan'); ?> - Rp <?php echo number_format($selectedLivestock['price'], 0, ',', '.'); ?></span>
                                        <a href="/lautan-ternak-pantura/marketplace" class="text-[10px] underline">Ganti</a>
                                    </div>
                                    <input type="hidden" id="target_type" value="<?php echo (int)$selectedLivestock['price']; ?>">
                                <?php else: ?>
                                    <select id="target_type" class="appearance-none block w-full pl-4 pr-10 py-3.5 text-base bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all cursor-pointer">
                                        <option value="3500000">Kambing Standar (± Rp 3.500.000)</option>
                                        <option value="4500000">Kambing Super (± Rp 4.500.000)</option>
                                        <option value="3000000">Sapi 1/7 Bagian (± Rp 3.000.000)</option>
                                        <option value="21000000">Sapi Utuh (± Rp 21.000.000)</option>
                                        <option value="custom">Nominal Custom</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="custom_amount_div" class="<?php echo $selectedLivestock ? 'hidden' : ''; ?>">
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-money-bill-wave text-brand-primary mr-1.5"></i> Nominal Target (Rp)</label>
                            <input type="number" id="target_amount" name="target_amount" value="<?php echo $selectedLivestock ? (int)$selectedLivestock['price'] : '3500000'; ?>" <?php echo $selectedLivestock ? 'readonly' : ''; ?> class="block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt text-brand-primary mr-1.5"></i> Durasi Menabung (Bulan)</label>
                            <input type="number" id="duration" name="duration" min="1" max="12" value="10" class="block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all">
                        </div>

                        <div class="bg-gradient-to-br from-brand-primary to-[#0a4286] p-6 rounded-2xl shadow-lg mt-8 relative overflow-hidden group">
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center relative z-10 gap-2">
                                <span class="text-sm font-medium text-blue-100">Estimasi Cicilan per Bulan</span>
                                <span id="monthly_result" class="text-3xl font-bold text-white tracking-tight">Rp 350.000</span>
                            </div>
                        </div>

                        <!-- Data Sohibul Qurban -->
                        <div class="border-t border-gray-100 pt-6 mt-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-gray-800"><i class="fas fa-user-circle text-brand-primary mr-1.5"></i> Data Sohibul Qurban</h3>
                                <button type="button" id="toggle-sq" class="text-brand-primary text-xs font-semibold hover:underline flex items-center gap-1">
                                    <i class="fas fa-chevron-down text-[10px]"></i> Isi Data
                                </button>
                            </div>
                            <div id="sq-section" class="space-y-5 hidden">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" name="sq_name" id="sq_name" required
                                        class="block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all"
                                        placeholder="Masukkan nama sohibul qurban">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Hubungan</label>
                                    <select name="sq_relationship"
                                        class="appearance-none block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all cursor-pointer">
                                        <option value="self">Diri Sendiri</option>
                                        <option value="ayah">Ayah</option>
                                        <option value="ibu">Ibu</option>
                                        <option value="kakek">Kakek</option>
                                        <option value="nenek">Nenek</option>
                                        <option value="suami">Suami</option>
                                        <option value="istri">Istri</option>
                                        <option value="anak">Anak</option>
                                        <option value="keluarga">Keluarga</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon</label>
                                        <input type="tel" name="sq_phone"
                                            class="block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all"
                                            placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                                        <textarea name="sq_address" rows="1"
                                            class="block w-full px-4 py-3.5 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 focus:border-brand-primary sm:text-sm rounded-xl transition-all resize-none"
                                            placeholder="Alamat lengkap"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-md text-base font-bold text-white bg-brand-primary hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand-primary/30 transition-all hover:-translate-y-0.5">
                                    <i class="fas fa-piggy-bank mr-2 text-lg"></i> Buat Rencana Tabungan
                                </button>
                            <?php else: ?>
                                <a href="/lautan-ternak-pantura/views/auth/login?action=register&redirect=<?php echo urlencode('/lautan-ternak-pantura/tabungan'); ?>" class="w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-sm text-base font-bold text-brand-primary bg-brand-light hover:bg-brand-light/80 border border-brand-primary/20 transition-all hover:-translate-y-0.5">
                                    Daftar untuk menabung <i class="fas fa-arrow-right ml-2 mt-0.5 text-sm"></i>
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
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-primary">
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
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-primary">
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
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-brand-light text-brand-primary">
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

    // Toggle Sohibul Qurban section
    document.getElementById('toggle-sq').addEventListener('click', function() {
        const section = document.getElementById('sq-section');
        const icon = this.querySelector('i');
        section.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
        this.querySelector('span').innerText = section.classList.contains('hidden') ? 'Isi Data' : 'Sembunyikan';
    });
</script>

<?php require_once 'includes/footer.php'; ?>
