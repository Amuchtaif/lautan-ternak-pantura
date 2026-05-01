<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if user is logged in and is a customer
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit;
}

$customerId = $_SESSION['user_id'];

try {
    // 1. Fetch Active Savings Plan
    $stmt = $conn->prepare("
        SELECT sp.*, l.type as animal_type, l.image_url as animal_image, l.price as animal_price
        FROM savings_plans sp
        LEFT JOIN livestock l ON sp.livestock_id = l.id
        WHERE sp.customer_id = ? AND sp.status = 'active'
        ORDER BY sp.created_at DESC LIMIT 1
    ");
    $stmt->execute([$customerId]);
    $activePlan = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Total Saved
    $totalSaved = 0;
    if ($activePlan) {
        $stmt = $conn->prepare("SELECT SUM(amount) FROM savings_transactions WHERE plan_id = ? AND status = 'verified'");
        $stmt->execute([$activePlan['id']]);
        $totalSaved = $stmt->fetchColumn() ?: 0;
    }

    // 3. Recent Transactions
    $stmt = $conn->prepare("
        SELECT st.*, sp.target_amount, l.type as animal_type
        FROM savings_transactions st
        JOIN savings_plans sp ON st.plan_id = sp.id
        LEFT JOIN livestock l ON sp.livestock_id = l.id
        WHERE sp.customer_id = ?
        ORDER BY st.created_at DESC LIMIT 5
    ");
    $stmt->execute([$customerId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}

require_once 'includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Dashboard <span class="text-brand-primary">Tabungan</span></h1>
                <p class="mt-1 text-sm text-gray-400 font-bold uppercase tracking-widest">Selamat datang kembali, <?php echo $_SESSION['name']; ?>!</p>
            </div>
            <a href="/lautan-ternak-pantura/marketplace" class="bg-brand-primary text-white px-6 py-3 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                <i class="fas fa-search"></i> Cari Hewan
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center gap-4 text-emerald-700 animate-in fade-in slide-in-from-top-4">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold">Rencana tabungan Anda telah berhasil dibuat! Mulai menabung sekarang.</p>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-[2rem] shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Tabungan</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo number_format($totalSaved, 0, ',', '.'); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-[2rem] shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Target Menabung</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo $activePlan ? number_format($activePlan['target_amount'], 0, ',', '.') : '0'; ?></p>
                </div>
            </div>
            <div class="bg-white rounded-[2rem] shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Cicilan / Bulan</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo $activePlan ? number_format($activePlan['monthly_installment'], 0, ',', '.') : '0'; ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Rencana Tabungan Aktif -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden flex flex-col">
                <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Rencana Tabungan</h3>
                    <?php if ($activePlan): ?>
                        <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest">Aktif</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-400 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest">Tidak Ada</span>
                    <?php endif; ?>
                </div>
                
                <div class="p-10 flex-grow flex flex-col justify-center">
                    <?php if ($activePlan): ?>
                        <div class="flex items-center gap-6 mb-8">
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 overflow-hidden border border-gray-100 shrink-0">
                                <img src="<?php echo $activePlan['animal_image'] ?: 'https://images.unsplash.com/photo-1524024973431-2ad916746881?auto=format&fit=crop&q=80'; ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-gray-900 capitalize leading-tight"><?php echo $activePlan['animal_type'] ?: 'Target Kustom'; ?></h4>
                                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">Target Tercapai: <?php echo floor(($totalSaved / $activePlan['target_amount']) * 100); ?>%</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-100 rounded-full h-4 mb-8 p-1 overflow-hidden">
                            <div class="bg-brand-primary h-full rounded-full transition-all duration-1000 shadow-lg shadow-brand-primary/20" style="width: <?php echo ($totalSaved / $activePlan['target_amount']) * 100; ?>%"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="p-4 rounded-2xl bg-gray-50">
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Terkumpul</p>
                                <p class="text-lg font-black text-brand-primary">Rp <?php echo number_format($totalSaved, 0, ',', '.'); ?></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 text-right">
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Kekurangan</p>
                                <p class="text-lg font-black text-gray-900">Rp <?php echo number_format($activePlan['target_amount'] - $totalSaved, 0, ',', '.'); ?></p>
                            </div>
                        </div>

                        <button onclick="openPaymentModal(<?php echo $activePlan['id']; ?>, <?php echo (int)$activePlan['monthly_installment']; ?>)" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-paper-plane"></i> Setor Tabungan (Bukti Transfer)
                        </button>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-6 text-gray-300 text-3xl">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <p class="text-gray-400 font-bold mb-8">Anda belum memiliki rencana tabungan aktif.</p>
                            <a href="/lautan-ternak-pantura/tabungan" class="inline-flex items-center gap-2 text-brand-primary font-black uppercase text-xs tracking-widest hover:gap-4 transition-all">
                                Mulai Menabung Sekarang <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Riwayat Setoran -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                <div class="px-10 py-8 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Riwayat Setoran</h3>
                </div>
                <div class="p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="p-10 text-center text-gray-400 font-bold">Belum ada riwayat transaksi.</div>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-50">
                            <?php foreach ($transactions as $trx): ?>
                                <li class="px-10 py-6 hover:bg-gray-50/50 flex items-center justify-between transition-all group">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 rounded-2xl <?php echo $trx['status'] === 'verified' ? 'bg-emerald-50 text-emerald-600' : ($trx['status'] === 'rejected' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600'); ?> flex items-center justify-center text-lg transition-transform group-hover:scale-110">
                                            <i class="fas <?php echo $trx['status'] === 'verified' ? 'fa-check' : ($trx['status'] === 'rejected' ? 'fa-xmark' : 'fa-clock'); ?>"></i>
                                        </div>
                                        <div class="ml-5">
                                            <p class="text-sm font-black text-gray-900 leading-none">Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?></p>
                                            <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest"><?php echo date('d M Y, H:i', strtotime($trx['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $trx['status'] === 'verified' ? 'bg-emerald-50 text-emerald-600' : ($trx['status'] === 'rejected' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600'); ?>">
                                            <?php echo $trx['status'] === 'verified' ? 'Berhasil' : ($trx['status'] === 'rejected' ? 'Ditolak' : 'Pending'); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0" id="modal-content">
        <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-xl font-black text-gray-900 tracking-tight">Setor Tabungan</h3>
            <button onclick="closePaymentModal()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
        </div>
        <form id="payment-form" action="/lautan-ternak-pantura/api/savings/deposit" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
            <input type="hidden" name="plan_id" id="modal-plan-id">
            
            <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100">
                <p class="text-[10px] text-blue-400 font-black uppercase tracking-widest mb-3">Tujuan Transfer</p>
                <div class="flex items-center gap-4">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" class="h-6 w-auto">
                    <div>
                        <p class="text-sm font-black text-gray-900">8610 9928 11</p>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">A/N LAUTAN TERNAK PANTURA</p>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nominal Setoran</label>
                <div class="relative">
                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-black text-sm">Rp</span>
                    <input type="number" name="amount" id="modal-amount" required class="w-full pl-14 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-black text-lg">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Bukti Transfer (Gambar)</label>
                <div id="drop-zone" class="relative group cursor-pointer">
                    <input type="file" name="proof" id="proof-input" accept="image/*" required class="hidden">
                    <div id="upload-ui" class="w-full py-10 bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-3 group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5 transition-all">
                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-400 group-hover:text-brand-primary transition-all">
                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all" id="filename-text">Klik atau tarik gambar ke sini</p>
                    </div>
                    <div id="preview-container" class="hidden absolute inset-0 rounded-3xl overflow-hidden bg-white">
                        <img id="image-preview" class="w-full h-full object-cover">
                        <button type="button" onclick="resetImage()" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-black/40 backdrop-blur-md text-white flex items-center justify-center hover:bg-black/60 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-3">
                <i class="fas fa-check-circle"></i> Kirim Konfirmasi Pembayaran
            </button>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(planId, amount) {
        document.getElementById('modal-plan-id').value = planId;
        document.getElementById('modal-amount').value = amount;
        
        const overlay = document.getElementById('payment-modal');
        const content = document.getElementById('modal-content');
        
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(() => {
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-90', 'opacity-0');
        }, 10);
    }

    function closePaymentModal() {
        const overlay = document.getElementById('payment-modal');
        const content = document.getElementById('modal-content');
        
        overlay.classList.remove('opacity-100');
        content.classList.add('scale-90', 'opacity-0');
        setTimeout(() => {
            overlay.classList.remove('flex');
            overlay.classList.add('hidden');
        }, 300);
    }

    // Image Upload Logic
    const proofInput = document.getElementById('proof-input');
    const dropZone = document.getElementById('drop-zone');
    const uploadUi = document.getElementById('upload-ui');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const filenameText = document.getElementById('filename-text');

    dropZone.addEventListener('click', () => proofInput.click());

    proofInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(this.files[0]);
            filenameText.innerText = this.files[0].name;
        }
    });

    function resetImage() {
        proofInput.value = '';
        previewContainer.classList.add('hidden');
        filenameText.innerText = 'Klik atau tarik gambar ke sini';
    }
</script>

<?php require_once 'includes/footer.php'; ?>

