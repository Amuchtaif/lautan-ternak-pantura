<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';

// Calculate totals from the filtered set
$totalIn = 0;
$totalOut = 0;
foreach ($transactions as $tx) {
    $totalIn += floatval($tx['cash_in']);
    $totalOut += floatval($tx['cash_out']);
}
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Buku Besar Arus Kas';
    $topbarSubtitle = 'Jurnal mutasi masuk dan keluar keuangan LTP terpusat';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary shadow-sm">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Arus <span class="text-brand-primary">Kas</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Buku besar mutasi kas dari seluruh modul penjualan, pembelian, operasional & investor.</p>
                </div>
            </div>
            <div class="print:block hidden bg-gray-100 px-4 py-2 rounded-xl text-xs font-black uppercase text-gray-700">
                Dokumen Laporan Keuangan Resmi
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Kas Masuk (Filter)</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-2">Rp <?php echo number_format($totalIn, 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Total Kas Keluar (Filter)</span>
                    <h3 class="text-2xl font-black text-red-600 mt-2">Rp <?php echo number_format($totalOut, 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm flex items-center justify-between group">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Selisih Arus Kas (Net)</span>
                    <?php 
                    $net = $totalIn - $totalOut;
                    $netColor = $net >= 0 ? 'text-brand-primary' : 'text-red-500';
                    ?>
                    <h3 class="text-2xl font-black mt-2 <?php echo $netColor; ?>">
                        <?php echo $net < 0 ? '-' : ''; ?>Rp <?php echo number_format(abs($net), 0, ',', '.'); ?>
                    </h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm print:hidden">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                <div class="space-y-2 col-span-1 md:col-span-1">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Cari Deskripsi</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Kata kunci..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 outline-none focus:bg-white focus:border-brand-primary">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Jenis Transaksi</label>
                    <div class="relative">
                        <select name="type" class="w-full pl-11 pr-10 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 appearance-none outline-none focus:bg-white focus:border-brand-primary cursor-pointer">
                            <option value="">Semua Jenis</option>
                            <option value="MODAL_INVESTOR" <?php echo ($_GET['type'] ?? '') === 'MODAL_INVESTOR' ? 'selected' : ''; ?>>Modal Investor</option>
                            <option value="OPERASIONAL" <?php echo ($_GET['type'] ?? '') === 'OPERASIONAL' ? 'selected' : ''; ?>>Dana Operasional</option>
                            <option value="PEMBELIAN_HEWAN" <?php echo ($_GET['type'] ?? '') === 'PEMBELIAN_HEWAN' ? 'selected' : ''; ?>>Pembelian Hewan</option>
                            <option value="PENJUALAN_HEWAN" <?php echo ($_GET['type'] ?? '') === 'PENJUALAN_HEWAN' ? 'selected' : ''; ?>>Penjualan Hewan</option>
                        </select>
                        <i class="fas fa-list-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Rekening Kas</label>
                    <div class="relative">
                        <select name="cash_account_id" class="w-full pl-11 pr-10 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 appearance-none outline-none focus:bg-white focus:border-brand-primary cursor-pointer">
                            <option value="">Semua Rekening</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['id']; ?>" <?php echo ($_GET['cash_account_id'] ?? '') == $acc['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($acc['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-wallet absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Mulai Tanggal</label>
                    <div class="relative">
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 outline-none focus:bg-white focus:border-brand-primary">
                        <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Sampai Tanggal</label>
                    <div class="relative">
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200/80 rounded-xl font-bold text-xs text-gray-700 outline-none focus:bg-white focus:border-brand-primary">
                        <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="flex gap-2 col-span-1 sm:col-span-2 md:col-span-5 md:mt-2">
                    <button type="submit" class="flex-1 bg-brand-primary hover:bg-brand-dark text-white rounded-xl py-3.5 font-black text-xs shadow-md shadow-brand-primary/10 hover:shadow-lg transition-all flex items-center justify-center gap-1.5">
                        <i class="fas fa-filter text-[10px]"></i> Saring Data
                    </button>
                    <a href="/lautan-ternak-pantura/finance/arusKas" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl py-3.5 font-black text-xs border border-gray-100 flex items-center justify-center gap-1.5 transition-all text-center">
                        Reset Filter
                    </a>
                    <button type="button" onclick="window.print()" class="px-6 bg-white hover:bg-gray-50 text-gray-700 rounded-xl py-3.5 font-black text-xs border border-gray-200 flex items-center justify-center gap-1.5 transition-all">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full" id="ledger-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Rekening</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Jenis</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Uang Masuk (In)</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Uang Keluar (Out)</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Saldo Berjalan</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-32">User Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-bold">
                                    Belum ada catatan mutasi arus kas yang sesuai filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $i => $tx): ?>
                                <?php
                                $typeLabel = '';
                                $typeClass = '';
                                switch ($tx['transaction_type']) {
                                    case 'MODAL_INVESTOR':
                                        $typeLabel = 'Modal Investor';
                                        $typeClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                        break;
                                    case 'OPERASIONAL':
                                        $typeLabel = 'Dana Operasional';
                                        $typeClass = 'bg-red-50 text-red-700 border-red-100';
                                        break;
                                    case 'PEMBELIAN_HEWAN':
                                        $typeLabel = 'Pembelian Hewan';
                                        $typeClass = 'bg-amber-50 text-amber-700 border-amber-100';
                                        break;
                                    case 'PENJUALAN_HEWAN':
                                        $typeLabel = 'Penjualan Hewan';
                                        $typeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                        break;
                                    default:
                                        $typeLabel = $tx['transaction_type'];
                                        $typeClass = 'bg-gray-50 text-gray-700 border-gray-100';
                                }
                                ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-sm font-black text-gray-400"><?php echo $i + 1; ?></td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                        <?php echo date('d M Y H:i', strtotime($tx['transaction_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-black text-gray-700">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fas fa-wallet text-gray-400 text-[10px]"></i>
                                            <span><?php echo htmlspecialchars($tx['account_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded border <?php echo $typeClass; ?>">
                                            <?php echo $typeLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($tx['description']); ?>">
                                        <?php echo htmlspecialchars($tx['description'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-emerald-600 text-xs">
                                        <?php if (floatval($tx['cash_in']) > 0): ?>
                                            +Rp <?php echo number_format($tx['cash_in'], 0, ',', '.'); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-red-600 text-xs">
                                        <?php if (floatval($tx['cash_out']) > 0): ?>
                                            -Rp <?php echo number_format($tx['cash_out'], 0, ',', '.'); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900 text-xs">
                                        Rp <?php echo number_format($tx['balance_after'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 font-bold">
                                        <?php echo htmlspecialchars($tx['creator_name']); ?>
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

<?php require_once 'views/admin/includes/footer.php'; ?>
