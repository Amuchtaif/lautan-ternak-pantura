<?php require_once 'includes/header.php'; ?>
<?php
$activePlans = (int)($stats['active_plans'] ?? 0);
$totalSaved = (float)($stats['total_saved'] ?? 0);
$totalTarget = (float)($stats['total_target'] ?? 0);
$overallProgress = $totalTarget > 0 ? min(100, round(($totalSaved / $totalTarget) * 100, 2)) : 0;
?>
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Tabungan Qurban</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola rencana, setoran, dan progress target qurban Anda.</p>
            </div>
            <a href="/lautan-ternak-pantura/tabungan#form-registrasi" class="inline-flex items-center justify-center gap-2 bg-brand-primary text-white px-5 py-3 rounded-lg font-bold text-sm hover:bg-brand-dark transition">
                <i class="fas fa-plus"></i> Buat Tabungan
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-100 rounded-lg p-5">
                <p class="text-xs font-bold text-gray-400 uppercase">Tabungan Aktif</p>
                <p class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($activePlans); ?></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-lg p-5">
                <p class="text-xs font-bold text-gray-400 uppercase">Saldo Terkumpul</p>
                <p class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($totalSaved, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-lg p-5">
                <p class="text-xs font-bold text-gray-400 uppercase">Progress Target</p>
                <p class="text-2xl font-black text-gray-900 mt-2"><?php echo $overallProgress; ?>%</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-lg p-5">
                <p class="text-xs font-bold text-gray-400 uppercase">Target Terdekat</p>
                <p class="text-lg font-black text-gray-900 mt-2"><?php echo !empty($stats['nearest_target_date']) ? date('d M Y', strtotime($stats['nearest_target_date'])) : '-'; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-black text-gray-900">Rencana Tabungan</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($plans)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <p class="font-bold">Belum ada rencana tabungan.</p>
                            <a href="/lautan-ternak-pantura/tabungan#form-registrasi" class="text-brand-primary font-black text-sm inline-block mt-3">Mulai menabung</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <?php $progress = $plan['target_amount'] > 0 ? min(100, round(($plan['current_amount'] / $plan['target_amount']) * 100, 2)) : 0; ?>
                            <a href="/lautan-ternak-pantura/savings/detail/<?php echo (int)$plan['id']; ?>" class="block p-6 hover:bg-gray-50 transition">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black text-brand-primary uppercase"><?php echo htmlspecialchars($plan['plan_code']); ?></p>
                                        <h3 class="text-lg font-black text-gray-900 mt-1"><?php echo htmlspecialchars($plan['livestock_target']); ?></h3>
                                        <p class="text-sm text-gray-500 mt-1">Target Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?> sampai <?php echo date('d M Y', strtotime($plan['target_date'])); ?></p>
                                    </div>
                                    <div class="md:w-56">
                                        <div class="flex justify-between text-xs font-bold text-gray-500 mb-2">
                                            <span>Rp <?php echo number_format($plan['current_amount'], 0, ',', '.'); ?></span>
                                            <span><?php echo $progress; ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                            <div class="h-full bg-brand-primary rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-black text-gray-900">Transaksi Terakhir</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="p-6 text-sm text-gray-500 font-bold">Belum ada setoran.</div>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $trx): ?>
                            <div class="p-5 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-black text-gray-900">Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></p>
                                    <p class="text-xs text-gray-400 font-bold mt-1"><?php echo date('d M Y H:i', strtotime($trx['created_at'])); ?></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $trx['transaction_status'] === 'verified' ? 'bg-green-50 text-green-600' : ($trx['transaction_status'] === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'); ?>">
                                    <?php echo htmlspecialchars($trx['transaction_status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
