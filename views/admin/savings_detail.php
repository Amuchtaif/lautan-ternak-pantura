<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<?php
    $statusColors = [
        'active'    => ['bg' => 'bg-emerald-50',  'text' => 'text-emerald-600', 'ring' => 'ring-emerald-200', 'dot' => 'bg-emerald-500'],
        'completed' => ['bg' => 'bg-blue-50',     'text' => 'text-blue-600',    'ring' => 'ring-blue-200',    'dot' => 'bg-blue-500'],
        'overdue'   => ['bg' => 'bg-red-50',       'text' => 'text-red-600',     'ring' => 'ring-red-200',     'dot' => 'bg-red-500'],
        'cancelled' => ['bg' => 'bg-gray-100',     'text' => 'text-gray-500',    'ring' => 'ring-gray-200',    'dot' => 'bg-gray-400'],
    ];
    $sc = $statusColors[$plan['status']] ?? $statusColors['active'];
    $remaining = max(0, $plan['target_amount'] - $plan['current_amount']);
    $progressClamped = min(100, (float)$progress);
    $verifiedCount = count(array_filter($transactions, fn($t) => $t['transaction_status'] === 'verified'));
    $pendingCount  = count(array_filter($transactions, fn($t) => $t['transaction_status'] === 'pending'));
?>
<style>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.shimmer-bar {
    position: relative;
    overflow: hidden;
}
.shimmer-bar::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
    animation: shimmer 2.5s infinite linear;
}
</style>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden bg-gray-50/60">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-6 lg:p-8 space-y-6 flex-grow">

        <!-- ── Breadcrumb ─────────────────────────────────── -->
        <div class="flex items-center gap-2 text-sm">
            <a href="/lautan-ternak-pantura/savings/management" class="font-bold text-gray-400 hover:text-brand-primary transition-colors flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-xs"></i> Tabungan Qurban
            </a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="font-black text-gray-700">Detail Tabungan</span>
        </div>

        <!-- ── Hero Card ──────────────────────────────────── -->
        <div class="relative bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Decorative gradient -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-brand-primary/5 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-brand-secondary/5 blur-3xl"></div>
            </div>

            <div class="relative p-8">
                <!-- Top row: identity + status -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5 mb-8">
                    <div class="flex items-center gap-5">
                        <!-- Avatar -->
                        <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-black text-xl shadow-lg shadow-brand-primary/20 flex-shrink-0">
                            <?php echo mb_strtoupper(mb_substr($plan['customer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-brand-primary uppercase tracking-[0.2em] mb-1"><?php echo htmlspecialchars($plan['plan_code']); ?></p>
                            <h1 class="text-2xl font-black text-gray-900 leading-tight"><?php echo htmlspecialchars($plan['customer_name']); ?></h1>
                            <p class="text-sm font-bold text-gray-400 mt-0.5 flex items-center gap-1.5">
                                <i class="fas fa-paw text-xs"></i>
                                <?php echo htmlspecialchars($plan['livestock_target']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest ring-1 <?php echo $sc['bg'] . ' ' . $sc['text'] . ' ' . $sc['ring']; ?>">
                            <span class="h-1.5 w-1.5 rounded-full <?php echo $sc['dot']; ?>"></span>
                            <?php echo ucfirst($plan['status']); ?>
                        </span>
                        <button type="button" onclick="openManualDepositModal()"
                            class="inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-2xl font-black text-sm shadow-lg shadow-brand-primary/25 hover:bg-brand-dark transition-all active:scale-95">
                            <i class="fas fa-plus"></i> Tambah Setoran
                        </button>
                    </div>
                </div>

                <!-- Stat cards row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-brand-primary/5 border border-brand-primary/10 rounded-2xl p-5">
                        <p class="text-[10px] font-black text-brand-primary uppercase tracking-widest mb-2">Saldo Terkumpul</p>
                        <p class="text-2xl font-black text-brand-primary leading-none">Rp <?php echo number_format($plan['current_amount'], 0, ',', '.'); ?></p>
                        <p class="text-[10px] font-bold text-brand-primary/50 mt-1.5"><?php echo $verifiedCount; ?> setoran terverifikasi</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Target Hewan</p>
                        <p class="text-2xl font-black text-gray-900 leading-none">Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?></p>
                        <p class="text-[10px] font-bold text-gray-400 mt-1.5"><?php echo htmlspecialchars($plan['livestock_target']); ?></p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-2">Sisa Kekurangan</p>
                        <p class="text-2xl font-black text-amber-600 leading-none">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></p>
                        <p class="text-[10px] font-bold text-amber-400 mt-1.5">Cicilan Rp <?php echo number_format($plan['monthly_target'], 0, ',', '.'); ?>/bln</p>
                    </div>
                    <div class="bg-<?php echo $pendingCount > 0 ? 'orange' : 'slate'; ?>-50 border border-<?php echo $pendingCount > 0 ? 'orange' : 'slate'; ?>-100 rounded-2xl p-5">
                        <p class="text-[10px] font-black text-<?php echo $pendingCount > 0 ? 'orange-500' : 'slate-400'; ?> uppercase tracking-widest mb-2">Menunggu Verifikasi</p>
                        <p class="text-2xl font-black text-<?php echo $pendingCount > 0 ? 'orange-600' : 'slate-500'; ?> leading-none"><?php echo $pendingCount; ?></p>
                        <p class="text-[10px] font-bold text-<?php echo $pendingCount > 0 ? 'orange-400' : 'slate-400'; ?> mt-1.5">transaksi pending</p>
                    </div>
                </div>

                <!-- Progress bar -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Progres Tabungan</span>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-brand-primary/10 text-brand-primary shadow-sm shadow-brand-primary/5">
                                <?php echo $progress; ?>%
                            </span>
                        </div>
                    </div>
                    <div class="h-5 w-full bg-gray-100/70 border border-gray-200/50 rounded-full p-[3px] shadow-inner relative flex items-center">
                        <div class="h-full rounded-full transition-all duration-1000 relative shadow-md shadow-brand-primary/20 flex items-center justify-end shimmer-bar"
                            style="width:<?php echo $progressClamped; ?>%; background: linear-gradient(90deg, #0d5bb5 0%, #00a3e0 100%);">
                            
                            <!-- Glowing stripe pattern -->
                            <div class="absolute inset-0 opacity-20" style="background: repeating-linear-gradient(45deg,transparent,transparent 8px,rgba(255,255,255,0.4) 8px,rgba(255,255,255,0.4) 16px)"></div>
                            
                            <!-- Leading glow indicator dot -->
                            <?php if ($progressClamped > 0): ?>
                                <span class="absolute right-[1px] h-3.5 w-3.5 rounded-full bg-white shadow-md shadow-brand-primary/50 flex items-center justify-center border border-brand-primary/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-brand-primary animate-ping"></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex justify-between mt-2 px-1">
                        <span class="text-[10px] font-bold text-gray-400">Rp 0</span>
                        <span class="text-[10px] font-bold text-gray-400">Target Pelunasan: Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Fetch associated qurban registration details if they exist
        $registration = null;
        if (!empty($plan['plan_code'])) {
            $stmtReg = $db->prepare("SELECT * FROM qurban_registrations WHERE nomor_registrasi = ?");
            $stmtReg->execute([$plan['plan_code']]);
            $registration = $stmtReg->fetch(PDO::FETCH_ASSOC);
        }
        ?>
        <?php if ($registration): ?>
            <!-- ── Detail Registrasi Qurban Section ──────────────── -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary">
                        <i class="fas fa-file-contract text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 leading-tight">Detail Registrasi & Penyaluran</h2>
                        <p class="text-xs font-bold text-gray-400 mt-0.5">Informasi lengkap peserta qurban berdasarkan formulir pendaftaran.</p>
                    </div>
                </div>
                <div class="p-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Column 1: Pequrban info -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Nama Lengkap & Bin/Binti</p>
                            <p class="text-sm font-black text-gray-900"><?php echo htmlspecialchars($registration['nama_pequrban']); ?></p>
                            <p class="text-xs text-gray-500 font-bold mt-1"><?php echo htmlspecialchars($registration['bin_binti']); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Nomor WhatsApp</p>
                            <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $registration['no_wa']); ?>" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-black text-emerald-600 hover:text-emerald-700 transition">
                                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($registration['no_wa']); ?>
                            </a>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Alamat Lengkap</p>
                            <p class="text-xs font-bold text-gray-600 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($registration['alamat']); ?></p>
                        </div>
                    </div>

                    <!-- Column 2: Distribution option -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Opsi Penyaluran Qurban</p>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-blue-50 text-blue-600 border border-blue-100">
                                <i class="fas fa-truck text-[10px]"></i> <?php echo htmlspecialchars($registration['opsi_penyaluran']); ?>
                            </span>
                        </div>
                        <?php if ($registration['opsi_penyaluran'] === 'Hewan hidup dikirim ke alamat pequrban'): ?>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Alamat Pengiriman Hewan</p>
                                <p class="text-xs font-bold text-gray-600 leading-relaxed"><?php echo htmlspecialchars($registration['alamat_pengiriman']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Kehadiran Penyembelihan</p>
                            <?php if ($registration['hadir_penyembelihan']): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    <i class="fas fa-eye text-[8px]"></i> Ingin Hadir Langsung
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-gray-50 text-gray-500 border border-gray-100">
                                    <i class="fas fa-eye-slash text-[8px]"></i> Tidak Hadir
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Column 3: Cert + Agreement -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Nama Pada Sertifikat</p>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-purple-50 text-purple-600 border border-purple-100">
                                <i class="fas fa-award text-[10px]"></i> <?php echo htmlspecialchars($registration['nama_sertifikat']); ?>
                            </span>
                        </div>
                        <?php if (!empty($registration['catatan'])): ?>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Catatan Khusus</p>
                                <p class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-100 p-3 rounded-2xl leading-relaxed italic"><?php echo htmlspecialchars($registration['catatan']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">Persetujuan Double Account</p>
                            <?php if ($registration['persetujuan']): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    <i class="fas fa-check-double text-[8px]"></i> Telah Disetujui
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase bg-red-50 text-red-500 border border-red-100">
                                    <i class="fas fa-exclamation-triangle text-[8px]"></i> Belum Disetujui
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Transaction Table ──────────────────────────── -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-gray-900">Riwayat Transaksi</h2>
                    <p class="text-xs font-bold text-gray-400 mt-0.5"><?php echo count($transactions); ?> transaksi total</p>
                </div>
                <button type="button" onclick="openManualDepositModal()"
                    class="inline-flex items-center gap-2 bg-brand-primary/8 border border-brand-primary/20 text-brand-primary px-4 py-2 rounded-xl font-black text-sm hover:bg-brand-primary hover:text-white transition-all">
                    <i class="fas fa-plus-circle"></i> <span class="hidden sm:inline">Setoran Manual</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nominal</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Metode</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Bukti</th>
                            <th class="px-4 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($transactions as $trx): ?>
                            <?php
                                $isPending  = $trx['transaction_status'] === 'pending';
                                $isVerified = $trx['transaction_status'] === 'verified';
                                $isRejected = $trx['transaction_status'] === 'rejected';
                                // Parse deposit date from notes
                                $depositDateDisplay = '';
                                if (preg_match('/Tanggal setor:\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $trx['notes'] ?? '', $matches)) {
                                    $depositDateDisplay = $matches[1];
                                }
                            ?>
                            <tr class="group hover:bg-brand-primary/[0.02] transition-colors">
                                <!-- Date -->
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-800"><?php echo date('d M Y', strtotime($trx['created_at'])); ?></p>
                                    <p class="text-[10px] font-bold text-gray-400"><?php echo date('H:i', strtotime($trx['created_at'])); ?>
                                        <?php if ($depositDateDisplay && $depositDateDisplay !== date('Y-m-d', strtotime($trx['created_at']))): ?>
                                            · Setor <?php echo date('d M Y', strtotime($depositDateDisplay)); ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                                <!-- Amount -->
                                <td class="px-4 py-5">
                                    <span class="text-base font-black <?php echo $isRejected ? 'text-gray-400 line-through' : 'text-gray-900'; ?>">
                                        Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <!-- Method -->
                                <td class="px-4 py-5">
                                    <?php
                                        $method = $trx['payment_method'];
                                        $methodIcon = $method === 'cash' ? 'fa-money-bill-wave' : 'fa-building-columns';
                                        $methodColor = $method === 'cash' ? 'text-emerald-500' : 'text-blue-500';
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600">
                                        <i class="fas <?php echo $methodIcon; ?> <?php echo $methodColor; ?>"></i>
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $method))); ?>
                                    </span>
                                </td>
                                <!-- Proof -->
                                <td class="px-4 py-5">
                                    <?php if (!empty($trx['payment_proof']) && strpos($trx['payment_proof'], '/storage/uploads/') !== false): ?>
                                        <button type="button" onclick="openProofLightbox('<?php echo htmlspecialchars($trx['payment_proof'], ENT_QUOTES); ?>')"
                                            class="group/btn inline-flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-1.5 pr-3 hover:border-brand-primary/30 hover:bg-brand-primary/5 transition">
                                            <img src="<?php echo htmlspecialchars($trx['payment_proof']); ?>" alt="Bukti" class="h-10 w-10 rounded-lg object-cover">
                                            <span class="text-xs font-black text-brand-primary"><i class="fas fa-expand-alt mr-0.5"></i> Lihat</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-400">
                                            <i class="fas fa-ban text-gray-300 text-[10px]"></i>
                                            <?php echo $method === 'cash' ? 'Tunai' : 'Tidak ada'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <!-- Status -->
                                <td class="px-4 py-5">
                                    <?php if ($isVerified): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">
                                            <i class="fas fa-check-circle text-[8px]"></i> Terverifikasi
                                        </span>
                                    <?php elseif ($isRejected): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-red-50 text-red-500 ring-1 ring-red-200">
                                            <i class="fas fa-times-circle text-[8px]"></i> Ditolak
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-amber-50 text-amber-600 ring-1 ring-amber-200 animate-pulse">
                                            <i class="fas fa-clock text-[8px]"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <!-- Actions -->
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($isVerified): ?>
                                            <a href="/lautan-ternak-pantura/savings/receipt/<?php echo (int)$trx['id']; ?>" target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100 text-gray-500 hover:bg-brand-primary hover:text-white hover:border-brand-primary font-bold text-xs transition-all">
                                                <i class="fas fa-print"></i> Kuitansi
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($isPending): ?>
                                            <button type="button" onclick="openApprovalModal(<?php echo (int)$trx['id']; ?>, <?php echo htmlspecialchars(json_encode($trx)); ?>)"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-blue-500 text-white hover:bg-blue-600 font-black text-xs transition-all shadow-sm shadow-blue-500/30">
                                                <i class="fas fa-gavel"></i> Proses
                                            </button>
                                        <?php elseif (!$isVerified): ?>
                                            <span class="text-[10px] text-gray-400 font-bold">
                                                <?php echo $trx['verified_at'] ? date('d M Y', strtotime($trx['verified_at'])) : '—'; ?>
                                            </span>
                                        <?php endif; ?>
                                        <!-- Delete button -->
                                        <button type="button"
                                            onclick="confirmDeleteTransaction(<?php echo (int)$trx['id']; ?>, 'Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?>', '<?php echo $trx['transaction_status']; ?>')"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-xl bg-red-50 border border-red-100 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all"
                                            title="Hapus transaksi">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 text-3xl">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <p class="font-bold text-gray-400">Belum ada transaksi setoran</p>
                                        <button type="button" onclick="openManualDepositModal()" class="text-brand-primary font-black text-sm hover:underline">
                                            + Tambah setoran pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Proof Lightbox -->
<div id="proof-lightbox" class="fixed inset-0 z-[1200] hidden bg-gray-900/80 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="flex h-full w-full flex-col transform scale-95 transition-all duration-300">
        <div class="mb-3 flex items-center justify-between text-white">
            <p class="text-sm font-black uppercase tracking-widest">Preview Bukti Transfer</p>
            <div class="flex gap-2">
                <button type="button" onclick="toggleProofFullscreen()" class="rounded-xl bg-white/10 px-4 py-2 font-bold hover:bg-white/20"><i class="fas fa-expand mr-2"></i>Fullscreen</button>
                <button type="button" onclick="closeProofLightbox()" class="h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="flex min-h-0 flex-1 items-center justify-center">
            <img id="proof-lightbox-img" src="" alt="Preview bukti transfer" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl">
        </div>
    </div>
</div>

<!-- Manual Deposit Modal -->
<div id="manual-deposit-modal" class="fixed inset-0 z-[1300] bg-black/60 backdrop-blur-sm p-4 items-center justify-center" style="display:none;">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl flex flex-col transition-all duration-300 scale-90 opacity-0" id="manual-deposit-content" style="max-height: min(90vh, 640px);">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-8 py-5 flex-shrink-0 bg-gray-50/50 rounded-t-3xl">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-primary/10 text-brand-primary">
                    <i class="fas fa-hand-holding-dollar text-sm"></i>
                </span>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Setoran Manual Admin</h3>
                    <p class="text-xs font-bold text-gray-400"><?php echo htmlspecialchars($plan['plan_code'] . ' — ' . $plan['customer_name']); ?></p>
                </div>
            </div>
            <button type="button" onclick="closeManualDepositModal()" class="h-9 w-9 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <!-- Body (scrollable, no visible scrollbar) -->
        <form id="manual-deposit-form" enctype="multipart/form-data" class="flex flex-col min-h-0 flex-1">
            <input type="hidden" name="savings_plan_id" value="<?php echo (int)$plan['id']; ?>">

            <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5" style="scrollbar-width:none;-ms-overflow-style:none;">
                <style>#manual-deposit-content .flex-1::-webkit-scrollbar{display:none}</style>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="payment-method-option cursor-pointer">
                            <input type="radio" name="payment_method" value="cash" class="sr-only" checked>
                            <div class="method-card flex items-center gap-3 rounded-2xl border-2 border-brand-primary bg-brand-primary/5 px-4 py-3.5 transition-all">
                                <i class="fas fa-money-bill-wave text-brand-primary"></i>
                                <span class="font-black text-sm text-gray-900">Tunai (Cash)</span>
                            </div>
                        </label>
                        <label class="payment-method-option cursor-pointer">
                            <input type="radio" name="payment_method" value="transfer_bank" class="sr-only">
                            <div class="method-card flex items-center gap-3 rounded-2xl border-2 border-gray-200 px-4 py-3.5 transition-all">
                                <i class="fas fa-building-columns text-gray-400"></i>
                                <span class="font-black text-sm text-gray-900">Transfer Bank</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Tanggal Setor -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Setor</label>
                    <input type="date" name="deposit_date" id="admin-deposit-date" required
                        class="w-full rounded-2xl border-2 border-gray-100 bg-gray-50 px-5 py-3.5 text-base font-bold text-gray-900 outline-none transition-all focus:border-brand-primary/30 focus:bg-white">
                </div>

                <!-- Nominal -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nominal Setoran</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-black text-sm pointer-events-none">Rp</span>
                        <input type="text" id="admin-deposit-amount-display" inputmode="numeric" autocomplete="off" required
                            placeholder="0"
                            class="w-full rounded-2xl border-2 border-gray-100 bg-gray-50 py-3.5 pl-12 pr-5 text-base font-black text-gray-900 outline-none transition-all focus:border-brand-primary/30 focus:bg-white">
                        <input type="hidden" name="amount" id="admin-deposit-amount">
                    </div>
                    <p id="admin-amount-hint" class="mt-1.5 px-1 text-[10px] font-bold text-gray-400">Minimal Rp 10.000</p>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Catatan <span class="text-gray-300 normal-case font-bold">(opsional)</span></label>
                    <textarea name="notes" rows="2" placeholder="Keterangan tambahan setoran..."
                        class="w-full rounded-2xl border-2 border-gray-100 bg-gray-50 px-5 py-3 text-sm font-bold text-gray-900 outline-none transition-all focus:border-brand-primary/30 focus:bg-white resize-none"></textarea>
                </div>

                <!-- Upload Bukti (hanya muncul saat transfer) -->
                <div id="admin-proof-section" class="hidden">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Bukti Transfer <span class="text-gray-300 normal-case font-bold">(JPG/PNG/WEBP, maks 2MB)</span></label>
                    <div id="admin-drop-zone" class="relative cursor-pointer group">
                        <input type="file" name="payment_proof" id="admin-proof-input" accept="image/jpeg,image/png,image/webp" class="hidden">
                        <div id="admin-upload-ui" class="w-full rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 py-7 px-4 text-center transition-all group-hover:border-brand-primary/40 group-hover:bg-brand-primary/5">
                            <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm text-gray-400 group-hover:text-brand-primary transition-colors">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary" id="admin-filename-text">Klik atau tarik gambar ke sini</p>
                        </div>
                        <div id="admin-preview-container" class="hidden absolute inset-0 rounded-2xl overflow-hidden bg-white border-2 border-gray-100">
                            <img id="admin-image-preview" class="h-full w-full object-contain">
                            <button type="button" onclick="resetAdminImage()" class="absolute right-3 top-3 h-8 w-8 flex items-center justify-center rounded-lg bg-black/40 text-white hover:bg-black/60 transition">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex-shrink-0 border-t border-gray-100 bg-white px-8 py-5 rounded-b-3xl">
                <div id="manual-deposit-error" class="hidden mb-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm font-bold text-red-600"></div>
                <button type="submit" id="manual-deposit-submit" class="w-full rounded-2xl bg-brand-primary py-4 text-sm font-black text-white shadow-lg shadow-brand-primary/25 transition hover:bg-brand-dark flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Simpan Setoran Manual
                </button>
            </div>
        </form>
    </div>
</div>



<!-- Approval Modal -->
<div id="approval-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[1200] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl transition-all duration-300 scale-90 opacity-0 overflow-hidden flex flex-col max-h-[95vh]" id="approval-modal-content">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
            <h3 class="text-lg font-black text-gray-900 tracking-tight">Proses Transaksi Tabungan</h3>
            <button type="button" onclick="closeApprovalModal()" class="w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 space-y-6 overflow-y-auto">
            <!-- Transaction Details -->
            <div class="grid grid-cols-2 gap-4 p-5 bg-gray-50 border border-gray-100 rounded-2xl">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Nominal</p>
                    <p class="text-xl font-black text-brand-primary" id="modal-amount">Rp -</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Metode</p>
                    <p class="text-lg font-black text-gray-900 capitalize" id="modal-method">-</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</p>
                    <p class="text-sm font-bold text-gray-700" id="modal-date">-</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100">Pending</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 mt-2">
                <button type="button" onclick="submitApproval('rejected')" class="w-full bg-red-50 text-red-600 border border-red-100 py-3.5 rounded-xl font-bold text-sm hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-times"></i> Tolak Transaksi
                </button>
                <button type="button" onclick="submitApproval('verified')" class="w-full bg-green-500 text-white border border-green-500 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-green-500/20 hover:bg-green-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Setujui Valid
                </button>
                <button type="button" onclick="closeApprovalModal()" class="col-span-2 w-full bg-gray-50 text-gray-600 border border-gray-200 py-3.5 rounded-xl font-bold text-sm hover:bg-gray-100 transition-all">
                    Tutup / Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-trx-modal" class="fixed inset-0 z-[1400] bg-black/60 backdrop-blur-sm p-4 items-center justify-center" style="display:none;">
    <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl transition-all duration-300 scale-90 opacity-0 overflow-hidden" id="delete-trx-content">
        <!-- Red header -->
        <div class="bg-red-50 border-b border-red-100 px-6 py-5 flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-500 flex-shrink-0">
                <i class="fas fa-trash-alt text-lg"></i>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-base">Hapus Transaksi?</h3>
                <p class="text-xs font-bold text-gray-400 mt-0.5">Tindakan ini tidak bisa dibatalkan</p>
            </div>
        </div>
        <!-- Body -->
        <div class="px-6 py-5 space-y-4">
            <div class="rounded-2xl bg-gray-50 border border-gray-100 px-5 py-4 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nominal</span>
                    <span class="font-black text-gray-900" id="del-trx-amount">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status</span>
                    <span class="font-bold text-gray-700 capitalize" id="del-trx-status">—</span>
                </div>
            </div>
            <div id="del-trx-warning" class="hidden rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-2">
                <i class="fas fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs font-bold text-amber-700">Transaksi ini sudah <strong>terverifikasi</strong>. Menghapus akan <strong>mengurangi saldo tabungan</strong> secara otomatis.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-1">
                <button type="button" onclick="closeDeleteModal()"
                    class="w-full py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 font-bold text-sm hover:bg-gray-100 transition-all">
                    Batal
                </button>
                <button type="button" id="del-trx-confirm-btn" onclick="executeDeleteTransaction()"
                    class="w-full py-3 rounded-xl bg-red-500 text-white font-black text-sm hover:bg-red-600 transition-all shadow-lg shadow-red-500/25 flex items-center justify-center gap-2">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openProofLightbox(src) {
    const modal = document.getElementById('proof-lightbox');
    document.getElementById('proof-lightbox-img').src = src;
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}
function closeProofLightbox() {
    const modal = document.getElementById('proof-lightbox');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('proof-lightbox-img').src = '';
    }, 300);
}
function toggleProofFullscreen() {
    const modal = document.getElementById('proof-lightbox');
    if (!document.fullscreenElement && modal.requestFullscreen) modal.requestFullscreen();
    else if (document.exitFullscreen) document.exitFullscreen();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProofLightbox();
});

// Approval Modal Functions
let currentTransactionId = null;

function openApprovalModal(txId, txData) {
    try {
        const trx = typeof txData === 'string' ? JSON.parse(txData) : txData;
        currentTransactionId = txId;
        
        // Populate modal with transaction details
        document.getElementById('modal-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.amount);
        document.getElementById('modal-method').textContent = trx.payment_method.replace('_', ' ');
        document.getElementById('modal-date').textContent = new Date((trx.created_at || '').replace(' ', 'T')).toLocaleDateString('id-ID', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const overlay = document.getElementById('approval-modal');
        const content = document.getElementById('approval-modal-content');
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(() => {
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-90', 'opacity-0');
        }, 10);
    } catch (e) {
        console.error('Error opening approval modal:', e);
    }
}

function closeApprovalModal() {
    const overlay = document.getElementById('approval-modal');
    const content = document.getElementById('approval-modal-content');
    
    overlay.classList.remove('opacity-100');
    content.classList.add('scale-90', 'opacity-0');
    setTimeout(() => {
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
        currentTransactionId = null;
    }, 300);
}

async function submitApproval(status) {
    if (!currentTransactionId) return;
    
    closeApprovalModal();
    
    const res = await fetch('/lautan-ternak-pantura/api/admin/verify_transfer', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: currentTransactionId, status})
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(data.message, 'error');
    }
}

// Delete Transaction Functions
let transactionIdToDelete = null;

function confirmDeleteTransaction(txId, amountStr, status) {
    transactionIdToDelete = txId;
    document.getElementById('del-trx-amount').textContent = amountStr;
    document.getElementById('del-trx-status').textContent = status;
    
    const warning = document.getElementById('del-trx-warning');
    if (status === 'verified') {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
    
    const modal = document.getElementById('delete-trx-modal');
    const content = document.getElementById('delete-trx-content');
    
    modal.style.display = 'flex';
    setTimeout(() => {
        content.classList.remove('scale-90', 'opacity-0');
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-trx-modal');
    const content = document.getElementById('delete-trx-content');
    
    content.classList.add('scale-90', 'opacity-0');
    setTimeout(() => {
        modal.style.display = 'none';
        transactionIdToDelete = null;
    }, 300);
}

async function executeDeleteTransaction() {
    if (!transactionIdToDelete) return;
    
    const btn = document.getElementById('del-trx-confirm-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Hapus';
    
    try {
        const res = await fetch('/lautan-ternak-pantura/api/admin/delete_saving_transaction', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: transactionIdToDelete})
        });
        const data = await res.json();
        if (data.success) {
            closeDeleteModal();
            showToast(data.message || 'Transaksi berhasil dihapus.', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.message || 'Terjadi kesalahan.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        showToast('Gagal menghapus transaksi. Silakan coba lagi.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Close delete modal on backdrop click
document.getElementById('delete-trx-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<script>
// ── Currency formatting helpers ───────────────────────────────────
function formatRupiah(num) {
    if (!num && num !== 0) return '';
    return String(parseInt(num, 10)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Wire up admin amount display ↔ hidden input
(function() {
    const display = document.getElementById('admin-deposit-amount-display');
    const hidden  = document.getElementById('admin-deposit-amount');
    const hint    = document.getElementById('admin-amount-hint');
    if (!display) return;
    display.addEventListener('input', function() {
        const raw = this.value.replace(/[^0-9]/g, '');
        const num = parseInt(raw, 10) || 0;
        this.value = raw ? formatRupiah(num) : '';
        hidden.value = num || '';
        if (hint) {
            hint.textContent = num >= 10000 ? 'Rp ' + formatRupiah(num) : 'Minimal Rp 10.000';
            hint.style.color = (num > 0 && num < 10000) ? '#ef4444' : '';
        }
    });
    display.addEventListener('keydown', function(e) {
        if (['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) return;
        if (!/^[0-9]$/.test(e.key)) e.preventDefault();
    });
})();

// ── Manual Deposit Modal ──────────────────────────────────────────
function openManualDepositModal() {
    const overlay = document.getElementById('manual-deposit-modal');
    const content = document.getElementById('manual-deposit-content');
    // Pre-fill today's date
    const dateInput = document.getElementById('admin-deposit-date');
    if (!dateInput.value) dateInput.value = new Date().toISOString().split('T')[0];
    // Reset amount display
    const amtDisplay = document.getElementById('admin-deposit-amount-display');
    const amtHidden  = document.getElementById('admin-deposit-amount');
    if (amtDisplay) { amtDisplay.value = ''; }
    if (amtHidden)  { amtHidden.value  = ''; }

    overlay.style.display = 'flex';
    setTimeout(() => content.classList.remove('scale-90', 'opacity-0'), 10);
}

function closeManualDepositModal() {
    const overlay = document.getElementById('manual-deposit-modal');
    const content = document.getElementById('manual-deposit-content');
    content.classList.add('scale-90', 'opacity-0');
    setTimeout(() => { overlay.style.display = 'none'; }, 300);
}

// Close on backdrop click
document.getElementById('manual-deposit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeManualDepositModal();
});

// Payment method toggle
document.querySelectorAll('.payment-method-option').forEach(label => {
    label.addEventListener('click', () => {
        // Update card styling
        document.querySelectorAll('.payment-method-option div').forEach(div => {
            div.classList.remove('border-brand-primary', 'bg-brand-primary/5');
            div.classList.add('border-gray-200');
            div.querySelector('i')?.classList.remove('text-brand-primary');
            div.querySelector('i')?.classList.add('text-gray-400');
        });
        const selectedDiv = label.querySelector('div');
        selectedDiv.classList.add('border-brand-primary', 'bg-brand-primary/5');
        selectedDiv.classList.remove('border-gray-200');
        selectedDiv.querySelector('i')?.classList.add('text-brand-primary');
        selectedDiv.querySelector('i')?.classList.remove('text-gray-400');

        // Show/hide proof upload
        const method = label.querySelector('input').value;
        document.getElementById('admin-proof-section').classList.toggle('hidden', method !== 'transfer_bank');
    });
});

// Admin proof file preview
const adminProofInput = document.getElementById('admin-proof-input');
const adminDropZone = document.getElementById('admin-drop-zone');
const adminUploadUi = document.getElementById('admin-upload-ui');
const adminPreviewContainer = document.getElementById('admin-preview-container');
const adminImagePreview = document.getElementById('admin-image-preview');
const adminFilenameText = document.getElementById('admin-filename-text');

adminDropZone.addEventListener('click', () => adminProofInput.click());

adminProofInput.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            adminImagePreview.src = e.target.result;
            adminPreviewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(this.files[0]);
        adminFilenameText.textContent = this.files[0].name;
    }
});

function resetAdminImage() {
    adminProofInput.value = '';
    adminPreviewContainer.classList.add('hidden');
    adminFilenameText.textContent = 'Klik atau tarik gambar ke sini';
}

// Form submission
document.getElementById('manual-deposit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('manual-deposit-submit');
    const errBox = document.getElementById('manual-deposit-error');
    errBox.classList.add('hidden');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

    try {
        const formData = new FormData(this);
        const res = await fetch('/lautan-ternak-pantura/api/admin/add_savings_deposit', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            closeManualDepositModal();
            showToast(data.message || 'Setoran berhasil dicatat.', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            errBox.textContent = data.message || 'Terjadi kesalahan.';
            errBox.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Simpan Setoran Manual';
        }
    } catch (err) {
        errBox.textContent = 'Gagal mengirim permintaan. Coba lagi.';
        errBox.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Simpan Setoran Manual';
    }
});
</script>
<?php require 'views/admin/includes/footer.php'; ?>

