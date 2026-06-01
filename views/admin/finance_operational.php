<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';

// Calculate monthly total
$currentMonthExpense = 0;
foreach ($expenses as $exp) {
    if (date('Y-m', strtotime($exp['date'])) === date('Y-m')) {
        $currentMonthExpense += floatval($exp['amount']);
    }
}
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Manajemen Dana Operasional';
    $topbarSubtitle = 'Pencatatan pengeluaran operasional dan biaya perawatan LTP';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary shadow-sm">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Dana <span class="text-brand-primary">Operasional</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Catat seluruh pakan, obat-obatan, logistik, dan operasional usaha.</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="openCategoryModal()" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-3.5 rounded-2xl transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-sm">
                    <i class="fas fa-tags text-sm"></i> Tambah Kategori
                </button>
                <button onclick="openExpenseModal()" class="bg-brand-primary hover:bg-brand-dark text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-brand-primary/25 transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-hand-holding-dollar text-sm"></i> Catat Pengeluaran
                </button>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Biaya Bulan Ini</span>
                    <h3 class="text-2xl font-black text-red-600 mt-2">Rp <?php echo number_format($currentMonthExpense, 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Kategori Biaya</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo count($categories); ?> <small class="text-xs font-bold text-gray-400">Aktif</small></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Pencatatan</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo count($expenses); ?> <small class="text-xs font-bold text-gray-400">Baris</small></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm print:hidden">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Kategori Biaya</label>
                    <div class="relative">
                        <select name="category_id" class="w-full pl-11 pr-10 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 appearance-none outline-none focus:bg-white focus:border-brand-primary cursor-pointer">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-tag absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Tanggal Mulai</label>
                    <div class="relative">
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 outline-none focus:bg-white focus:border-brand-primary">
                        <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Tanggal Akhir</label>
                    <div class="relative">
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 outline-none focus:bg-white focus:border-brand-primary">
                        <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-brand-primary hover:bg-brand-dark text-white rounded-xl py-3.5 font-black text-xs shadow-md shadow-brand-primary/10 hover:shadow-lg transition-all flex items-center justify-center gap-1.5">
                        <i class="fas fa-filter text-[10px]"></i> Filter
                    </button>
                    <a href="/lautan-ternak-pantura/finance/operasional" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl py-3.5 font-black text-xs border border-gray-100 flex items-center justify-center gap-1.5 transition-all text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Expenses Table Card -->
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full" id="expenses-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Sumber Kas</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($expenses)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-bold">
                                    Belum ada catatan biaya operasional terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $i => $exp): ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-sm font-black text-gray-400"><?php echo $i + 1; ?></td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                        <?php echo date('d M Y', strtotime($exp['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-black text-brand-primary">
                                        <span class="bg-brand-light/50 px-2.5 py-1 rounded-lg">
                                            <?php echo htmlspecialchars($exp['category_name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">
                                        <?php echo htmlspecialchars($exp['description'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fas fa-wallet text-[10px] text-gray-400"></i>
                                            <span><?php echo htmlspecialchars($exp['account_name']); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-black text-red-600">
                                        Rp <?php echo number_format($exp['amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex gap-2">
                                            <?php if ($exp['attachment']): ?>
                                                <button onclick="openLightbox('<?php echo htmlspecialchars($exp['attachment'], ENT_QUOTES); ?>')" class="h-8 px-3 rounded-lg bg-gray-50 border border-gray-100 text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 flex items-center gap-1 text-[10px] font-black uppercase tracking-wider shadow-sm transition-all" title="Lihat Nota">
                                                    <i class="fas fa-receipt"></i> Nota
                                                </button>
                                            <?php endif; ?>
                                            <form action="/lautan-ternak-pantura/finance/operasional" method="POST" onsubmit="return confirm('Batalkan pengeluaran biaya ini? Saldo kas terkait akan dipulihkan.');" class="inline">
                                                <input type="hidden" name="action" value="delete_expense">
                                                <input type="hidden" name="id" value="<?php echo $exp['id']; ?>">
                                                <button type="submit" class="h-8 w-8 rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-500 hover:text-white flex items-center justify-center text-xs shadow-sm transition-all">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal 1: Add Operational Category -->
<div id="category-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-tag text-brand-primary"></i> Kategori Pengeluaran Baru
            </h3>
            <button onclick="closeModal('category')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/operasional" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_category">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Kategori Pengeluaran</label>
                <input type="text" name="name" required placeholder="Contoh: Sewa Kandang, Listrik & Air..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('category')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Add Operational Expense -->
<div id="expense-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-brand-primary"></i> Catat Biaya Operasional
            </h3>
            <button onclick="closeModal('expense')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/operasional" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_expense">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Kategori</label>
                <div class="relative">
                    <select name="category_id" required class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Kas / Bank Sumber Dana</label>
                <div class="relative">
                    <select name="cash_account_id" required class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="">-- Pilih Rekening Sumber --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?> (Saldo: Rp <?php echo number_format($acc['current_balance'], 0, ',', '.'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Tanggal Transaksi</label>
                <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nominal Biaya (Rp)</label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="text" name="amount" required oninput="formatCurrency(this)" placeholder="0" class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all font-mono">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi / Detail Biaya</label>
                <textarea name="description" placeholder="Contoh: Pembelian 15 karung pakan konsentrat..." rows="2" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nota Transaksi / Receipt (Lampiran)</label>
                <input type="file" name="attachment" accept="image/*" class="w-full px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('expense')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Catat Biaya</button>
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

<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function openCategoryModal() {
        const modal = document.getElementById('category-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openExpenseModal() {
        const modal = document.getElementById('expense-modal');
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
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
