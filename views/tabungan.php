<?php
require_once __DIR__ . '/../includes/header.php';

$livestocks = $livestocks ?? [];
$defaultTargetDate = date('Y-m-d', strtotime('+10 months'));
$minTargetDate = date('Y-m-d', strtotime('+1 month'));
?>

<div class="bg-white">
    <section class="bg-gradient-to-br from-brand-light/70 via-white to-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-black text-brand-primary uppercase tracking-[0.22em] mb-4">Program Tabungan Qurban</p>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 leading-tight">Daftar tabungan qurban dalam satu halaman.</h1>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed">Pilih hewan dari katalog aktif atau tentukan nominal manual, buat akun sendiri, lihat simulasi cicilan realtime, lalu langsung masuk ke dashboard tabungan.</p>
                <a href="#form-registrasi" class="mt-8 inline-flex items-center justify-center gap-3 rounded-full bg-brand-primary px-8 py-4 text-white font-black hover:bg-brand-dark transition">
                    <i class="fas fa-piggy-bank"></i> Daftar Tabungan Qurban
                </a>
            </div>
        </div>
    </section>

    <section class="py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-5">
            <?php
            $benefits = [
                ['icon' => 'fa-user-check', 'title' => 'Akun dibuat langsung', 'text' => 'Username dan password ditentukan sendiri oleh peserta.'],
                ['icon' => 'fa-bullseye', 'title' => 'Target fleksibel', 'text' => 'Pilih hewan tersedia atau isi target nominal manual.'],
                ['icon' => 'fa-chart-line', 'title' => 'Progress transparan', 'text' => 'Saldo, sisa target, dan histori setoran tampil di dashboard.']
            ];
            ?>
            <?php foreach ($benefits as $benefit): ?>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6">
                    <div class="h-12 w-12 rounded-xl bg-brand-light text-brand-primary flex items-center justify-center mb-4"><i class="fas <?php echo $benefit['icon']; ?>"></i></div>
                    <h2 class="text-lg font-black text-gray-900"><?php echo htmlspecialchars($benefit['title']); ?></h2>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed"><?php echo htmlspecialchars($benefit['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="py-14 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p class="text-sm font-black text-brand-primary uppercase tracking-[0.22em] mb-3">Cara Kerja</p>
                <h2 class="text-3xl font-black text-gray-900">Landing, isi form, submit, dashboard.</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php foreach (['Pilih target tabungan', 'Isi data dan akun', 'Cek simulasi bulanan', 'Masuk dashboard'] as $index => $step): ?>
                    <div class="rounded-2xl bg-white border border-gray-100 p-6">
                        <p class="text-4xl font-black text-brand-primary/20"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></p>
                        <h3 class="mt-4 font-black text-gray-900"><?php echo htmlspecialchars($step); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="form-registrasi" class="py-16 bg-white scroll-mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-black text-brand-primary uppercase tracking-[0.22em] mb-3">Simulasi dan Form Registrasi</p>
                <h2 class="text-3xl font-black text-gray-900">Mulai tabungan qurban sekarang.</h2>
            </div>

            <form id="tabungan-register-form" action="/lautan-ternak-pantura/api/savings/register" method="POST" enctype="multipart/form-data" class="grid lg:grid-cols-12 gap-6" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="duration_month" id="duration_month" value="10">

                <div class="lg:col-span-7 space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6">
                        <h3 class="text-xl font-black text-gray-900 mb-5">Data Pribadi</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div><input name="full_name" required placeholder="Nama lengkap" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="full_name" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div><input name="phone" required placeholder="No HP / WhatsApp" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="phone" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div class="md:col-span-2"><input name="email" type="email" placeholder="Email (opsional)" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="email" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div class="md:col-span-2"><textarea name="address" required rows="3" placeholder="Alamat lengkap" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary resize-none"></textarea><p data-error-for="address" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6">
                        <h3 class="text-xl font-black text-gray-900 mb-5">Data Akun Login</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div><input name="username" required minlength="4" pattern="[A-Za-z0-9._-]{4,50}" placeholder="Username" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="username" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div><input name="password" required minlength="8" type="password" placeholder="Password" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="password" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div><input name="password_confirm" required minlength="8" type="password" placeholder="Konfirmasi password" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="password_confirm" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">Password minimal 8 karakter dan mengandung huruf serta angka.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6">
                        <h3 class="text-xl font-black text-gray-900 mb-5">Target Tabungan</h3>
                        <div class="grid sm:grid-cols-2 gap-3 mb-5">
                            <label class="target-mode rounded-2xl border border-brand-primary bg-brand-light/50 p-4 cursor-pointer flex items-center gap-4">
                                <input type="radio" name="target_mode" value="livestock" <?php echo !empty($livestocks) ? 'checked' : ''; ?> class="sr-only" <?php echo empty($livestocks) ? 'disabled' : ''; ?>>
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary text-white"><i class="fas fa-paw"></i></span>
                                <span><span class="block font-black text-gray-900">Pilih Hewan Qurban</span><span class="text-xs text-gray-500">Target mengikuti harga hewan</span></span>
                            </label>
                            <label class="target-mode rounded-2xl border border-gray-200 bg-white p-4 cursor-pointer flex items-center gap-4">
                                <input type="radio" name="target_mode" value="manual" <?php echo empty($livestocks) ? 'checked' : ''; ?> class="sr-only">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary text-white"><i class="fas fa-calendar-alt"></i></span>
                                <span><span class="block font-black text-gray-900">Tentukan Tenor</span><span class="text-xs text-gray-500">Tentukan nominal manual</span></span>
                            </label>
                        </div>

                        <div id="livestock-target" class="grid sm:grid-cols-2 gap-4">
                            <?php foreach ($livestocks as $index => $item): ?>
                                <?php $image = $item['image'] ?: '/lautan-ternak-pantura/assets/images/landing-page.jpg'; ?>
                                <label class="livestock-card cursor-pointer rounded-2xl border <?php echo $index === 0 ? 'border-brand-primary ring-4 ring-brand-primary/10' : 'border-gray-100'; ?> overflow-hidden">
                                    <input type="radio" name="livestock_id" value="<?php echo (int)$item['id']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?> class="sr-only"
                                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                        data-price="<?php echo (int)$item['price']; ?>">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-36 w-full object-cover bg-gray-100">
                                    <div class="p-4">
                                        <p class="font-black text-gray-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Jenis: <?php echo htmlspecialchars($item['breed']); ?></p>
                                        <p class="text-lg font-black text-brand-primary mt-2">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                        <p class="text-xs text-gray-500">Estimasi 10 bulan: Rp <?php echo number_format($item['price'] / 10, 0, ',', '.'); ?>/bulan</p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div id="manual-target" class="hidden">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase mb-2">Target Nominal (Rp)</label>
                                    <input type="number" name="manual_target_amount" id="manual_target_amount" min="100000" step="50000" value="<?php echo !empty($livestocks) ? (int)$livestocks[0]['price'] : 3500000; ?>" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary">
                                    <p data-error-for="manual_target_amount" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase mb-2">Target Berapa Bulan</label>
                                    <div class="relative">
                                        <input type="number" id="manual_duration" min="1" max="12" value="10" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary pr-16">
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 font-bold text-gray-500 pointer-events-none">Bulan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6">
                        <h3 class="text-xl font-black text-gray-900 mb-5">Data Tabungan</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div><input type="date" name="target_date" id="target_date" min="<?php echo $minTargetDate; ?>" value="<?php echo $defaultTargetDate; ?>" required class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary"><p data-error-for="target_date" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                            <div>
                                <input type="text" id="initial_deposit_display" value="Rp 100.000" required placeholder="Nominal awal" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary">
                                <input type="hidden" name="initial_deposit" id="initial_deposit" value="100000">
                                <p data-error-for="initial_deposit" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            </div>
                            <div><select name="payment_method" required class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary">
                                    <option value="transfer_bank">Transfer Bank</option>
                                    <option value="cash">Tunai</option>
                                </select><p data-error-for="payment_method" class="mt-1 text-xs font-bold text-red-600 hidden"></p></div>
                        </div>
                        <div id="transfer-info" class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                            <p class="text-xs font-black uppercase tracking-widest text-blue-500">Info Transfer</p>
                            <p class="mt-2 font-black text-gray-900">Bank BCA</p>
                            <p class="text-sm text-gray-700">a.n Sohibuddin</p>
                            <p class="text-sm text-gray-700">No Rekening: <span class="font-black">1341699695</span></p>
                        </div>
                        <div id="proof-field" class="mt-5">
                            <label class="block text-xs font-black uppercase text-gray-500 mb-2">Upload Bukti Transfer (jpg/png/webp, maks 2MB)</label>
                            <input type="file" name="payment_proof" id="payment_proof" accept="image/jpeg,image/png,image/webp" class="form-field w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p data-error-for="payment_proof" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            <img id="proof-preview" src="" alt="Preview bukti transfer" class="mt-4 hidden max-h-56 w-full rounded-2xl border border-gray-100 object-contain bg-gray-50">
                        </div>
                    </div>
                </div>

                <aside class="lg:col-span-5">
                    <div class="lg:sticky lg:top-24 rounded-2xl border border-gray-100 bg-white shadow-xl shadow-gray-900/5 p-6">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Panel Simulasi</p>
                        <h3 id="summary-title" class="text-2xl font-black text-gray-900 mt-2">-</h3>
                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold text-gray-500">Target</p><p id="target-preview" class="font-black text-gray-900 mt-1">Rp 0</p></div>
                            <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold text-gray-500">Tenor</p><p id="tenor-preview" class="font-black text-gray-900 mt-1">0 bulan</p></div>
                            <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold text-gray-500">Awal</p><p id="deposit-preview" class="font-black text-gray-900 mt-1">Rp 0</p></div>
                            <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold text-gray-500">Progress</p><p id="progress-preview" class="font-black text-gray-900 mt-1">0%</p></div>
                        </div>
                        <div class="mt-5 rounded-2xl bg-brand-primary p-5 text-white">
                            <p class="text-sm text-blue-100 font-semibold">Estimasi setoran bulanan</p>
                            <p id="monthly-preview" class="text-3xl font-black mt-1">Rp 0</p>
                            <p id="remaining-preview" class="text-xs text-blue-100 mt-2">Sisa target Rp 0</p>
                        </div>
                        <div class="mt-5 h-3 rounded-full bg-gray-100 overflow-hidden"><div id="progress-bar" class="h-full bg-brand-primary rounded-full" style="width:0%"></div></div>
                        <div id="form-error-summary" class="mt-5 hidden rounded-xl bg-red-50 border border-red-100 p-4 text-sm font-bold text-red-700"></div>
                        <button id="submit-tabungan-btn" type="submit" class="mt-6 w-full rounded-xl bg-brand-primary py-4 text-white font-black hover:bg-brand-dark transition disabled:opacity-60 disabled:cursor-not-allowed">
                            Submit dan Masuk Dashboard
                        </button>
                    </div>
                </aside>
            </form>
        </div>
    </section>

    <section class="py-14 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <h2 class="text-3xl font-black text-gray-900 text-center mb-8">FAQ</h2>
            <?php foreach ([
                ['Apakah harus login dulu?', 'Tidak. Akun dibuat langsung dari form pendaftaran tabungan.'],
                ['Bisa target nominal manual?', 'Bisa. Pilih opsi Nominal Manual dan isi target sesuai kebutuhan.'],
                ['Ke mana setelah submit?', 'Sistem otomatis login dan mengarahkan Anda ke dashboard detail tabungan.']
            ] as $faq): ?>
                <div class="rounded-2xl bg-white border border-gray-100 p-6">
                    <h3 class="font-black text-gray-900"><?php echo htmlspecialchars($faq[0]); ?></h3>
                    <p class="mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($faq[1]); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
const rupiah = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const modeInputs = document.querySelectorAll('input[name="target_mode"]');
const livestockRadios = document.querySelectorAll('input[name="livestock_id"]');
const manualInput = document.getElementById('manual_target_amount');
const manualDurationInput = document.getElementById('manual_duration');
const dateInput = document.getElementById('target_date');
const depositInput = document.getElementById('initial_deposit');
const depositDisplay = document.getElementById('initial_deposit_display');
const paymentMethodInput = document.querySelector('select[name="payment_method"]');
const transferInfo = document.getElementById('transfer-info');
const proofField = document.getElementById('proof-field');
const proofInput = document.getElementById('payment_proof');
const proofPreview = document.getElementById('proof-preview');
const durationInput = document.getElementById('duration_month');
const form = document.getElementById('tabungan-register-form');
const submitButton = document.getElementById('submit-tabungan-btn');
const errorSummary = document.getElementById('form-error-summary');
const draftKey = 'ltp_tabungan_registration_draft';
const remoteValidationState = { username: null, email: null };
let remoteValidationTimer = null;
let showValidationErrors = false;

function monthsToTarget() {
    const target = new Date(dateInput.value + 'T00:00:00');
    const today = new Date();
    let months = (target.getFullYear() - today.getFullYear()) * 12 + target.getMonth() - today.getMonth();
    if (target.getDate() > today.getDate()) months += 1;
    return Math.max(1, months || 1);
}
function selectedLivestock() {
    return document.querySelector('input[name="livestock_id"]:checked');
}
function currentTarget() {
    const mode = document.querySelector('input[name="target_mode"]:checked').value;
    if (mode === 'manual') return { title: 'Tentukan Tenor', amount: parseFloat(manualInput.value || 0) };
    const item = selectedLivestock();
    return { title: item ? item.dataset.name : 'Pilih Hewan Qurban', amount: item ? parseFloat(item.dataset.price || 0) : 0 };
}
function calculateSimulation() {
    const mode = document.querySelector('input[name="target_mode"]:checked').value;
    document.getElementById('livestock-target').classList.toggle('hidden', mode !== 'livestock');
    document.getElementById('manual-target').classList.toggle('hidden', mode !== 'manual');
    document.querySelectorAll('.target-mode').forEach(label => {
        const checked = label.querySelector('input').checked;
        label.classList.toggle('border-brand-primary', checked);
        label.classList.toggle('bg-brand-light/50', checked);
        label.classList.toggle('border-gray-200', !checked);
    });
    document.querySelectorAll('.livestock-card').forEach(label => {
        const checked = label.querySelector('input').checked;
        label.classList.toggle('border-brand-primary', checked);
        label.classList.toggle('ring-4', checked);
        label.classList.toggle('ring-brand-primary/10', checked);
    });
    const target = currentTarget();
    const months = monthsToTarget();
    const deposit = Math.max(0, parseFloat(depositInput.value || 0));
    const remaining = Math.max(0, target.amount - deposit);
    const progress = target.amount > 0 ? Math.min(100, Math.round((deposit / target.amount) * 100)) : 0;
    durationInput.value = months;
    document.getElementById('summary-title').textContent = target.title;
    document.getElementById('target-preview').textContent = rupiah(target.amount);
    document.getElementById('tenor-preview').textContent = months + ' bulan';
    document.getElementById('deposit-preview').textContent = rupiah(deposit);
    document.getElementById('progress-preview').textContent = progress + '%';
    document.getElementById('monthly-preview').textContent = rupiah(remaining / months);
    document.getElementById('remaining-preview').textContent = 'Sisa target ' + rupiah(remaining);
    document.getElementById('progress-bar').style.width = progress + '%';
}

function field(name) {
    return form.elements[name];
}

function setFieldError(name, message) {
    const input = field(name);
    const error = form.querySelector(`[data-error-for="${name}"]`);
    if (input && input.classList) {
        input.classList.toggle('border-red-400', Boolean(message));
        input.classList.toggle('bg-red-50', Boolean(message));
        input.classList.toggle('border-gray-200', !message);
    }
    if (error) {
        error.textContent = message || '';
        error.classList.toggle('hidden', !message);
    }
}

function currentTargetAmount() {
    return currentTarget().amount;
}

function digitsOnly(value) {
    return String(value || '').replace(/[^\d]/g, '');
}

function syncCurrencyInput(display, hidden) {
    const numeric = parseInt(digitsOnly(display.value) || '0', 10);
    hidden.value = numeric;
    display.value = numeric ? rupiah(numeric) : '';
}

function isTransferMethod() {
    return paymentMethodInput.value !== 'cash';
}

function localValidationErrors() {
    const errors = {};
    const username = field('username').value.trim().toLowerCase();
    const password = field('password').value;
    const confirm = field('password_confirm').value;
    const email = field('email').value.trim();
    const targetMode = document.querySelector('input[name="target_mode"]:checked').value;
    const targetAmount = currentTargetAmount();
    const deposit = parseFloat(depositInput.value || 0);
    const proof = proofInput.files[0];

    if (!field('full_name').value.trim()) errors.full_name = 'Nama lengkap wajib diisi.';
    if (!field('phone').value.trim()) errors.phone = 'No HP / WhatsApp wajib diisi.';
    if (!field('address').value.trim()) errors.address = 'Alamat wajib diisi.';
    if (!/^[a-z0-9._-]{4,50}$/.test(username)) errors.username = 'Username minimal 4 karakter dan hanya huruf, angka, titik, strip, atau underscore.';
    if (password.length < 8 || !/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) errors.password = 'Password minimal 8 karakter serta mengandung huruf dan angka.';
    if (confirm !== password) errors.password_confirm = 'Konfirmasi password tidak cocok.';
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Format email tidak valid.';
    if (targetMode === 'livestock' && !selectedLivestock()) errors.livestock_id = 'Pilih salah satu hewan qurban.';
    if (targetMode === 'manual' && targetAmount < 100000) errors.manual_target_amount = 'Target nominal manual minimal Rp 100.000.';
    if (!dateInput.value) errors.target_date = 'Target pelunasan wajib dipilih.';
    if (deposit < 10000) errors.initial_deposit = 'Nominal awal minimal Rp 10.000.';
    if (targetAmount > 0 && deposit > targetAmount) errors.initial_deposit = 'Nominal awal tidak boleh melebihi target tabungan.';
    if (isTransferMethod()) {
        if (!proof) errors.payment_proof = 'Upload bukti transfer wajib untuk pembayaran transfer.';
        if (proof && !['image/jpeg', 'image/png', 'image/webp'].includes(proof.type)) errors.payment_proof = 'File harus jpg, png, atau webp.';
        if (proof && proof.size > 2 * 1024 * 1024) errors.payment_proof = 'Ukuran file maksimal 2MB.';
    }
    if (remoteValidationState.username) errors.username = remoteValidationState.username;
    if (remoteValidationState.email) errors.email = remoteValidationState.email;

    return errors;
}

function renderValidation() {
    const errors = localValidationErrors();
    if (showValidationErrors) {
        ['full_name', 'phone', 'email', 'address', 'username', 'password', 'password_confirm', 'manual_target_amount', 'target_date', 'initial_deposit', 'payment_method', 'payment_proof'].forEach(name => {
            setFieldError(name, errors[name] || '');
        });
        const messages = Object.values(errors);
        errorSummary.textContent = messages.length ? 'Periksa kembali: ' + messages[0] : '';
        errorSummary.classList.toggle('hidden', messages.length === 0);
    }
    const messages = Object.values(errors);
    submitButton.disabled = messages.length > 0;
    return errors;
}

function saveDraft() {
    const data = {};
    Array.from(form.elements).forEach(el => {
        if (!el.name || el.type === 'password' || el.type === 'file' || el.name === 'csrf_token') return;
        if (el.type === 'radio') {
            if (el.checked) data[el.name] = el.value;
            return;
        }
        data[el.name] = el.value;
    });
    localStorage.setItem(draftKey, JSON.stringify(data));
}

function updatePaymentFields() {
    const showTransfer = isTransferMethod();
    transferInfo.classList.toggle('hidden', !showTransfer);
    proofField.classList.toggle('hidden', !showTransfer);
    proofInput.required = showTransfer;
    if (!showTransfer) {
        proofInput.value = '';
        proofPreview.classList.add('hidden');
        proofPreview.src = '';
    }
}

function restoreDraft() {
    const raw = localStorage.getItem(draftKey);
    if (!raw) return;
    try {
        const data = JSON.parse(raw);
        Object.keys(data).forEach(name => {
            const el = form.elements[name];
            if (!el) return;
            if (el instanceof RadioNodeList || (typeof el.length === 'number' && !el.tagName)) {
                Array.from(el).forEach(radio => radio.checked = radio.value === data[name]);
                return;
            }
            el.value = data[name];
        });
    } catch (e) {}
}

function validateRemote() {
    clearTimeout(remoteValidationTimer);
    remoteValidationTimer = setTimeout(() => {
        const username = field('username').value.trim().toLowerCase();
        const email = field('email').value.trim().toLowerCase();
        const params = new URLSearchParams();
        if (username.length >= 4) params.set('username', username);
        if (email) params.set('email', email);
        if (!params.toString()) {
            remoteValidationState.username = null;
            remoteValidationState.email = null;
            renderValidation();
            return;
        }
        fetch('/lautan-ternak-pantura/api/savings/validate_registration?' + params.toString())
            .then(response => response.ok ? response.json() : null)
            .then(payload => {
                remoteValidationState.username = payload && payload.errors ? (payload.errors.username || null) : null;
                remoteValidationState.email = payload && payload.errors ? (payload.errors.email || null) : null;
                renderValidation();
            })
            .catch(() => renderValidation());
    }, 350);
}

modeInputs.forEach(input => input.addEventListener('change', calculateSimulation));
livestockRadios.forEach(input => input.addEventListener('change', function() {
    if (this.checked) {
        manualInput.value = this.dataset.price;
    }
    calculateSimulation();
}));
[manualInput, dateInput, depositInput].forEach(input => input.addEventListener('input', calculateSimulation));

function syncDurationToDate() {
    if (!dateInput.value) return;
    const target = new Date(dateInput.value + 'T00:00:00');
    const today = new Date();
    let months = (target.getFullYear() - today.getFullYear()) * 12 + target.getMonth() - today.getMonth();
    if (target.getDate() > today.getDate()) months += 1;
    manualDurationInput.value = Math.max(1, months || 1);
}

manualDurationInput.addEventListener('input', function() {
    const months = parseInt(this.value || 1, 10);
    const today = new Date();
    today.setMonth(today.getMonth() + months);
    dateInput.value = today.toISOString().split('T')[0];
    calculateSimulation();
});

dateInput.addEventListener('input', function() {
    syncDurationToDate();
});
depositDisplay.addEventListener('input', function() {
    syncCurrencyInput(depositDisplay, depositInput);
    calculateSimulation();
    renderValidation();
    saveDraft();
});
paymentMethodInput.addEventListener('change', function() {
    updatePaymentFields();
    renderValidation();
    saveDraft();
});
proofInput.addEventListener('change', function() {
    const file = proofInput.files[0];
    if (file && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        proofPreview.src = URL.createObjectURL(file);
        proofPreview.classList.remove('hidden');
    } else {
        proofPreview.classList.add('hidden');
        proofPreview.src = '';
    }
    renderValidation();
});
restoreDraft();
form.addEventListener('input', function(event) {
    if (event.target.name === 'username') remoteValidationState.username = null;
    if (event.target.name === 'email') remoteValidationState.email = null;
    saveDraft();
    calculateSimulation();
    renderValidation();
    if (event.target.name === 'username' || event.target.name === 'email') validateRemote();
});
form.addEventListener('change', function(event) {
    saveDraft();
    calculateSimulation();
    renderValidation();
    if (event.target.name === 'target_mode' || event.target.name === 'livestock_id') validateRemote();
});
form.addEventListener('submit', function(event) {
    showValidationErrors = true;
    const errors = renderValidation();
    if (Object.keys(errors).length > 0) {
        event.preventDefault();
        const firstInvalid = Object.keys(errors)[0];
        const input = field(firstInvalid) || form.querySelector(`[name="${firstInvalid}"]`);
        if (input && input.focus) input.focus();
        return;
    }
    saveDraft();
    submitButton.disabled = true;
    submitButton.textContent = 'Memproses pendaftaran...';
});
window.addEventListener('pageshow', function() {
    restoreDraft();
    depositDisplay.value = depositInput.value ? rupiah(parseFloat(depositInput.value)) : '';
    updatePaymentFields();
    syncDurationToDate();
    calculateSimulation();
    renderValidation();
    validateRemote();
});
fetch('/lautan-ternak-pantura/api/savings/livestock').then(r => r.ok ? r.json() : null).then(() => calculateSimulation()).catch(calculateSimulation);
depositDisplay.value = depositInput.value ? rupiah(parseFloat(depositInput.value)) : '';
updatePaymentFields();
syncDurationToDate();
calculateSimulation();
renderValidation();
validateRemote();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
