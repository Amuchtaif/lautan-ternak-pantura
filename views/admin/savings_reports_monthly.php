<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>

<!-- Chart.js Library Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                    <i class="fas fa-calendar-days text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Laporan Bulanan <span class="text-brand-primary">Tabungan</span></h1>
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Analisis pertumbuhan dana, efektivitas nasabah & tren bulanan.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 print:hidden">
                <button onclick="window.print()" class="bg-white border border-gray-100 hover:border-gray-200 text-gray-600 hover:text-gray-900 px-5 py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-print text-xs"></i> Cetak Laporan
                </button>
                <a href="/lautan-ternak-pantura/savingsReport/daily" class="bg-brand-light hover:bg-brand-primary hover:text-white border border-brand-primary/10 text-brand-primary px-5 py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-calendar-day text-xs"></i> Laporan Harian
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm print:hidden">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div class="space-y-2">
                    <label class="text-[11px] font-black uppercase text-gray-400 tracking-wider">Bulan Laporan</label>
                    <div class="relative">
                        <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold text-sm text-gray-700 focus:bg-white focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/10 transition-all outline-none">
                        <i class="fas fa-calendar-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
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

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Dana Terkumpul -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Dana Terkumpul</span>
                    <h3 class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($report['summary']['total_collected'], 0, ',', '.'); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>

            <!-- Card 2: Nasabah Aktif -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Nasabah Aktif</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['active_customers']); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <!-- Card 3: Completion Rate -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Completion Rate</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-2"><?php echo $report['completion_rate']; ?>%</h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-circle-dollar-to-slot"></i>
                </div>
            </div>

            <!-- Card 4: Transaksi -->
            <div class="bg-white border border-gray-100 p-6 rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group print:no-shadow">
                <div>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider block">Total Transaksi</span>
                    <h3 class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['transaction_count']); ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <!-- Split Analytics Section (Growth & Payments) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Daily Growth Chart -->
            <div class="bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden print:no-shadow">
                <div class="px-6 py-5 border-b border-gray-100/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2 class="font-black text-gray-900 text-sm">Pertumbuhan Harian</h2>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-1 rounded">Grafik Tren</span>
                </div>
                <div class="p-6">
                    <?php if (empty($report['growth'])): ?>
                        <div class="py-16 text-center text-gray-400 font-bold flex flex-col items-center justify-center gap-2">
                            <i class="fas fa-face-meh text-3xl"></i>
                            <span>Belum ada data pertumbuhan harian bulan ini.</span>
                        </div>
                    <?php else: ?>
                        <div class="h-[320px] w-full relative">
                            <canvas id="growthChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Payment Method Statistics Progress Bars -->
            <div class="bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden print:no-shadow">
                <div class="px-6 py-5 border-b border-gray-100/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h2 class="font-black text-gray-900 text-sm">Statistik Pembayaran</h2>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-1 rounded">Distribusi</span>
                </div>
                <div class="p-6 space-y-6">
                    <?php if (empty($report['payment_stats'])): ?>
                        <div class="py-16 text-center text-gray-400 font-bold flex flex-col items-center justify-center gap-2">
                            <i class="fas fa-face-meh text-3xl"></i>
                            <span>Belum ada data pembayaran bulan ini.</span>
                        </div>
                    <?php else: ?>
                        <?php
                        $totalPayments = 0;
                        foreach ($report['payment_stats'] as $row) {
                            $totalPayments += $row['amount'];
                        }
                        ?>
                        <div class="space-y-6">
                            <?php foreach ($report['payment_stats'] as $index => $row): ?>
                                <?php 
                                $pct = $totalPayments > 0 ? round(($row['amount'] / $totalPayments) * 100, 1) : 0;
                                
                                // Determine custom premium gradient based on index
                                if ($index === 0) {
                                    $gradient = 'from-brand-primary to-blue-500';
                                    $iconBg = 'bg-brand-light text-brand-primary';
                                } elseif ($index === 1) {
                                    $gradient = 'from-emerald-400 to-teal-500';
                                    $iconBg = 'bg-emerald-50 text-emerald-600';
                                } elseif ($index === 2) {
                                    $gradient = 'from-amber-400 to-orange-500';
                                    $iconBg = 'bg-amber-50 text-amber-600';
                                } else {
                                    $gradient = 'from-purple-400 to-indigo-500';
                                    $iconBg = 'bg-purple-50 text-purple-600';
                                }
                                ?>
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-8 w-8 rounded-lg <?php echo $iconBg; ?> flex items-center justify-center text-xs">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-wide">
                                                    <?php echo htmlspecialchars(str_replace('_', ' ', $row['payment_method'])); ?>
                                                </h4>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                                                    <?php echo number_format($row['count']); ?> Transaksi
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-xs text-gray-900 block">Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></span>
                                            <span class="text-[10px] text-emerald-600 font-black uppercase tracking-wider"><?php echo $pct; ?>%</span>
                                        </div>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="h-3 bg-gray-50 rounded-full overflow-hidden border border-gray-100/50 shadow-inner">
                                        <div class="h-full rounded-full bg-gradient-to-r <?php echo $gradient; ?> transition-all duration-500" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if (!empty($report['growth'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('growthChart').getContext('2d');
    
    // PHP to JS data parsing
    const labels = <?php echo json_encode(array_map(function($row) {
        return date('d M', strtotime($row['period']));
    }, $report['growth'])); ?>;
    
    const data = <?php echo json_encode(array_map(function($row) {
        return (float)$row['amount'];
    }, $report['growth'])); ?>;
    
    // Create soft brand gradient for line area
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(13, 91, 181, 0.25)');
    gradient.addColorStop(1, 'rgba(13, 91, 181, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Setoran',
                data: data,
                borderColor: '#0d5bb5',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0d5bb5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#0d5bb5',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: {
                        family: 'Plus Jakarta Sans',
                        size: 11,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Plus Jakarta Sans',
                        size: 13,
                        weight: '800'
                    },
                    padding: 12,
                    cornerRadius: 16,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 10,
                            weight: 'bold'
                        },
                        color: '#9ca3af'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(243, 244, 246, 1)',
                        drawBorder: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 10,
                            weight: 'bold'
                        },
                        color: '#9ca3af',
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000) + 'jt';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000) + 'rb';
                            }
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require 'views/admin/includes/footer.php'; ?>
