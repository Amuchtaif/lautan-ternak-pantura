<?php require_once 'includes/header.php'; ?>
<?php
$livestocks = $livestocks ?? [];
$currentUser = $currentUser ?? [];
$selectedLivestockId = $selectedLivestock ? (int)$selectedLivestock['id'] : (int)($livestocks[0]['id'] ?? 0);
$tomorrow = date('Y-m-d', strtotime('+1 month'));
$defaultTargetDate = date('Y-m-d', strtotime('+10 months'));
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <a href="/lautan-ternak-pantura/tabungan" class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Program
                </a>
                <h1 class="text-3xl font-black text-gray-900 mt-4">Form Pendaftaran Tabungan Qurban</h1>
                <p class="text-sm text-gray-500 mt-2">Isi data peserta, pilih target hewan dari database, lalu tentukan target pelunasan.</p>
            </div>
            <a href="/lautan-ternak-pantura/savings" class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-full bg-white border border-gray-200 text-gray-700 font-bold hover:border-brand-primary hover:text-brand-primary transition">
                <i class="fas fa-gauge-high"></i> Dashboard Tabungan
            </a>
        </div>

        <?php if (empty($livestocks)): ?>
            <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 text-center">
                <div class="mx-auto h-14 w-14 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-4">
                    <i class="fas fa-cow text-2xl"></i>
                </div>
                <h2 class="text-xl font-black text-gray-900">Belum ada hewan tersedia</h2>
                <p class="text-sm text-gray-500 mt-2">Form pendaftaran akan aktif setelah admin menambahkan hewan berstatus tersedia.</p>
            </div>
        <?php else: ?>
            <form action="/lautan-ternak-pantura/api/savings/create" method="POST" class="grid lg:grid-cols-12 gap-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="livestock_target" id="livestock_target" value="">
                <input type="hidden" name="target_amount" id="target_amount" value="">
                <input type="hidden" name="duration_month" id="duration_month" value="10">

                <div class="lg:col-span-8 space-y-6">
                    <section class="bg-white border border-gray-100 rounded-2xl p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-11 w-11 rounded-xl bg-brand-light text-brand-primary flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-900">Data Pribadi Peserta</h2>
                                <p class="text-xs text-gray-500">Data ini digunakan untuk administrasi tabungan qurban.</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Nama Lengkap</label>
                                <input type="text" name="participant_name" required value="<?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['name'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">No. Telepon</label>
                                <input type="tel" name="participant_phone" required value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" placeholder="08xxxxxxxxxx" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary focus:bg-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Alamat</label>
                                <textarea name="participant_address" rows="3" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-brand-primary focus:bg-white resize-none"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white border border-gray-100 rounded-2xl p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-11 w-11 rounded-xl bg-brand-light text-brand-primary flex items-center justify-center">
                                <i class="fas fa-cow"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-900">Pilihan Target Hewan Qurban</h2>
                                <p class="text-xs text-gray-500">Daftar hewan dimuat dari database hewan berstatus tersedia.</p>
                            </div>
                        </div>

                        <div id="livestock-list" class="grid sm:grid-cols-2 gap-4">
                            <?php foreach ($livestocks as $item): ?>
                                <?php
                                $itemId = (int)$item['id'];
                                $price = (float)$item['price'];
                                $image = $item['image'] ?: '/lautan-ternak-pantura/assets/images/landing-page.jpg';
                                ?>
                                <label class="target-card cursor-pointer rounded-2xl border bg-white overflow-hidden transition-all <?php echo $itemId === $selectedLivestockId ? 'border-brand-primary ring-4 ring-brand-primary/10' : 'border-gray-100 hover:border-brand-primary/40'; ?>">
                                    <input type="radio" name="livestock_id" value="<?php echo $itemId; ?>" class="sr-only" <?php echo $itemId === $selectedLivestockId ? 'checked' : ''; ?>
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-code="<?php echo htmlspecialchars($item['livestock_code']); ?>"
                                        data-type="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-price="<?php echo (int)$price; ?>"
                                        data-image="<?php echo htmlspecialchars($image); ?>">
                                    <div class="grid grid-cols-[112px,1fr] min-h-[128px]">
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-full w-full object-cover bg-gray-100">
                                        <div class="p-4">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <h3 class="font-black text-gray-900 leading-tight"><?php echo htmlspecialchars($item['name']); ?></h3>
                                                    <p class="text-[11px] font-bold text-gray-400 uppercase mt-1"><?php echo htmlspecialchars($item['livestock_code']); ?></p>
                                                </div>
                                                <span class="card-check h-7 w-7 rounded-full bg-gray-100 text-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-check text-xs"></i>
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-3">Jenis: <?php echo htmlspecialchars($item['breed'] ?? $item['name']); ?> · <?php echo htmlspecialchars($item['weight']); ?> kg</p>
                                            <p class="text-lg font-black text-brand-primary mt-1">Rp <?php echo number_format($price, 0, ',', '.'); ?></p>
                                            <p class="text-xs text-gray-500">Estimasi 10 bulan: Rp <?php echo number_format($price / 10, 0, ',', '.'); ?>/bulan</p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="bg-white border border-gray-100 rounded-2xl p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-11 w-11 rounded-xl bg-brand-light text-brand-primary flex items-center justify-center">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-900">Rencana Setoran</h2>
                                <p class="text-xs text-gray-500">Estimasi bulanan dihitung otomatis tanpa reload halaman.</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Tabungan Awal</label>
                                <input type="number" name="initial_deposit" id="initial_deposit" min="10000" step="1000" value="100000" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Metode Pembayaran</label>
                                <select name="payment_method" id="payment_method" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary focus:bg-white">
                                    <option value="transfer_bank">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                    <option value="cash">Tunai di Kantor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Target Waktu Pelunasan</label>
                                <input type="date" name="target_date" id="target_date" min="<?php echo $tomorrow; ?>" value="<?php echo $defaultTargetDate; ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary focus:bg-white">
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-xs font-black text-gray-500 uppercase mb-2">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:border-brand-primary focus:bg-white resize-none" placeholder="Opsional, misalnya preferensi penyaluran atau nama sohibul qurban."></textarea>
                        </div>
                    </section>
                </div>

                <aside class="lg:col-span-4">
                    <div class="sticky top-24 bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-xl shadow-gray-900/5">
                        <div id="summary-image-wrap" class="aspect-[4/3] bg-gray-100">
                            <img id="summary-image" src="" alt="Target hewan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Ringkasan Target</p>
                                <h2 id="summary-name" class="text-2xl font-black text-gray-900 mt-1">-</h2>
                                <p id="summary-code" class="text-xs font-bold text-gray-500 uppercase mt-1">-</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-[11px] font-black text-gray-400 uppercase">Target</p>
                                    <p id="summary-price" class="font-black text-gray-900 mt-1">Rp 0</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 p-4">
                                    <p class="text-[11px] font-black text-gray-400 uppercase">Durasi</p>
                                    <p id="summary-duration" class="font-black text-gray-900 mt-1">10 bulan</p>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-brand-primary p-5 text-white">
                                <p class="text-sm font-semibold text-blue-100">Estimasi cicilan per bulan</p>
                                <p id="monthly_preview" class="text-3xl font-black mt-1">Rp 0</p>
                                <p id="remaining_preview" class="text-xs text-blue-100 mt-2">Setelah dikurangi tabungan awal.</p>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 bg-brand-primary text-white py-4 rounded-xl font-black hover:bg-brand-dark transition">
                                <i class="fas fa-check-circle"></i> Daftar Sekarang
                            </button>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Setelah berhasil daftar, Anda akan diarahkan otomatis ke dashboard detail tabungan user.
                            </p>
                        </div>
                    </div>
                </aside>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
const cards = document.querySelectorAll('.target-card');
const radios = document.querySelectorAll('input[name="livestock_id"]');
const targetNameInput = document.getElementById('livestock_target');
const targetAmountInput = document.getElementById('target_amount');
const durationInput = document.getElementById('duration_month');
const initialDepositInput = document.getElementById('initial_deposit');
const targetDateInput = document.getElementById('target_date');
const monthlyPreview = document.getElementById('monthly_preview');
const remainingPreview = document.getElementById('remaining_preview');
const summaryName = document.getElementById('summary-name');
const summaryCode = document.getElementById('summary-code');
const summaryPrice = document.getElementById('summary-price');
const summaryDuration = document.getElementById('summary-duration');
const summaryImage = document.getElementById('summary-image');

function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
}

function monthDiffFromToday(dateValue) {
    const target = new Date(dateValue + 'T00:00:00');
    const today = new Date();
    if (Number.isNaN(target.getTime())) return 1;
    let months = (target.getFullYear() - today.getFullYear()) * 12 + (target.getMonth() - today.getMonth());
    if (target.getDate() > today.getDate()) months += 1;
    return Math.max(1, months);
}

function activeRadio() {
    return document.querySelector('input[name="livestock_id"]:checked') || radios[0];
}

function refreshCardState() {
    cards.forEach(card => {
        const checked = card.querySelector('input[type="radio"]').checked;
        card.classList.toggle('border-brand-primary', checked);
        card.classList.toggle('ring-4', checked);
        card.classList.toggle('ring-brand-primary/10', checked);
        card.classList.toggle('border-gray-100', !checked);
        const check = card.querySelector('.card-check');
        check.classList.toggle('bg-brand-primary', checked);
        check.classList.toggle('text-white', checked);
        check.classList.toggle('bg-gray-100', !checked);
        check.classList.toggle('text-gray-300', !checked);
    });
}

function calculatePlan() {
    const selected = activeRadio();
    if (!selected) return;

    const price = parseFloat(selected.dataset.price || 0);
    const initialDeposit = Math.max(0, parseFloat(initialDepositInput.value || 0));
    const months = monthDiffFromToday(targetDateInput.value);
    const remaining = Math.max(0, price - initialDeposit);
    const monthly = remaining / months;

    targetNameInput.value = selected.dataset.name || 'Hewan Qurban';
    targetAmountInput.value = price;
    durationInput.value = months;

    summaryName.textContent = selected.dataset.name || '-';
    summaryCode.textContent = selected.dataset.code || '-';
    summaryPrice.textContent = formatRupiah(price);
    summaryDuration.textContent = months + ' bulan';
    summaryImage.src = selected.dataset.image || '/lautan-ternak-pantura/assets/images/landing-page.jpg';
    monthlyPreview.textContent = formatRupiah(monthly);
    remainingPreview.textContent = 'Sisa target ' + formatRupiah(remaining) + ' setelah tabungan awal.';
    refreshCardState();
}

radios.forEach(radio => radio.addEventListener('change', calculatePlan));
initialDepositInput.addEventListener('input', calculatePlan);
targetDateInput.addEventListener('change', calculatePlan);
calculatePlan();

fetch('/lautan-ternak-pantura/api/savings/livestock')
    .then(response => response.ok ? response.json() : null)
    .then(payload => {
        if (!payload || !payload.success) return;
        console.info('Loaded available qurban targets:', payload.data.length);
    })
    .catch(() => {});
</script>

<?php require_once 'includes/footer.php'; ?>
