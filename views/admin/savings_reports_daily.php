<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>

<!-- Scoped Custom Styling & Print Formatting -->
<style>
@media print {
    aside, nav, .print\:hidden, form, button, a {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        background: white !important;
    }
    .print\:no-shadow {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }
}
</style>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary shadow-sm">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Laporan Harian <span class="text-brand-primary">Tabungan</span></h1>
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Analisis setoran & audit transaksi per hari.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 print:hidden">
                <button onclick="window.print()" class="bg-white border border-gray-100 hover:border-gray-200 text-gray-600 hover:text-gray-900 px-5 py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-print text-xs"></i> Cetak Laporan
                </button>
                <a href="/lautan-ternak-pantura/savingsReport/monthly" class="bg-brand-light hover:bg-brand-primary hover:text-white border border-brand-primary/10 text-brand-primary px-5 py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-calendar-days text-xs"></i> Laporan Bulanan
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm print:hidden">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Tanggal Laporan</label>
                    <div class="relative">
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold text-sm text-gray-700 focus:bg-white focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all outline-none">
                        <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Status Transaksi</label>
                    <div class="relative">
                        <select name="status" class="w-full pl-11 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold text-sm text-gray-700 appearance-none focus:bg-white focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all outline-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <?php foreach (['pending', 'verified', 'rejected'] as $item): ?>
                                <option value="<?php echo $item; ?>" <?php echo $status === $item ? 'selected' : ''; ?>><?php echo ucfirst($item); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Pencarian Nasabah</label>
                    <div class="relative">
                        <input type="text" name="customer" value="<?php echo htmlspecialchars($customer); ?>" placeholder="Nama / Kode Plan" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold text-sm text-gray-700 focus:bg-white focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all outline-none">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <button type="submit" class="w-full bg-brand-primary hover:bg-brand-dark text-white rounded-xl py-3.5 font-black text-sm shadow-md shadow-brand-primary/10 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sliders text-xs"></i> Filter Data
                </button>
            </form>
        </div>

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Setoran Verified -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Setoran Terverifikasi</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-2">Rp <?php echo number_format($report['summary']['verified_amount'], 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>

            <!-- Card 2: Jumlah Transaksi -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Total Transaksi</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['transaction_count']); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>

            <!-- Card 3: Pending -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Transaksi Pending</span>
                    <h3 class="text-2xl font-black text-amber-600 mt-2 flex items-center gap-1.5">
                        <?php echo number_format($report['summary']['pending_count']); ?>
                        <?php if ($report['summary']['pending_count'] > 0): ?>
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-clock-rotate-left"></i>
                </div>
            </div>

            <!-- Card 4: Verified -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Transaksi Verified</span>
                    <h3 class="text-2xl font-black text-teal-600 mt-2"><?php echo number_format($report['summary']['verified_count']); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full" id="daily-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Kode Plan</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Metode</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($report['transactions'])): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-bold">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-folder-open text-3xl"></i>
                                        <span>Tidak ada data transaksi pada filter ini.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($report['transactions'] as $i => $trx): ?>
                                <?php
                                $stat = $trx['transaction_status'];
                                if ($stat === 'verified') {
                                    $badgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                    $badgeIcon = '<i class="fas fa-check-circle mr-1 text-[10px]"></i>';
                                } elseif ($stat === 'rejected') {
                                    $badgeClass = 'bg-rose-50 text-rose-600 border border-rose-100';
                                    $badgeIcon = '<i class="fas fa-times-circle mr-1 text-[10px]"></i>';
                                } else {
                                    $badgeClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                                    $badgeIcon = '<i class="fas fa-clock mr-1 text-[10px]"></i>';
                                }
                                ?>
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-sm font-black text-gray-400"><?php echo $i + 1; ?></td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-600">
                                        <div class="flex items-center gap-1.5">
                                            <i class="far fa-clock text-gray-400 text-xs"></i>
                                            <span><?php echo date('H:i', strtotime($trx['created_at'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-black text-gray-900"><?php echo htmlspecialchars($trx['customer_name']); ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="font-black text-brand-primary bg-brand-light/50 px-2.5 py-1 rounded-lg">
                                            <?php echo htmlspecialchars($trx['plan_code']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-black text-gray-900">
                                        Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-credit-card text-gray-400 text-[10px]"></i>
                                            <span><?php echo htmlspecialchars(str_replace('_', ' ', $trx['payment_method'] ?? 'transfer_bank')); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $badgeClass; ?>">
                                            <?php echo $badgeIcon; ?>
                                            <span><?php echo htmlspecialchars($trx['transaction_status']); ?></span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Client-Side Pagination (Only show if table has transactions) -->
            <?php if (!empty($report['transactions'])): ?>
                <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0 print:hidden">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tampilkan</span>
                        <div class="relative">
                            <select id="entries-per-page" onchange="changeEntriesPerPage(this.value)" class="pl-4 pr-10 py-2 bg-white border border-gray-100 rounded-lg outline-none focus:border-brand-primary text-xs font-bold text-gray-700 appearance-none cursor-pointer shadow-sm">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">data</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="prevPage()" id="prev-btn" class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i class="fas fa-chevron-left text-xs"></i></button>
                        <div id="page-numbers" class="flex items-center gap-1.5">
                            <!-- Dynamic page numbers -->
                        </div>
                        <button onclick="nextPage()" id="next-btn" class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i class="fas fa-chevron-right text-xs"></i></button>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="entries-info"></span>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Client-side pagination engine for daily transactions table
let currentPage = 1;
let rowsPerPage = 10;
let tableRows = [];

function initPagination() {
    const table = document.getElementById('daily-table');
    if (!table) return;
    tableRows = Array.from(table.querySelectorAll('tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
    if (tableRows.length > 0) {
        showPage(1);
    }
}

function showPage(page) {
    currentPage = page;
    const totalRows = tableRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
    
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;

    const startIdx = (currentPage - 1) * rowsPerPage;
    const endIdx = Math.min(startIdx + rowsPerPage, totalRows);

    tableRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = "";
            // Recalculate display row number dynamically for seamless ordering
            const numCell = row.cells[0];
            if (numCell) numCell.innerText = idx + 1;
        } else {
            row.style.display = "none";
        }
    });

    updatePaginationUI(totalPages, totalRows, startIdx, endIdx);
}

function updatePaginationUI(totalPages, totalRows, startIdx, endIdx) {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const pageNumbers = document.getElementById('page-numbers');
    const infoText = document.getElementById('entries-info');

    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;

    if (prevBtn) {
        if (currentPage === 1) prevBtn.classList.add('opacity-40', 'cursor-not-allowed');
        else prevBtn.classList.remove('opacity-40', 'cursor-not-allowed');
    }
    if (nextBtn) {
        if (currentPage === totalPages) nextBtn.classList.add('opacity-40', 'cursor-not-allowed');
        else nextBtn.classList.remove('opacity-40', 'cursor-not-allowed');
    }

    if (infoText) {
        if (totalRows === 0) {
            infoText.innerText = "Tidak ada data";
        } else {
            infoText.innerText = `Menampilkan ${startIdx + 1}-${endIdx} dari ${totalRows} data`;
        }
    }

    if (pageNumbers) {
        pageNumbers.innerHTML = "";
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.onclick = () => showPage(i);
            btn.className = `w-8 h-8 rounded-lg text-xs font-black transition-all shadow-sm ${
                currentPage === i 
                    ? 'bg-brand-primary text-white shadow-brand-primary/20' 
                    : 'border border-gray-100 bg-white text-gray-500 hover:text-brand-primary hover:border-brand-primary/20'
            }`;
            pageNumbers.appendChild(btn);
        }
    }
}

function prevPage() {
    if (currentPage > 1) showPage(currentPage - 1);
}

function nextPage() {
    const totalPages = Math.ceil(tableRows.length / rowsPerPage) || 1;
    if (currentPage < totalPages) showPage(currentPage + 1);
}

function changeEntriesPerPage(val) {
    rowsPerPage = parseInt(val);
    showPage(1);
}

window.addEventListener('DOMContentLoaded', () => {
    initPagination();
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
