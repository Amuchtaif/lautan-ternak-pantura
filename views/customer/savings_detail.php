<?php require_once 'includes/header.php'; ?>
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="/lautan-ternak-pantura/savings" class="text-sm font-bold text-brand-primary"><i class="fas fa-arrow-left mr-2"></i>Dashboard Tabungan</a>
                <h1 class="text-2xl font-black text-gray-900 mt-3"><?php echo htmlspecialchars($plan['livestock_target']); ?></h1>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($plan['plan_code']); ?> - Target <?php echo date('d M Y', strtotime($plan['target_date'])); ?></p>
            </div>
            <?php if (in_array($plan['status'], ['active', 'overdue'], true)): ?>
                <button onclick="openDepositModal()" class="bg-brand-primary text-white px-5 py-3 rounded-lg font-bold hover:bg-brand-dark transition">
                    <i class="fas fa-upload mr-2"></i>Setor Tabungan
                </button>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg p-6">
            <?php $remainingTarget = max(0, (float)$plan['target_amount'] - (float)$plan['current_amount']); ?>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-6">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase">Terkumpul</p>
                    <p class="text-2xl font-black text-brand-primary mt-1">Rp <?php echo number_format($plan['current_amount'], 0, ',', '.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase">Target</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase">Cicilan Simulasi</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">Rp <?php echo number_format($plan['monthly_target'], 0, ',', '.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase">Sisa Target</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">Rp <?php echo number_format($remainingTarget, 0, ',', '.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase">Status</p>
                    <p class="text-2xl font-black text-gray-900 mt-1 capitalize"><?php echo htmlspecialchars($plan['status']); ?></p>
                </div>
            </div>
            <div class="flex justify-between text-sm font-bold text-gray-500 mb-2">
                <span>Progress</span>
                <span><?php echo $progress; ?>%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                <div class="h-full bg-brand-primary rounded-full" style="width: <?php echo $progress; ?>%"></div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="font-black text-gray-900">Riwayat Setoran</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Metode</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Diverifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 font-bold">Belum ada transaksi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $trx): ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-600"><?php echo date('d M Y H:i', strtotime($trx['created_at'])); ?></td>
                                    <td class="px-6 py-4 text-sm font-black text-gray-900">Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($trx['payment_method']); ?></td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $trx['transaction_status'] === 'verified' ? 'bg-green-50 text-green-600' : ($trx['transaction_status'] === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'); ?>"><?php echo htmlspecialchars($trx['transaction_status']); ?></span></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $trx['verified_at'] ? htmlspecialchars($trx['verifier_name'] ?? '-') . ' / ' . date('d M Y', strtotime($trx['verified_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="deposit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[1000] hidden items-center justify-center p-4 opacity-0 transition-all duration-300">
    <div id="deposit-panel" class="bg-white rounded-3xl w-full max-w-lg p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300 max-h-[95vh] overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-black text-gray-900">Setor Tabungan</h3>
            <button onclick="closeDepositModal()" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200"><i class="fas fa-times"></i></button>
        </div>
        <form id="deposit-form" action="/lautan-ternak-pantura/api/savings/deposit" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="savings_plan_id" value="<?php echo (int)$plan['id']; ?>">
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Nominal Setoran</label>
                <input type="text" id="deposit_amount_display" value="Rp <?php echo number_format((float)$plan['monthly_target'], 0, ',', '.'); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary">
                <input type="hidden" name="amount" id="deposit_amount" value="<?php echo (int)$plan['monthly_target']; ?>">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Tanggal Setor Tabungan</label>
                <input type="date" name="deposit_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Metode Pembayaran</label>
                <select name="payment_method" id="deposit_payment_method" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary">
                    <option value="transfer_bank">Transfer Bank</option>
                    <option value="cash">Tunai</option>
                </select>
            </div>
            <div id="deposit-transfer-info" class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-xs font-black uppercase tracking-widest text-blue-500">Info Transfer</p>
                <p class="mt-2 font-black text-gray-900">Bank BCA</p>
                <p class="text-sm text-gray-700">a.n Sohibuddin</p>
                <p class="text-sm text-gray-700">No Rekening: <span class="font-black">1341699695</span></p>
            </div>
            <div id="deposit-proof-field">
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Bukti Transfer (jpg/png/webp, maks 2MB)</label>
                <input type="file" name="payment_proof" id="deposit_payment_proof" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                <p id="deposit-proof-error" class="mt-1 hidden text-xs font-bold text-red-600"></p>
                <img id="deposit-proof-preview" src="" alt="Preview bukti transfer" class="mt-4 hidden max-h-56 w-full rounded-2xl border border-gray-100 object-contain bg-gray-50">
            </div>
            <button type="submit" id="deposit-submit" class="w-full bg-brand-primary text-white py-4 rounded-xl font-black hover:bg-brand-dark transition disabled:opacity-60">Kirim Setoran</button>
        </form>
    </div>
</div>
<script>
function formatDepositRupiah(value){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(value||0); }
function depositDigits(value){ return String(value||'').replace(/[^\d]/g,''); }
function syncDepositCurrency(){
    const display = document.getElementById('deposit_amount_display');
    const hidden = document.getElementById('deposit_amount');
    const amount = parseInt(depositDigits(display.value) || '0', 10);
    hidden.value = amount;
    display.value = amount ? formatDepositRupiah(amount) : '';
}
function updateDepositPaymentFields(){
    const method = document.getElementById('deposit_payment_method').value;
    const isTransfer = method !== 'cash';
    document.getElementById('deposit-transfer-info').classList.toggle('hidden', !isTransfer);
    document.getElementById('deposit-proof-field').classList.toggle('hidden', !isTransfer);
    document.getElementById('deposit_payment_proof').required = isTransfer;
}
function openDepositModal(){
    document.body.classList.add('overflow-hidden');
    const modal = document.getElementById('deposit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        document.getElementById('deposit-panel').classList.remove('scale-95');
    });
    updateDepositPaymentFields();
}
function closeDepositModal(){
    document.body.classList.remove('overflow-hidden');
    const modal = document.getElementById('deposit-modal');
    modal.classList.add('opacity-0');
    document.getElementById('deposit-panel').classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
}
document.getElementById('deposit_amount_display').addEventListener('input', syncDepositCurrency);
document.getElementById('deposit_payment_method').addEventListener('change', updateDepositPaymentFields);
document.getElementById('deposit_payment_proof').addEventListener('change', function(){
    const file = this.files[0];
    const error = document.getElementById('deposit-proof-error');
    const preview = document.getElementById('deposit-proof-preview');
    error.classList.add('hidden');
    preview.classList.add('hidden');
    if (!file) return;
    if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
        error.textContent = 'File harus jpg/png/webp dan maksimal 2MB.';
        error.classList.remove('hidden');
        this.value = '';
        return;
    }
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
});
document.getElementById('deposit-form').addEventListener('submit', function(){
    const btn = document.getElementById('deposit-submit');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
});
updateDepositPaymentFields();
</script>
<?php require_once 'includes/footer.php'; ?>
