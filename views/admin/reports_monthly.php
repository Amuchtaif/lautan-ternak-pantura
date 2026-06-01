<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php'; 
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Laporan Bulanan';
    $topbarSubtitle = 'Evaluasi performa bulanan, grafik penjualan harian, dan statistik pembayaran';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="w-full space-y-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Laporan Bulanan <span class="text-brand-primary">(Monthly Report)</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Evaluasi performa bulanan, grafik penjualan harian, dan statistik pembayaran</p>
            </div>
            
            <!-- Month & Status Filter -->
            <form method="GET" action="/lautan-ternak-pantura/report/monthly" class="flex flex-wrap items-center gap-3 bg-white p-2.5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="relative">
                    <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" class="pl-4 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl outline-none font-bold text-sm text-gray-700">
                </div>
                <div class="relative">
                    <select name="status" class="px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl outline-none font-bold text-sm text-gray-700">
                        <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <button type="submit" class="bg-brand-primary hover:bg-brand-dark text-white px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                    Filter
                </button>
            </form>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Penjualan Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100/50 flex items-center group hover:shadow-xl transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="ml-4">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Penjualan</p>
                    <p class="text-lg font-black text-gray-900">Rp <?php echo number_format($summary['total_sales'], 0, ',', '.'); ?></p>
                    <span class="text-[9px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded">Total Nilai Penjualan</span>
                </div>
            </div>

            <!-- Total Modal Pembelian Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100/50 flex items-center group hover:shadow-xl transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="ml-4">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Modal Pembelian</p>
                    <p class="text-lg font-black text-gray-900">Rp <?php echo number_format($summary['total_purchase_cost'], 0, ',', '.'); ?></p>
                    <span class="text-[9px] text-rose-600 font-bold bg-rose-50 px-1.5 py-0.5 rounded">Modal Beli Stok Ternak</span>
                </div>
            </div>

            <!-- Total Margin Keuntungan Card -->
            <?php
            $marginIsNegative = $summary['total_margin'] < 0;
            $marginBg = $marginIsNegative ? 'bg-rose-50' : 'bg-emerald-50';
            $marginText = $marginIsNegative ? 'text-rose-600' : 'text-emerald-600';
            $marginLabel = 'Margin Keuntungan';
            $marginIcon = $marginIsNegative ? 'fa-arrow-trend-down' : 'fa-arrow-trend-up';
            ?>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100/50 flex items-center group hover:shadow-xl transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl <?php echo $marginBg; ?> <?php echo $marginText; ?> flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fas <?php echo $marginIcon; ?>"></i>
                </div>
                <div class="ml-4">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1"><?php echo $marginLabel; ?></p>
                    <p class="text-lg font-black <?php echo $marginIsNegative ? 'text-rose-600' : 'text-gray-900'; ?>">
                        <?php echo ($marginIsNegative ? '-' : '') . 'Rp ' . number_format(abs($summary['total_margin']), 0, ',', '.'); ?>
                    </p>
                    <span class="text-[9px] <?php echo $marginText; ?> font-bold <?php echo $marginBg; ?> px-1.5 py-0.5 rounded">Selisih Harga Jual & Beli</span>
                </div>
            </div>

            <!-- Total Transaksi Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100/50 flex items-center group hover:shadow-xl transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="ml-4">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Transaksi</p>
                    <p class="text-lg font-black text-gray-900"><?php echo number_format($summary['total_transactions'], 0, ',', '.'); ?> Transaksi</p>
                    <span class="text-[9px] text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded">Jumlah Penjualan</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Daily Revenue Breakdown Chart / Sparklines (Left 2 Cols) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Daily Trend Chart Card -->
                <?php
                // Prepare chart data for every day of the month
                $numDays = date('t', strtotime($month . '-01'));
                $chartDataByDay = array_fill(1, $numDays, 0);
                foreach ($summary['chart_data'] as $dayData) {
                    $dayNum = (int)date('d', strtotime($dayData['sale_date']));
                    $chartDataByDay[$dayNum] = (float)$dayData['revenue'];
                }
                $chartLabels = range(1, $numDays);
                $chartValues = array_values($chartDataByDay);

                // English Month name translation to Indonesian for beautiful layout
                $monthNamesIndo = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember'
                ];
                $englishMonthName = date('F', strtotime($month . '-01'));
                $yearNum = date('Y', strtotime($month . '-01'));
                $indoMonthName = $monthNamesIndo[$englishMonthName] ?? $englishMonthName;
                $displayMonth = $indoMonthName . ' ' . $yearNum;
                ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 flex flex-col justify-between mb-8">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">Tren Penjualan Bulan <?php echo $displayMonth; ?></h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Akumulasi penjualan harian sepanjang bulan</p>
                        </div>
                        <span class="px-4 py-1.5 text-xs font-black rounded-full bg-blue-50 text-blue-600 tracking-wide">Monthly View</span>
                    </div>
                    
                    <div class="relative w-full h-[320px]">
                        <canvas id="monthlyTrendChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById('monthlyTrendChart');
                    if (ctx) {
                        const chartCtx = ctx.getContext('2d');
                        const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(13, 91, 181, 0.15)');
                        gradient.addColorStop(1, 'rgba(13, 91, 181, 0.00)');

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($chartLabels); ?>,
                                datasets: [{
                                    label: 'Pendapatan',
                                    data: <?php echo json_encode($chartValues); ?>,
                                    borderColor: '#0d5bb5', // brand-primary
                                    borderWidth: 3,
                                    fill: true,
                                    backgroundColor: gradient,
                                    tension: 0.4, // curved line like in mock
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#0d5bb5',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6
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
                                        callbacks: {
                                            label: function(context) {
                                                return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        title: {
                                            display: true,
                                            text: 'Tanggal',
                                            font: {
                                                family: "'Plus Jakarta Sans', sans-serif",
                                                weight: 'bold',
                                                size: 11
                                            },
                                            color: '#9ca3af'
                                        },
                                        ticks: {
                                            font: {
                                                family: "'Plus Jakarta Sans', sans-serif",
                                                weight: 'bold',
                                                size: 10
                                            },
                                            color: '#9ca3af'
                                        }
                                    },
                                    y: {
                                        min: 0,
                                        grid: {
                                            color: '#f3f4f6',
                                            drawBorder: false,
                                            borderDash: [5, 5]
                                        },
                                        ticks: {
                                            precision: 0,
                                            font: {
                                                family: "'Plus Jakarta Sans', sans-serif",
                                                weight: 'bold',
                                                size: 10
                                            },
                                            color: '#9ca3af',
                                            callback: function(value) {
                                                if (value < 0) return '';
                                                if (value === 0) return 'Rp 0';
                                                if (value >= 1000000 && value % 1000000 === 0) return 'Rp ' + (value / 1000000) + 'jt';
                                                if (value >= 1000) return 'Rp ' + Math.round(value / 1000) + 'rb';
                                                return 'Rp ' + Math.round(value);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
                </script>

                <!-- Monthly Top Selling Table -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight"><i class="fas fa-crown text-amber-500 mr-2"></i>Produk Terlaris Bulan Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/20">
                                <tr>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Hewan</th>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Kode</th>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Terjual</th>
                                    <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Nilai Penjualan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($summary['top_selling'])): ?>
                                    <tr>
                                        <td colspan="4" class="px-8 py-10 text-center text-gray-400 font-bold">
                                            Belum ada data penjualan di bulan ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($summary['top_selling'] as $item): ?>
                                        <tr class="hover:bg-brand-light/10 transition-all">
                                            <td class="px-8 py-5">
                                                <p class="text-sm font-black text-gray-900 capitalize"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                            </td>
                                            <td class="px-8 py-5">
                                                <span class="inline-block px-2.5 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-black uppercase"><?php echo htmlspecialchars($item['product_code']); ?></span>
                                            </td>
                                            <td class="px-8 py-5">
                                                <p class="text-sm font-bold text-gray-700"><?php echo $item['total_sold']; ?> ekor</p>
                                            </td>
                                            <td class="px-8 py-5 text-right font-black text-brand-primary text-sm">
                                                Rp <?php echo number_format($item['total_sales_value'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Breakdown Stat (Right 1 Col) -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 space-y-6">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Status Pembayaran</h3>
                    <div class="space-y-6">
                        <?php if (empty($summary['payment_stats'])): ?>
                            <p class="text-xs text-gray-400 font-bold text-center py-4">Belum ada aktivitas transaksi pembayaran.</p>
                        <?php else: ?>
                            <?php foreach ($summary['payment_stats'] as $pStat): ?>
                                <?php 
                                    $lbl = [
                                        'pending' => 'Pending',
                                        'waiting_payment' => 'Nunggu Bayar',
                                        'payment_review' => 'Review Bayar',
                                        'paid' => 'Lunas',
                                        'processing' => 'Diproses',
                                        'delivered' => 'Dikirim',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Batal'
                                    ][$pStat['status']] ?? $pStat['status'];
                                    
                                    $color = [
                                        'paid' => 'bg-emerald-500',
                                        'completed' => 'bg-emerald-600',
                                        'processing' => 'bg-purple-500',
                                        'delivered' => 'bg-teal-500',
                                        'payment_review' => 'bg-blue-500',
                                        'pending' => 'bg-amber-500',
                                        'cancelled' => 'bg-red-500',
                                        'waiting_payment' => 'bg-orange-500'
                                    ][$pStat['status']] ?? 'bg-gray-400';
                                ?>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-xs font-black uppercase">
                                        <span class="text-gray-500 capitalize"><?php echo $lbl; ?> (<?php echo $pStat['count']; ?>)</span>
                                        <span class="text-gray-900">Rp <?php echo number_format($pStat['total_value'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                        <div class="<?php echo $color; ?> h-full rounded-full" style="width: <?php echo ($pStat['count'] / $summary['total_transactions']) * 100; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chart Card (Right 1 Col) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 tracking-tight mb-1"><i class="fas fa-chart-pie text-brand-primary mr-2"></i>Proporsi Status Pembayaran</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-6">Persentase status pembayaran bulan ini</p>
                    </div>
                    <div class="relative w-full aspect-square max-h-[220px] mx-auto flex items-center justify-center mb-4">
                        <?php if (empty($summary['payment_stats'])): ?>
                            <div class="text-center text-gray-400 font-bold py-10">
                                <i class="fas fa-chart-pie text-4xl mb-3 block text-gray-300 animate-pulse"></i>
                                Belum ada data pembayaran
                            </div>
                        <?php else: ?>
                            <canvas id="monthlyPaymentChart" class="w-full h-full"></canvas>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-50 text-[11px] font-bold text-gray-400 leading-relaxed">
                        * Data disinkronkan real-time dengan basis data internal LTP.
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById('monthlyPaymentChart');
                    if (ctx) {
                        const statusLabels = [];
                        const statusCounts = [];
                        const statusColors = [];
                        
                        const labelsMap = {
                            'pending': 'Pending',
                            'waiting_payment': 'Nunggu Bayar',
                            'payment_review': 'Review Bayar',
                            'paid': 'Lunas',
                            'processing': 'Diproses',
                            'delivered': 'Dikirim',
                            'completed': 'Selesai',
                            'cancelled': 'Batal'
                        };
                        
                        const colorsMap = {
                            'paid': '#10b981',           // emerald-500
                            'completed': '#059669',      // emerald-600
                            'processing': '#8b5cf6',     // purple-500
                            'delivered': '#14b8a6',      // teal-500
                            'payment_review': '#3b82f6', // blue-500
                            'pending': '#f59e0b',        // amber-500
                            'cancelled': '#ef4444',      // red-500
                            'waiting_payment': '#f97316' // orange-500
                        };

                        <?php foreach ($summary['payment_stats'] as $pStat): ?>
                            statusLabels.push(labelsMap['<?php echo $pStat['status']; ?>'] || '<?php echo $pStat['status']; ?>');
                            statusCounts.push(<?php echo $pStat['count']; ?>);
                            statusColors.push(colorsMap['<?php echo $pStat['status']; ?>'] || '#9ca3af');
                        <?php endforeach; ?>

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: statusLabels,
                                datasets: [{
                                    data: statusCounts,
                                    backgroundColor: statusColors,
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                family: "'Plus Jakarta Sans', sans-serif",
                                                weight: 'bold',
                                                size: 10
                                            },
                                            color: '#4b5563',
                                            padding: 8,
                                            boxWidth: 8
                                        }
                                    }
                                },
                                cutout: '70%'
                            }
                        });
                    }
                });
                </script>
            </div>
        </div>

        </div>
    </main>
</div>

<?php require_once 'views/admin/includes/footer.php'; ?>
