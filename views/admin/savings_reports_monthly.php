<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Laporan Bulanan <span class="text-brand-primary">Tabungan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Pertumbuhan dana, nasabah aktif, dan statistik pembayaran.</p>
            </div>
            <a href="/lautan-ternak-pantura/savingsReport/daily" class="bg-white border border-gray-100 px-5 py-3 rounded-lg font-black text-sm text-brand-primary">Laporan Harian</a>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
                <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
                    <option value="">Semua Status</option>
                    <?php foreach (['pending', 'verified', 'rejected'] as $item): ?>
                        <option value="<?php echo $item; ?>" <?php echo $status === $item ? 'selected' : ''; ?>><?php echo ucfirst($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="customer" value="<?php echo htmlspecialchars($customer); ?>" placeholder="Customer / kode plan" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
                <button class="bg-brand-primary text-white rounded-lg font-black text-sm">Filter</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Dana Terkumpul</p><p class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($report['summary']['total_collected'], 0, ',', '.'); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Nasabah Aktif</p><p class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['active_customers']); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Completion Rate</p><p class="text-2xl font-black text-green-600 mt-2"><?php echo $report['completion_rate']; ?>%</p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Transaksi</p><p class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['transaction_count']); ?></p></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100"><h2 class="font-black text-gray-900">Pertumbuhan Harian</h2></div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($report['growth'])): ?>
                        <div class="p-6 text-gray-500 font-bold">Belum ada data.</div>
                    <?php else: ?>
                        <?php foreach ($report['growth'] as $row): ?>
                            <div class="p-5 flex items-center justify-between"><span class="font-bold text-gray-700"><?php echo date('d M Y', strtotime($row['period'])); ?></span><span class="font-black text-brand-primary">Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></span></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100"><h2 class="font-black text-gray-900">Statistik Pembayaran</h2></div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($report['payment_stats'])): ?>
                        <div class="p-6 text-gray-500 font-bold">Belum ada data.</div>
                    <?php else: ?>
                        <?php foreach ($report['payment_stats'] as $row): ?>
                            <div class="p-5 flex items-center justify-between"><span class="font-bold text-gray-700"><?php echo htmlspecialchars($row['payment_method']); ?> (<?php echo number_format($row['count']); ?>)</span><span class="font-black text-gray-900">Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></span></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require 'views/admin/includes/footer.php'; ?>
