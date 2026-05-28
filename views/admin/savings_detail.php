<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <div>
            <a href="/lautan-ternak-pantura/savings/management" class="text-sm font-bold text-brand-primary"><i class="fas fa-arrow-left mr-2"></i>Manajemen Tabungan</a>
            <h1 class="text-2xl font-black text-gray-900 mt-3"><?php echo htmlspecialchars($plan['plan_code']); ?></h1>
            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($plan['customer_name']); ?> - <?php echo htmlspecialchars($plan['livestock_target']); ?></p>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
                <div><p class="text-xs font-black text-gray-400 uppercase">Saldo</p><p class="text-2xl font-black text-brand-primary">Rp <?php echo number_format($plan['current_amount'], 0, ',', '.'); ?></p></div>
                <div><p class="text-xs font-black text-gray-400 uppercase">Target</p><p class="text-2xl font-black text-gray-900">Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?></p></div>
                <div><p class="text-xs font-black text-gray-400 uppercase">Bulanan</p><p class="text-2xl font-black text-gray-900">Rp <?php echo number_format($plan['monthly_target'], 0, ',', '.'); ?></p></div>
                <div><p class="text-xs font-black text-gray-400 uppercase">Status</p><p class="text-2xl font-black text-gray-900"><?php echo ucfirst($plan['status']); ?></p></div>
            </div>
            <div class="flex justify-between text-sm font-bold text-gray-500 mb-2"><span>Progress</span><span><?php echo $progress; ?>%</span></div>
            <div class="h-4 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-brand-primary" style="width: <?php echo $progress; ?>%"></div></div>
        </div>

        <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h2 class="font-black text-gray-900">Audit Transaksi</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Tanggal</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nominal</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Metode</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Bukti</th><th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th><th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($transactions as $trx): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold text-gray-600"><?php echo date('d M Y H:i', strtotime($trx['created_at'])); ?></td>
                                <td class="px-6 py-4 font-black text-gray-900">Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-600 capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $trx['payment_method'])); ?></td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($trx['payment_proof']) && strpos($trx['payment_proof'], '/storage/uploads/') !== false): ?>
                                        <button type="button" onclick="openProofLightbox('<?php echo htmlspecialchars($trx['payment_proof'], ENT_QUOTES); ?>')" class="group inline-flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-2 pr-3 hover:border-brand-primary/30 hover:bg-brand-light/30 transition">
                                            <img src="<?php echo htmlspecialchars($trx['payment_proof']); ?>" alt="Bukti transfer" class="h-12 w-12 rounded-lg object-cover bg-white">
                                            <span class="text-sm font-black text-brand-primary"><i class="fas fa-magnifying-glass mr-1"></i>Preview</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-gray-400"><?php echo htmlspecialchars($trx['payment_method'] === 'cash' ? 'Cash' : 'Tidak ada file'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $trx['transaction_status'] === 'verified' ? 'bg-green-50 text-green-600' : ($trx['transaction_status'] === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'); ?>"><?php echo htmlspecialchars($trx['transaction_status']); ?></span></td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($trx['transaction_status'] === 'pending'): ?>
                                        <button type="button" onclick="openApprovalModal(<?php echo (int)$trx['id']; ?>, <?php echo htmlspecialchars(json_encode($trx)); ?>)" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white font-bold text-sm transition"><i class="fas fa-check-square mr-1"></i>Proses</button>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 font-bold"><?php echo $trx['verified_at'] ? date('d M Y', strtotime($trx['verified_at'])) : '-'; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<div id="proof-lightbox" class="fixed inset-0 z-[1200] hidden bg-gray-900/80 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="flex h-full w-full flex-col transform scale-95 transition-all duration-300">
        <div class="mb-3 flex items-center justify-between text-white">
            <p class="text-sm font-black uppercase tracking-widest">Preview Bukti Transfer</p>
            <div class="flex gap-2">
                <button type="button" onclick="toggleProofFullscreen()" class="rounded-xl bg-white/10 px-4 py-2 font-bold hover:bg-white/20"><i class="fas fa-expand mr-2"></i>Fullscreen</button>
                <button type="button" onclick="closeProofLightbox()" class="h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="flex min-h-0 flex-1 items-center justify-center">
            <img id="proof-lightbox-img" src="" alt="Preview bukti transfer" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl">
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approval-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[1200] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl transition-all duration-300 scale-90 opacity-0 overflow-hidden flex flex-col max-h-[95vh]" id="approval-modal-content">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
            <h3 class="text-lg font-black text-gray-900 tracking-tight">Proses Transaksi Tabungan</h3>
            <button type="button" onclick="closeApprovalModal()" class="w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 space-y-6 overflow-y-auto">
            <!-- Transaction Details -->
            <div class="grid grid-cols-2 gap-4 p-5 bg-gray-50 border border-gray-100 rounded-2xl">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Nominal</p>
                    <p class="text-xl font-black text-brand-primary" id="modal-amount">Rp -</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Metode</p>
                    <p class="text-lg font-black text-gray-900 capitalize" id="modal-method">-</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</p>
                    <p class="text-sm font-bold text-gray-700" id="modal-date">-</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100">Pending</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 mt-2">
                <button type="button" onclick="submitApproval('rejected')" class="w-full bg-red-50 text-red-600 border border-red-100 py-3.5 rounded-xl font-bold text-sm hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-times"></i> Tolak Transaksi
                </button>
                <button type="button" onclick="submitApproval('verified')" class="w-full bg-green-500 text-white border border-green-500 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-green-500/20 hover:bg-green-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Setujui Valid
                </button>
                <button type="button" onclick="closeApprovalModal()" class="col-span-2 w-full bg-gray-50 text-gray-600 border border-gray-200 py-3.5 rounded-xl font-bold text-sm hover:bg-gray-100 transition-all">
                    Tutup / Batal
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function openProofLightbox(src) {
    const modal = document.getElementById('proof-lightbox');
    document.getElementById('proof-lightbox-img').src = src;
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}
function closeProofLightbox() {
    const modal = document.getElementById('proof-lightbox');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.getElementById('proof-lightbox-img').src = '';
    }, 300);
}
function toggleProofFullscreen() {
    const modal = document.getElementById('proof-lightbox');
    if (!document.fullscreenElement && modal.requestFullscreen) modal.requestFullscreen();
    else if (document.exitFullscreen) document.exitFullscreen();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProofLightbox();
});

// Approval Modal Functions
let currentTransactionId = null;

function openApprovalModal(txId, txData) {
    try {
        const trx = typeof txData === 'string' ? JSON.parse(txData) : txData;
        currentTransactionId = txId;
        
        // Populate modal with transaction details
        document.getElementById('modal-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.amount);
        document.getElementById('modal-method').textContent = trx.payment_method.replace('_', ' ');
        document.getElementById('modal-date').textContent = new Date(trx.created_at).toLocaleDateString('id-ID', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const overlay = document.getElementById('approval-modal');
        const content = document.getElementById('approval-modal-content');
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(() => {
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-90', 'opacity-0');
        }, 10);
    } catch (e) {
        console.error('Error opening approval modal:', e);
    }
}

function closeApprovalModal() {
    const overlay = document.getElementById('approval-modal');
    const content = document.getElementById('approval-modal-content');
    
    overlay.classList.remove('opacity-100');
    content.classList.add('scale-90', 'opacity-0');
    setTimeout(() => {
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
        currentTransactionId = null;
    }, 300);
}

async function submitApproval(status) {
    if (!currentTransactionId) return;
    
    closeApprovalModal();
    
    const res = await fetch('/lautan-ternak-pantura/api/admin/verify_transfer', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: currentTransactionId, status})
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(data.message, 'error');
    }
}
</script>
<?php require 'views/admin/includes/footer.php'; ?>
