<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Laporan Harian <span class="text-brand-primary">Tabungan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Ringkasan setoran per tanggal.</p>
            </div>
            <a href="/lautan-ternak-pantura/savingsReport/monthly" class="bg-white border border-gray-100 px-5 py-3 rounded-lg font-black text-sm text-brand-primary">Laporan Bulanan</a>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
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
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Setoran Verified</p><p class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($report['summary']['verified_amount'], 0, ',', '.'); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Jumlah Transaksi</p><p class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($report['summary']['transaction_count']); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Pending</p><p class="text-2xl font-black text-amber-600 mt-2"><?php echo number_format($report['summary']['pending_count']); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Verified</p><p class="text-2xl font-black text-green-600 mt-2"><?php echo number_format($report['summary']['verified_count']); ?></p></div>
        </div>

        <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Waktu</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Customer</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Kode</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($report['transactions'])): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 font-bold">Tidak ada data pada filter ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($report['transactions'] as $trx): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-600"><?php echo date('H:i', strtotime($trx['created_at'])); ?></td>
                                    <td class="px-6 py-4 font-black text-gray-900"><?php echo htmlspecialchars($trx['customer_name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-brand-primary font-bold"><?php echo htmlspecialchars($trx['plan_code']); ?></td>
                                    <td class="px-6 py-4 font-black text-gray-900">Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($trx['transaction_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require 'views/admin/includes/footer.php'; ?>
