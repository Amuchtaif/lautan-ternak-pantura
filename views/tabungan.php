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
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 leading-tight">Daftar tabungan qurban bersama kami.</h1>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed">Pilih hewan dari katalog aktif atau tentukan nominal manual, buat akun sendiri, lihat simulasi cicilan realtime, lalu langsung masuk ke dashboard tabungan.</p>
                <a href="#form-registrasi" class="mt-8 inline-flex items-center justify-center gap-3 rounded-full bg-brand-primary px-8 py-4 text-white font-black hover:bg-brand-dark transition">
                    <i class="fas fa-paw"></i> Daftar Tabungan Qurban
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
            <?php
            $packages = [
                'Domba' => [
                    ['name' => 'Domba Ekonomis (23-25 Kg)', 'price' => 2000000],
                    ['name' => 'Domba Standar (30-33 Kg)', 'price' => 3000000],
                    ['name' => 'Domba Ideal (43-45 Kg)', 'price' => 4500000],
                    ['name' => 'Domba Besar (55-60 Kg)', 'price' => 6000000],
                    ['name' => 'Domba Super (75-80 Kg)', 'price' => 8000000]
                ],
                'Sapi' => [
                    ['name' => 'Sapi Ekonomis (1/7 Bagian)', 'price' => 3000000],
                    ['name' => 'Sapi Medium (1/7 Bagian)', 'price' => 3500000],
                    ['name' => 'Sapi Ideal (1/7 Bagian)', 'price' => 4000000],
                    ['name' => 'Sapi Besar (1/7 Bagian)', 'price' => 5000000]
                ]
            ];
            ?>

            <form id="tabungan-register-form" action="/lautan-ternak-pantura/api/savings/register" method="POST" class="grid lg:grid-cols-12 gap-6" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- Left Column: Registration Sections -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Section 1: Informasi Program -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">1</span>
                            <span>Informasi Program</span>
                        </h3>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-gray-50 rounded-xl p-3.5 text-center">
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Tahun Hijriyah</p>
                                <p class="text-base font-black text-brand-primary mt-1">1448 H</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3.5 text-center">
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Tahun Masehi</p>
                                <p class="text-base font-black text-brand-primary mt-1">2027 M</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3.5 text-center">
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">No. Registrasi</p>
                                <p class="text-xs font-black text-amber-500 mt-2">LTP-2027-XXXXX</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Data Pequrban -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">2</span>
                            <span>Data Pequrban</span>
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Pequrban</label>
                                <input name="nama_pequrban" id="nama_pequrban" required placeholder="Contoh: Ahmad Abdullah" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium" oninput="syncCertificateName(this.value)">
                                <p data-error-for="nama_pequrban" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bin / Binti</label>
                                <input name="bin_binti" id="bin_binti" required placeholder="Contoh: bin Abdullah" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                                <p data-error-for="bin_binti" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nomor WhatsApp Aktif</label>
                                <input name="no_wa" id="no_wa" required placeholder="Contoh: 08123456789" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                                <p data-error-for="no_wa" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Alamat Lengkap</label>
                                <textarea name="alamat" id="alamat" required rows="3" placeholder="Alamat lengkap pequrban..." class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary resize-none font-medium"></textarea>
                                <p data-error-for="alamat" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                            </div>

                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <div class="md:col-span-2 my-2 border-t border-gray-100 pt-4">
                                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Informasi Akun</p>
                                    <p class="text-[10px] text-gray-400">Gunakan email dan password ini untuk login ke dashboard nanti.</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Alamat Email</label>
                                    <input name="email" id="email" type="email" required placeholder="Contoh: ahmad@gmail.com" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                                    <p data-error-for="email" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password</label>
                                    <input name="password" id="password" required minlength="8" type="password" placeholder="Password" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                                    <p data-error-for="password" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Konfirmasi Password</label>
                                    <input name="password_confirm" id="password_confirm" required minlength="8" type="password" placeholder="Ulangi password" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                                    <p data-error-for="password_confirm" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                                </div>
                            <?php else: ?>
                                <div class="md:col-span-2 bg-blue-50/60 p-4 rounded-xl border border-blue-100 flex items-center gap-3">
                                    <i class="fas fa-circle-user text-brand-primary text-xl"></i>
                                    <div class="text-xs font-bold text-blue-800">
                                        Terhubung dengan akun aktif Anda: <span class="font-black"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></span>. Pendaftaran akan otomatis terintegrasi.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Section 3: Pilih Paket Qurban -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-4 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">3</span>
                            <span>Pilih Paket Qurban</span>
                        </h3>
                        
                        <!-- Hidden inputs for package target -->
                        <input type="hidden" name="jenis_qurban" id="jenis_qurban" value="Domba">
                        <input type="hidden" name="paket_qurban" id="paket_qurban" value="Domba Ekonomis (23-25 Kg)">
                        <input type="hidden" name="harga_target" id="harga_target" value="2000000">

                        <!-- Tabs for Animal Types -->
                        <div class="flex border-b border-gray-100 mb-6">
                            <button type="button" onclick="switchCategory('Domba')" id="tab-domba" class="category-tab py-3 px-6 font-black text-sm border-b-2 border-brand-primary text-brand-primary outline-none transition flex items-center gap-2">
                                <i class="fas fa-cloud"></i> Kategori Domba
                            </button>
                            <button type="button" onclick="switchCategory('Sapi')" id="tab-sapi" class="category-tab py-3 px-6 font-black text-sm border-b-2 border-transparent text-gray-400 hover:text-gray-600 outline-none transition flex items-center gap-2">
                                <i class="fas fa-leaf"></i> Sapi (1/7 Bagian)
                            </button>
                        </div>

                        <!-- Domba Package Grid -->
                        <div id="container-domba" class="grid sm:grid-cols-2 gap-4">
                            <?php foreach ($packages['Domba'] as $index => $pkg): ?>
                                <label class="package-card cursor-pointer rounded-2xl border <?php echo $index === 0 ? 'border-brand-primary ring-4 ring-brand-primary/10 bg-brand-light/10' : 'border-gray-100 bg-white'; ?> p-5 transition-all duration-300 flex flex-col justify-between" id="card-domba-<?php echo $index; ?>">
                                    <input type="radio" name="select_paket" value="<?php echo htmlspecialchars($pkg['name']); ?>" data-jenis="Domba" data-price="<?php echo $pkg['price']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?> class="sr-only" onchange="selectPackage(this, 'card-domba-<?php echo $index; ?>')">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="w-8 h-8 rounded-lg bg-brand-light text-brand-primary flex items-center justify-center text-xs"><i class="fas fa-cloud"></i></span>
                                            <span class="text-[9px] font-black uppercase text-gray-400">Domba Premium</span>
                                        </div>
                                        <p class="font-black text-gray-900 leading-snug"><?php echo htmlspecialchars($pkg['name']); ?></p>
                                    </div>
                                    <p class="text-lg font-black text-brand-primary mt-4">Rp <?php echo number_format($pkg['price'], 0, ',', '.'); ?></p>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Sapi Package Grid -->
                        <div id="container-sapi" class="grid sm:grid-cols-2 gap-4 hidden">
                            <?php foreach ($packages['Sapi'] as $index => $pkg): ?>
                                <label class="package-card cursor-pointer rounded-2xl border border-gray-100 bg-white p-5 transition-all duration-300 flex flex-col justify-between" id="card-sapi-<?php echo $index; ?>">
                                    <input type="radio" name="select_paket" value="<?php echo htmlspecialchars($pkg['name']); ?>" data-jenis="Sapi" data-price="<?php echo $pkg['price']; ?>" class="sr-only" onchange="selectPackage(this, 'card-sapi-<?php echo $index; ?>')">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs"><i class="fas fa-leaf"></i></span>
                                            <span class="text-[9px] font-black uppercase text-gray-400">1/7 Sapi</span>
                                        </div>
                                        <p class="font-black text-gray-900 leading-snug"><?php echo htmlspecialchars($pkg['name']); ?></p>
                                    </div>
                                    <p class="text-lg font-black text-brand-primary mt-4">Rp <?php echo number_format($pkg['price'], 0, ',', '.'); ?></p>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Section 4: Pola Tabungan -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">4</span>
                            <span>Pola Tabungan</span>
                        </h3>
                        
                        <!-- Hidden fields for selected pattern and target setoran -->
                        <input type="hidden" name="pola_tabungan" id="pola_tabungan" value="Bulanan">
                        <input type="hidden" name="nominal_target_setoran" id="nominal_target_setoran" value="300000">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5">
                            <?php foreach (['Harian', 'Mingguan', 'Bulanan', 'Bebas Setor'] as $pola): ?>
                                <label class="pola-tab-btn rounded-xl border <?php echo $pola === 'Bulanan' ? 'border-brand-primary bg-brand-light/40 text-brand-primary' : 'border-gray-200 bg-white text-gray-500'; ?> py-3 px-2 text-center cursor-pointer font-bold text-sm transition block text-ellipsis overflow-hidden whitespace-nowrap">
                                    <input type="radio" name="select_pola" value="<?php echo $pola; ?>" <?php echo $pola === 'Bulanan' ? 'checked' : ''; ?> class="sr-only" onchange="selectPolaOption(this)">
                                    <?php echo $pola; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Nominal setoran inputs -->
                        <div id="container-nominal-harian" class="pola-nominal-container hidden">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target Setoran Harian (Rp)</label>
                            <input type="text" id="nominal_harian" placeholder="Contoh: Rp 10.000" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium" oninput="handleCurrencyInput(this)">
                        </div>
                        <div id="container-nominal-mingguan" class="pola-nominal-container hidden">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target Setoran Mingguan (Rp)</label>
                            <input type="text" id="nominal_mingguan" placeholder="Contoh: Rp 75.000" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium" oninput="handleCurrencyInput(this)">
                        </div>
                        <div id="container-nominal-bulanan" class="pola-nominal-container">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target Setoran Bulanan (Rp)</label>
                            <input type="text" id="nominal_bulanan" value="Rp 300.000" placeholder="Contoh: Rp 300.000" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium" oninput="handleCurrencyInput(this)">
                        </div>
                        <div id="container-nominal-bebas" class="pola-nominal-container hidden">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rencana Nominal per Setoran (Rp)</label>
                            <input type="text" id="bebas_setor" placeholder="Misal: Rp 100.000" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium" oninput="handleCurrencyInput(this)">
                        </div>
                        <p data-error-for="nominal_target_setoran" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                    </div>

                    <!-- Section 5: Target Pelunasan -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">5</span>
                            <span>Target Pelunasan</span>
                        </h3>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bulan Pelunasan</label>
                                <select name="target_lunas_bulan" id="target_lunas_bulan" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-bold text-gray-700" onchange="calculateSimulation()">
                                    <option value="Januari">Januari</option>
                                    <option value="Februari">Februari</option>
                                    <option value="Maret">Maret</option>
                                    <option value="April">April</option>
                                    <option value="Mei" selected>Mei (Rekomendasi - Idul Adha 1448 H)</option>
                                    <option value="Juni">Juni</option>
                                    <option value="Juli">Juli</option>
                                    <option value="Agustus">Agustus</option>
                                    <option value="September">September</option>
                                    <option value="Oktober">Oktober</option>
                                    <option value="November">November</option>
                                    <option value="Desember">Desember</option>
                                    <option value="Dzulhijjah">Dzulhijjah 1448 H</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tahun Pelunasan</label>
                                <input type="number" name="target_lunas_tahun" id="target_lunas_tahun" value="2027" readonly class="form-field w-full px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 outline-none text-gray-500 font-bold cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <!-- Section 6: Opsi Penyaluran Qurban -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">6</span>
                            <span>Opsi Penyaluran Qurban</span>
                        </h3>
                        <div class="space-y-3.5 mb-5">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio" name="opsi_penyaluran" value="Sembelih di LTP, daging diambil seluruhnya" checked class="mt-1 border-gray-300 text-brand-primary focus:ring-brand-primary" onchange="toggleAlamatPengiriman(false)">
                                <span class="text-sm font-bold text-gray-700 leading-tight">Sembelih di LTP, daging diambil seluruhnya</span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio" name="opsi_penyaluran" value="Sembelih di LTP, daging disalurkan oleh LTP kepada warga" class="mt-1 border-gray-300 text-brand-primary focus:ring-brand-primary" onchange="toggleAlamatPengiriman(false)">
                                <span class="text-sm font-bold text-gray-700 leading-tight">Sembelih di LTP, daging disalurkan oleh LTP kepada warga</span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio" name="opsi_penyaluran" value="Hewan hidup dikirim ke alamat pequrban" class="mt-1 border-gray-300 text-brand-primary focus:ring-brand-primary" onchange="toggleAlamatPengiriman(true)">
                                <span class="text-sm font-bold text-gray-700 leading-tight">Hewan hidup dikirim ke alamat pequrban</span>
                            </label>
                        </div>

                        <!-- Optional Live Animal Delivery Address -->
                        <div id="container-alamat-pengiriman" class="hidden mb-5">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Alamat Pengiriman Hewan Hidup</label>
                            <textarea name="alamat_pengiriman" id="alamat_pengiriman" rows="2" placeholder="Tulis alamat tujuan pengiriman lengkap..." class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary resize-none font-medium"></textarea>
                            <p data-error-for="alamat_pengiriman" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="hadir_penyembelihan" value="1" class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                                <span class="text-sm font-bold text-gray-700">Saya ingin hadir menyaksikan penyembelihan secara langsung</span>
                            </label>
                        </div>
                    </div>

                    <!-- Section 7: Data Sertifikat -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">7</span>
                            <span>Data Sertifikat</span>
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Pada Sertifikat</label>
                            <input name="nama_sertifikat" id="nama_sertifikat" placeholder="Default: Mengikuti Nama Pequrban" class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary font-medium">
                            <p class="mt-2 text-xs text-gray-400">Contoh: Keluarga Bapak Ahmad, Hamba Allah, atau Atas Nama Orang Tua (dapat disesuaikan).</p>
                        </div>
                    </div>

                    <!-- Section 8: Catatan Tambahan -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">8</span>
                            <span>Catatan Tambahan</span>
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Catatan Khusus</label>
                            <textarea name="catatan" id="catatan" rows="3" placeholder="Contoh: Qurban atas nama orang tua. Mohon dihubungi sebelum pengiriman." class="form-field w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-primary resize-none font-medium"></textarea>
                        </div>
                    </div>

                    <!-- Section 9: Persetujuan Peserta -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-5 flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-light text-brand-primary text-xs font-bold">9</span>
                            <span>Persetujuan Peserta</span>
                        </h3>
                        <div>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="persetujuan" id="persetujuan" value="1" class="mt-1 rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                                <span class="text-sm font-bold text-gray-700 leading-relaxed">
                                    Saya menyetujui sistem Double Account Tabungan Qurban LTP dan memahami bahwa dana yang ditabung digunakan khusus untuk program qurban sesuai ketentuan yang berlaku.
                                </span>
                            </label>
                            <p data-error-for="persetujuan" class="mt-1 text-xs font-bold text-red-600 hidden"></p>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Panel Simulasi (Sticky) -->
                <aside class="lg:col-span-5">
                    <div class="lg:sticky lg:top-24 rounded-2xl border border-gray-100 bg-white shadow-xl shadow-gray-900/5 p-6 space-y-6">
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Panel Simulasi</p>
                            <h3 id="summary-title" class="text-2xl font-black text-gray-900 mt-1">Domba Ekonomis</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Target Harga</p>
                                <p id="target-preview" class="font-black text-gray-900 mt-1 text-lg">Rp 2.000.000</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Tenor Tabungan</p>
                                <p id="tenor-preview" class="font-black text-gray-900 mt-1 text-lg">10 Bulan</p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gradient-to-br from-brand-primary to-brand-dark p-6 text-white shadow-xl shadow-brand-primary/20">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white"><i class="fas fa-coins text-xs"></i></span>
                                <p class="text-xs text-blue-100 font-black uppercase tracking-wider">Estimasi Target Setoran</p>
                            </div>
                            <p id="monthly-preview" class="text-4xl font-black mt-1">Rp 200.000</p>
                            <p id="remaining-preview" class="text-xs text-blue-100/80 mt-2 font-medium">Pola: Bulanan | Target Lunas: Mei 2027</p>
                        </div>

                        <div id="form-error-summary" class="hidden rounded-xl bg-red-50 border border-red-100 p-4 text-xs font-bold text-red-700"></div>

                        <button id="submit-tabungan-btn" type="submit" class="w-full rounded-xl bg-brand-primary py-4 text-white font-black hover:bg-brand-dark transition disabled:opacity-60 disabled:cursor-not-allowed text-center flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span>Daftar Tabungan Qurban</span>
                        </button>
                        
                        <p class="text-[10px] text-gray-400 text-center font-semibold uppercase leading-tight">Pendaftaran Awal Bebas Biaya (Rp 0). Mulai menabung bertahap lewat dashboard setelah akun Anda aktif.</p>
                    </div>
                </aside>
            </form>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-14 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <h2 class="text-3xl font-black text-gray-900 text-center mb-8">FAQ Tabungan Qurban</h2>
            <?php foreach ([
                ['Apakah ada biaya pendaftaran awal atau setoran awal?', 'Tidak ada. Pendaftaran awal di platform kami sepenuhnya bebas biaya (Rp 0). Anda dapat mendaftar, membuat rencana target, dan setelah akun aktif Anda dapat mulai menyetor saldo tabungan secara fleksibel melalui dashboard.'],
                ['Bagaimana cara memantau riwayat setoran dan progress tabungan?', 'Setelah pendaftaran sukses, Anda akan dialihkan ke dashboard customer. Di sana, grafik progress tabungan (Total Target, Total Tersimpan, Kekurangan) dan list riwayat setoran uang akan ditampilkan secara real-time.'],
                ['Apakah nama pada sertifikat qurban bisa berbeda dengan nama pendaftar?', 'Bisa. Pada bagian formulir data sertifikat, Anda bisa menentukan nama kustom (seperti nama orang tua, keluarga, atau hamba Allah) yang akan dicetak di sertifikat resmi qurban.'],
                ['Apakah saya bisa menyaksikan langsung proses penyembelihan?', 'Bisa. Kami memiliki opsi "Ingin hadir menyaksikan penyembelihan" yang dapat Anda centang saat melakukan pendaftaran untuk memudahkan koordinasi pada hari H Idul Adha.']
            ] as $faq): ?>
                <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                    <h3 class="font-black text-gray-900"><?php echo htmlspecialchars($faq[0]); ?></h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed"><?php echo htmlspecialchars($faq[1]); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
const rupiah = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);

function handleCurrencyInput(input) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
    } else {
        const num = parseInt(value, 10);
        input.value = rupiah(num);
    }
    syncPolaNominal();
}

// Switch between animal categories (Domba vs Sapi)
function switchCategory(cat) {
    document.getElementById('container-domba').classList.toggle('hidden', cat !== 'Domba');
    document.getElementById('container-sapi').classList.toggle('hidden', cat !== 'Sapi');

    const tabDomba = document.getElementById('tab-domba');
    const tabSapi = document.getElementById('tab-sapi');

    if (cat === 'Domba') {
        tabDomba.className = 'category-tab py-3 px-6 font-black text-sm border-b-2 border-brand-primary text-brand-primary outline-none transition flex items-center gap-2';
        tabSapi.className = 'category-tab py-3 px-6 font-black text-sm border-b-2 border-transparent text-gray-400 hover:text-gray-600 outline-none transition flex items-center gap-2';
        // Auto-select first Domba option
        document.querySelector('#container-domba input[type="radio"]').click();
    } else {
        tabSapi.className = 'category-tab py-3 px-6 font-black text-sm border-b-2 border-brand-primary text-brand-primary outline-none transition flex items-center gap-2';
        tabDomba.className = 'category-tab py-3 px-6 font-black text-sm border-b-2 border-transparent text-gray-400 hover:text-gray-600 outline-none transition flex items-center gap-2';
        // Auto-select first Sapi option
        document.querySelector('#container-sapi input[type="radio"]').click();
    }
}

// Select a package from the cards
function selectPackage(radio, cardId) {
    document.getElementById('jenis_qurban').value = radio.dataset.jenis;
    document.getElementById('paket_qurban').value = radio.value;
    document.getElementById('harga_target').value = radio.dataset.price;

    // Remove selection ring/bg from all cards in the current category
    const parentContainer = radio.closest('.grid');
    parentContainer.querySelectorAll('.package-card').forEach(card => {
        card.className = 'package-card cursor-pointer rounded-2xl border border-gray-100 bg-white p-5 transition-all duration-300 flex flex-col justify-between';
    });

    // Add selection highlights to active card
    const activeCard = document.getElementById(cardId);
    if (activeCard) {
        activeCard.className = 'package-card cursor-pointer rounded-2xl border border-brand-primary ring-4 ring-brand-primary/10 bg-brand-light/10 p-5 transition-all duration-300 flex flex-col justify-between';
    }

    calculateSimulation();
}

// Switch Pola Tabungan
function selectPolaOption(radio) {
    document.getElementById('pola_tabungan').value = radio.value;

    // Toggle active classes on pattern buttons
    const buttons = radio.closest('.grid').querySelectorAll('.pola-tab-btn');
    buttons.forEach(btn => {
        const checked = btn.querySelector('input').checked;
        if (checked) {
            btn.className = 'pola-tab-btn rounded-xl border border-brand-primary bg-brand-light/40 text-brand-primary py-3 px-2 text-center cursor-pointer font-bold text-sm transition block text-ellipsis overflow-hidden whitespace-nowrap';
        } else {
            btn.className = 'pola-tab-btn rounded-xl border border-gray-200 bg-white text-gray-500 py-3 px-2 text-center cursor-pointer font-bold text-sm transition block text-ellipsis overflow-hidden whitespace-nowrap';
        }
    });

    // Hide all nominal containers
    document.querySelectorAll('.pola-nominal-container').forEach(c => c.classList.add('hidden'));

    // Show correct input container
    if (radio.value === 'Harian') {
        document.getElementById('container-nominal-harian').classList.remove('hidden');
    } else if (radio.value === 'Mingguan') {
        document.getElementById('container-nominal-mingguan').classList.remove('hidden');
    } else if (radio.value === 'Bulanan') {
        document.getElementById('container-nominal-bulanan').classList.remove('hidden');
    } else {
        document.getElementById('container-nominal-bebas').classList.remove('hidden');
    }

    syncPolaNominal();
}

// Sync current pattern's nominal value to the form state
function syncPolaNominal() {
    const pola = document.getElementById('pola_tabungan').value;
    let rawVal = '';

    if (pola === 'Harian') {
        rawVal = document.getElementById('nominal_harian').value;
    } else if (pola === 'Mingguan') {
        rawVal = document.getElementById('nominal_mingguan').value;
    } else if (pola === 'Bulanan') {
        rawVal = document.getElementById('nominal_bulanan').value;
    } else {
        rawVal = document.getElementById('bebas_setor').value;
    }

    const cleaned = (rawVal || '').replace(/\D/g, '');
    const nominal = parseInt(cleaned, 10) || 0;

    document.getElementById('nominal_target_setoran').value = nominal;
    calculateSimulation();
}

// Toggle delivery address field visibility
function toggleAlamatPengiriman(show) {
    document.getElementById('container-alamat-pengiriman').classList.toggle('hidden', !show);
    if (!show) {
        document.getElementById('alamat_pengiriman').value = '';
    }
}

// Auto-sync certificate name default value
let certificateNameEdited = false;
document.getElementById('nama_sertifikat').addEventListener('focus', function() {
    certificateNameEdited = true;
});
function syncCertificateName(name) {
    if (!certificateNameEdited) {
        document.getElementById('nama_sertifikat').placeholder = name ? name : "Default: Mengikuti Nama Pequrban";
    }
}

// Compute tenor months and simulate setoran targets in real-time
function calculateSimulation() {
    const targetPrice = parseFloat(document.getElementById('harga_target').value) || 0;
    const pola = document.getElementById('pola_tabungan').value;
    const selectedBulan = document.getElementById('target_lunas_bulan').value;
    const selectedTahun = parseInt(document.getElementById('target_lunas_tahun').value) || 2027;

    // Tenor calculation based on target month
    const monthMap = {
        'Januari': 0, 'Februari': 1, 'Maret': 2, 'April': 3, 'Mei': 4, 'Juni': 5,
        'Juli': 6, 'Agustus': 7, 'September': 8, 'Oktober': 9, 'November': 10, 'Desember': 11,
        'Dzulhijjah': 4 // Dzulhijjah 1448 H is in May 2027
    };
    const targetMonthIndex = monthMap[selectedBulan] ?? 4;
    
    const today = new Date();
    const targetDate = new Date(selectedTahun, targetMonthIndex, 25);
    
    let tenorMonths = 1;
    if (targetDate > today) {
        const diffYears = targetDate.getFullYear() - today.getFullYear();
        const diffMonths = targetDate.getMonth() - today.getMonth();
        tenorMonths = Math.max(1, (diffYears * 12) + diffMonths + (targetDate.getDate() > today.getDate() ? 1 : 0));
    }

    // Update previews
    document.getElementById('summary-title').textContent = document.getElementById('paket_qurban').value;
    document.getElementById('target-preview').textContent = rupiah(targetPrice);
    document.getElementById('tenor-preview').textContent = tenorMonths + ' Bulan';

    // Target setoran display
    let nominalSetoran = parseFloat(document.getElementById('nominal_target_setoran').value) || 0;
    
    // If setoran is empty or 0, suggest a value based on target price divided by tenor
    let suggestedAmountText = '';
    if (nominalSetoran <= 0) {
        if (pola === 'Harian') {
            nominalSetoran = Math.round(targetPrice / (tenorMonths * 30));
        } else if (pola === 'Mingguan') {
            nominalSetoran = Math.round(targetPrice / (tenorMonths * 4.3));
        } else if (pola === 'Bulanan') {
            nominalSetoran = Math.round(targetPrice / tenorMonths);
        } else {
            nominalSetoran = 0; // Bebas setor can be 0
        }
        if (nominalSetoran > 0) {
            suggestedAmountText = ' <span class="text-xs font-semibold text-blue-100/80 block sm:inline mt-1 sm:mt-0 sm:ml-1.5 font-sans">(Rekomendasi)</span>';
        }
    }

    document.getElementById('monthly-preview').innerHTML = rupiah(nominalSetoran) + suggestedAmountText;
    document.getElementById('remaining-preview').textContent = `Pola: ${pola} | Target Lunas: ${selectedBulan} ${selectedTahun}`;
}

// Validation logic
const form = document.getElementById('tabungan-register-form');
const submitButton = document.getElementById('submit-tabungan-btn');
const errorSummary = document.getElementById('form-error-summary');
let showValidationErrors = false;

function setFieldError(name, message) {
    const input = document.getElementById(name);
    const errorEl = form.querySelector(`[data-error-for="${name}"]`);
    if (input) {
        input.classList.toggle('border-red-400', Boolean(message));
        input.classList.toggle('bg-red-50', Boolean(message));
        input.classList.toggle('border-gray-200', !message);
    }
    if (errorEl) {
        errorEl.textContent = message || '';
        errorEl.classList.toggle('hidden', !message);
    }
}

function checkValidation() {
    const errors = {};
    
    const namaPequrban = (document.getElementById('nama_pequrban').value || '').trim();
    const binBinti = (document.getElementById('bin_binti').value || '').trim();
    const noWa = (document.getElementById('no_wa').value || '').trim().replace(/[^0-9]/g, '');
    const alamat = (document.getElementById('alamat').value || '').trim();
    const persetujuan = document.getElementById('persetujuan').checked;
    
    if (!namaPequrban) errors.nama_pequrban = 'Nama pequrban wajib diisi.';
    if (!binBinti) errors.bin_binti = 'Bin / Binti wajib diisi.';
    if (!noWa) errors.no_wa = 'Nomor WhatsApp wajib diisi.';
    else if (!/^(08|62)[0-9]{8,14}$/.test(noWa)) errors.no_wa = 'Format no WA tidak valid (harus angka Indonesia, e.g. 0812...).';
    
    if (!alamat) errors.alamat = 'Alamat lengkap wajib diisi.';

    // Email/Password check only if not logged in
    const emailField = document.getElementById('email');
    if (emailField) {
        const email = emailField.value.trim();
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;

        if (!email) errors.email = 'Email wajib diisi.';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Format email tidak valid.';

        if (!password || password.length < 8 || !/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) {
            errors.password = 'Password minimal 8 karakter serta mengandung huruf dan angka.';
        }
        if (password !== confirm) {
            errors.password_confirm = 'Konfirmasi password tidak cocok.';
        }
    }

    const pola = document.getElementById('pola_tabungan').value;
    const nominal = parseFloat(document.getElementById('nominal_target_setoran').value) || 0;
    if (pola !== 'Bebas Setor' && nominal <= 0) {
        errors.nominal_target_setoran = 'Masukkan nominal setoran target yang valid.';
    }

    const deliveryRadio = form.querySelector('input[name="opsi_penyaluran"]:checked');
    if (deliveryRadio && deliveryRadio.value === 'Hewan hidup dikirim ke alamat pequrban') {
        const shippingAddress = (document.getElementById('alamat_pengiriman').value || '').trim();
        if (!shippingAddress) {
            errors.alamat_pengiriman = 'Alamat pengiriman hewan hidup wajib diisi.';
        }
    }

    if (!persetujuan) {
        errors.persetujuan = 'Anda harus menyetujui sistem Double Account Tabungan Qurban.';
    }

    return errors;
}

function renderValidation() {
    const errors = checkValidation();
    if (showValidationErrors) {
        const fieldsToValidate = ['nama_pequrban', 'bin_binti', 'no_wa', 'alamat', 'email', 'password', 'password_confirm', 'nominal_target_setoran', 'alamat_pengiriman', 'persetujuan'];
        fieldsToValidate.forEach(name => {
            setFieldError(name, errors[name] || '');
        });
        
        const messages = Object.values(errors);
        errorSummary.textContent = messages.length ? 'Periksa kembali formulir: ' + messages[0] : '';
        errorSummary.classList.toggle('hidden', messages.length === 0);
    }
    return errors;
}

form.addEventListener('input', function(event) {
    renderValidation();
});

form.addEventListener('change', function(event) {
    renderValidation();
});

form.addEventListener('submit', function(event) {
    showValidationErrors = true;
    const errors = renderValidation();
    if (Object.keys(errors).length > 0) {
        event.preventDefault();
        const firstInvalid = Object.keys(errors)[0];
        const input = document.getElementById(firstInvalid);
        if (input && input.focus) {
            input.focus();
        }
        return;
    }
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Memproses Pendaftaran...';
});

// Initial load
window.addEventListener('load', function() {
    calculateSimulation();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
