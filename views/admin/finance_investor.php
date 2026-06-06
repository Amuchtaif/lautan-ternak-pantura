<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';

// Calculate total active capital
$totalActiveCapital = 0;
foreach ($funds as $f) {
    if ($f['status'] === 'active') {
        $totalActiveCapital += floatval($f['amount']);
    }
}
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Manajemen Modal Investor';
    $topbarSubtitle = 'Pengelolaan kemitraan modal qurban dan investasi LTP';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Alerts -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['success_msg'])); ?>", "success");
                });
            </script>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['error'])); ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary shadow-sm">
                    <i class="fas fa-hand-holding-dollar text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Modal <span class="text-brand-primary">Investor</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Kelola modal patungan dan pembagian hasil investasi qurban.</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="openInvestorModal()" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-3.5 rounded-2xl transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-sm">
                    <i class="fas fa-user-plus text-sm"></i> Daftar Investor
                </button>
                <button onclick="openFundModal()" class="bg-brand-primary hover:bg-brand-dark text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-brand-primary/25 transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-coins text-sm"></i> Setor Modal
                </button>
            </div>
        </div>

        <!-- Metric Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Modal Aktif</span>
                    <h3 class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($totalActiveCapital, 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Investor Terdaftar</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo count($investors); ?> <small class="text-xs font-bold text-gray-400">Mitra</small></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Riwayat Setoran</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo count($funds); ?> <small class="text-xs font-bold text-gray-400">Log</small></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <!-- Section Tabs (Daftar Investor vs Jurnal Investasi) -->
        <div class="space-y-6">
            <div class="border-b border-gray-200/80 flex gap-6 text-xs font-black uppercase tracking-wider">
                <button onclick="switchTab('funds')" id="tab-btn-funds" class="pb-4 border-b-2 border-brand-primary text-brand-primary">Jurnal Modal Investor</button>
                <button onclick="switchTab('investors')" id="tab-btn-investors" class="pb-4 border-b-2 border-transparent text-gray-400 hover:text-gray-600">Daftar Pemasok Modal</button>
            </div>

            <!-- Tab 1: Jurnal Investasi -->
            <div id="tab-content-funds" class="space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="funds-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Investor</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tanggal</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tujuan Rekening</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($funds)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-bold">
                                            Belum ada pencatatan setoran modal investasi.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($funds as $i => $fund): ?>
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="px-6 py-4 text-sm font-black text-gray-400"><?php echo $i + 1; ?></td>
                                            <td class="px-6 py-4">
                                                <p class="font-black text-gray-800"><?php echo htmlspecialchars($fund['investor_name']); ?></p>
                                                <p class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($fund['investor_phone'] ?: '-'); ?></p>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                                <?php echo date('d M Y', strtotime($fund['date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 font-black text-brand-primary">
                                                Rp <?php echo number_format($fund['amount'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-600">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fas fa-building-columns text-[10px] text-gray-400"></i>
                                                    <span><?php echo htmlspecialchars($fund['account_name']); ?></span>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-wider rounded <?php echo $fund['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'; ?>">
                                                    <?php echo $fund['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex gap-2">
                                                    <?php if ($fund['proof']): ?>
                                                        <button onclick="openLightbox('<?php echo htmlspecialchars($fund['proof'], ENT_QUOTES); ?>')" class="h-8 px-3 rounded-lg bg-gray-50 border border-gray-100 text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 flex items-center gap-1 text-[10px] font-black uppercase tracking-wider shadow-sm transition-all" title="Lihat Bukti">
                                                            <i class="fas fa-image"></i> Bukti
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($fund['status'] === 'active'): ?>
                                                        <form action="/lautan-ternak-pantura/finance/investor" method="POST" class="inline">
                                                            <input type="hidden" name="action" value="complete_fund">
                                                            <input type="hidden" name="id" value="<?php echo $fund['id']; ?>">
                                                            <button type="submit" class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-500 hover:text-white flex items-center gap-1 text-[10px] font-black uppercase tracking-wider shadow-sm transition-all">
                                                                <i class="fas fa-check"></i> Selesai
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                     <button type="button" onclick="openDeleteModal('delete_fund', <?php echo $fund['id']; ?>, 'Batalkan Setoran Modal', 'Apakah Anda yakin ingin membatalkan setoran modal dari <?php echo htmlspecialchars(addslashes($fund['investor_name']), ENT_QUOTES); ?> senilai Rp <?php echo number_format($fund['amount'], 0, ',', '.'); ?>? Saldo rekening kas terkait akan dikurangi kembali secara otomatis.')" class="h-8 w-8 rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-500 hover:text-white flex items-center justify-center text-xs shadow-sm transition-all" title="Batalkan Investasi">
                                                         <i class="fas fa-trash"></i>
                                                     </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Daftar Investor -->
            <div id="tab-content-investors" class="space-y-6 hidden">
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="investors-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nama Pemasok Modal</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nomor HP / WA</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Alamat Domisili</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($investors)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-bold">
                                            Belum ada investor terdaftar.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($investors as $i => $inv): ?>
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="px-6 py-4 text-sm font-black text-gray-400"><?php echo $i + 1; ?></td>
                                            <td class="px-6 py-4 font-black text-gray-900"><?php echo htmlspecialchars($inv['name']); ?></td>
                                            <td class="px-6 py-4 text-xs font-bold text-gray-600">
                                                <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $inv['phone']); ?>" target="_blank" class="hover:underline hover:text-emerald-500">
                                                    <i class="fab fa-whatsapp text-emerald-500 mr-1"></i><?php echo htmlspecialchars($inv['phone'] ?: '-'); ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">
                                                <?php echo htmlspecialchars($inv['address'] ?: '-'); ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex gap-2">
                                                    <button onclick='openEditInvestorModal(<?php echo json_encode($inv, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="h-8 w-8 rounded-lg bg-gray-50 border border-gray-100 text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 flex items-center justify-center text-xs shadow-sm transition-all">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" onclick="openDeleteModal('delete_investor', <?php echo $inv['id']; ?>, 'Hapus Profil Investor', 'Apakah Anda yakin ingin menghapus profil investor <?php echo htmlspecialchars(addslashes($inv['name']), ENT_QUOTES); ?>? Catatan setoran modal yang terdaftar dari investor ini wajib kosong terlebih dahulu agar tidak merusak laporan arus kas.')" class="h-8 w-8 rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-500 hover:text-white flex items-center justify-center text-xs shadow-sm transition-all" title="Hapus Investor">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal 1: Register Master Investor -->
<div id="investor-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2" id="investor-modal-title">
                <i class="fas fa-user-plus text-brand-primary"></i> Daftarkan Investor Baru
            </h3>
            <button onclick="closeModal('investor')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/investor" method="POST" class="space-y-4">
            <input type="hidden" name="action" id="investor-action" value="add_investor">
            <input type="hidden" name="id" id="investor-id">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Lengkap Investor</label>
                <input type="text" name="name" id="investor-name" required placeholder="Contoh: Budi Santoso..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nomor WhatsApp / HP</label>
                <input type="text" name="phone" id="investor-phone" placeholder="Contoh: 0812345678..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Alamat Lengkap</label>
                <textarea name="address" id="investor-address" placeholder="Alamat tinggal investor..." rows="3" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('investor')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Log Capital Injection (Fund) -->
<div id="fund-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-coins text-brand-primary"></i> Catat Setoran Modal
            </h3>
            <button onclick="closeModal('fund')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/investor" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_fund">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Pilih Investor</label>
                <div class="relative">
                    <select name="investor_id" required class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="">-- Pilih Investor Pemasok --</option>
                        <?php foreach ($investors as $inv): ?>
                            <option value="<?php echo $inv['id']; ?>"><?php echo htmlspecialchars($inv['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Kas / Bank Penerima Dana</label>
                <div class="relative">
                    <select name="cash_account_id" required class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="">-- Pilih Rekening Kas --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?> (Saldo: Rp <?php echo number_format($acc['current_balance'], 0, ',', '.'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Tanggal Setor</label>
                <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nominal Investasi (Rp)</label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="text" name="amount" required oninput="formatCurrency(this)" placeholder="0" class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all font-mono">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Keterangan / Memo</label>
                <textarea name="description" placeholder="Contoh: Investasi patungan 5 ekor sapi..." rows="2" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Bukti Transfer (Lampiran)</label>
                <input type="file" name="proof" accept="image/*" class="w-full px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('fund')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Setor Modal</button>
            </div>
        </form>
    </div>
</div>

<!-- Image Lightbox Overlay -->
<div id="lightbox-overlay" class="fixed inset-0 bg-black/85 backdrop-blur-sm z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0 animate-all" onclick="closeLightbox()">
    <div class="relative max-w-3xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeLightbox()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="lightbox-preview" src="" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl border-4 border-white/10">
    </div>
</div>

<!-- Modal 4: Delete Confirmation (Reusable) -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-xl mx-auto">
                <i class="fas fa-trash-can"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 leading-none" id="delete-title">Konfirmasi Hapus</h3>
                <p class="text-xs text-gray-400 font-bold mt-2.5 leading-relaxed" id="delete-message">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/investor" method="POST" class="mt-6 flex gap-3">
            <input type="hidden" name="action" id="delete-action" value="">
            <input type="hidden" name="id" id="delete-id" value="">
            
            <button type="button" onclick="closeModal('delete')" class="flex-1 py-3 bg-gray-50 border border-gray-100 hover:bg-gray-100 text-gray-500 rounded-xl text-xs font-bold transition-all uppercase tracking-wider">Batal</button>
            <button type="submit" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-red-500/20 uppercase tracking-wider">Ya, Hapus</button>
        </form>
    </div>
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

    function switchTab(tab) {
        const btnFunds = document.getElementById('tab-btn-funds');
        const btnInvestors = document.getElementById('tab-btn-investors');
        const contentFunds = document.getElementById('tab-content-funds');
        const contentInvestors = document.getElementById('tab-content-investors');

        if (tab === 'funds') {
            btnFunds.className = "pb-4 border-b-2 border-brand-primary text-brand-primary";
            btnInvestors.className = "pb-4 border-b-2 border-transparent text-gray-400 hover:text-gray-600";
            contentFunds.classList.remove('hidden');
            contentInvestors.classList.add('hidden');
        } else {
            btnInvestors.className = "pb-4 border-b-2 border-brand-primary text-brand-primary";
            btnFunds.className = "pb-4 border-b-2 border-transparent text-gray-400 hover:text-gray-600";
            contentInvestors.classList.remove('hidden');
            contentFunds.classList.add('hidden');
        }
    }

    function openInvestorModal() {
        document.getElementById('investor-modal-title').innerHTML = '<i class="fas fa-user-plus text-brand-primary"></i> Daftarkan Investor Baru';
        document.getElementById('investor-action').value = 'add_investor';
        document.getElementById('investor-id').value = '';
        document.getElementById('investor-name').value = '';
        document.getElementById('investor-phone').value = '';
        document.getElementById('investor-address').value = '';

        const modal = document.getElementById('investor-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openEditInvestorModal(inv) {
        document.getElementById('investor-modal-title').innerHTML = '<i class="fas fa-edit text-brand-primary"></i> Edit Profil Investor';
        document.getElementById('investor-action').value = 'edit_investor';
        document.getElementById('investor-id').value = inv.id;
        document.getElementById('investor-name').value = inv.name;
        document.getElementById('investor-phone').value = inv.phone || '';
        document.getElementById('investor-address').value = inv.address || '';

        const modal = document.getElementById('investor-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openFundModal() {
        const modal = document.getElementById('fund-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closeModal(prefix) {
        const modal = document.getElementById(prefix + '-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function openLightbox(src) {
        const overlay = document.getElementById('lightbox-overlay');
        const img = document.getElementById('lightbox-preview');
        img.src = src;
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
        }, 10);
    }

    function closeLightbox() {
        const overlay = document.getElementById('lightbox-overlay');
        if (overlay) {
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                document.getElementById('lightbox-preview').src = '';
            }, 300);
        }
    }

    function openDeleteModal(action, id, title, message) {
        document.getElementById('delete-action').value = action;
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-title').textContent = title;
        document.getElementById('delete-message').textContent = message;

        const modal = document.getElementById('delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
