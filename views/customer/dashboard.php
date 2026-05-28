<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and is a customer
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /lautan-ternak-pantura/auth/login");
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$customerId = $_SESSION['user_id'];

$activePlan = null;
$sohibulQurban = null;
$totalSaved = 0;
$transactions = [];

function customerSavingsColumnExists($conn, $table, $column) {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

if (isset($conn)) {
    try {
        $usesNewSavingsPlans = customerSavingsColumnExists($conn, 'savings_plans', 'plan_code');
        $usesNewSavingsTransactions = customerSavingsColumnExists($conn, 'savings_transactions', 'savings_plan_id');
        $txPlanColumn = $usesNewSavingsTransactions ? 'savings_plan_id' : 'plan_id';
        $txStatusColumn = $usesNewSavingsTransactions ? 'transaction_status' : 'status';
        $monthlyColumn = customerSavingsColumnExists($conn, 'savings_plans', 'monthly_target') ? 'monthly_target' : 'monthly_installment';
        $currentAmountExpr = customerSavingsColumnExists($conn, 'savings_plans', 'current_amount')
            ? 'sp.current_amount'
            : "(SELECT COALESCE(SUM(st.amount), 0) FROM savings_transactions st WHERE st.{$txPlanColumn} = sp.id AND st.{$txStatusColumn} = 'verified')";
        $livestockTargetExpr = customerSavingsColumnExists($conn, 'savings_plans', 'livestock_target') ? 'sp.livestock_target' : "'Hewan Qurban'";

        // 1. Fetch Active Savings Plan
        $stmt = $conn->prepare("
            SELECT sp.*, {$livestockTargetExpr} as animal_type, {$currentAmountExpr} AS normalized_current_amount,
                sp.{$monthlyColumn} AS normalized_monthly_target, NULL as animal_image, sp.target_amount as animal_price
            FROM savings_plans sp
            WHERE sp.customer_id = ? AND sp.status = 'active'
            ORDER BY sp.created_at DESC LIMIT 1
        ");
        $stmt->execute([$customerId]);
        $activePlan = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch Sohibul Qurban data for the active plan
        if ($activePlan) {
            $sohibulQurban = ['name' => $_SESSION['full_name'] ?? $_SESSION['name'] ?? '', 'relationship' => 'self', 'phone' => '', 'address' => $activePlan['notes'] ?? ''];
        }

        // 2. Total Saved
        if ($activePlan) {
            $activePlan['current_amount'] = $activePlan['normalized_current_amount'] ?? ($activePlan['current_amount'] ?? 0);
            $activePlan['monthly_target'] = $activePlan['normalized_monthly_target'] ?? ($activePlan['monthly_target'] ?? ($activePlan['monthly_installment'] ?? 0));
            $stmt = $conn->prepare("SELECT SUM(amount) FROM savings_transactions WHERE {$txPlanColumn} = ? AND {$txStatusColumn} = 'verified'");
            $stmt->execute([$activePlan['id']]);
            $totalSaved = $stmt->fetchColumn() ?: 0;
        }

        // 3. Recent Transactions
        $stmt = $conn->prepare("
            SELECT st.*, st.{$txStatusColumn} AS status, sp.target_amount, {$livestockTargetExpr} as animal_type
            FROM savings_transactions st
            JOIN savings_plans sp ON st.{$txPlanColumn} = sp.id
            WHERE sp.customer_id = ?
            ORDER BY st.created_at DESC LIMIT 5
        ");
        $stmt->execute([$customerId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php'; 
$onboardingMessage = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($onboardingMessage): ?>
            <div class="mb-6 rounded-lg border border-green-100 bg-green-50 px-6 py-5 text-green-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="font-black"><?php echo htmlspecialchars($onboardingMessage); ?></p>
                    <p class="text-sm font-bold text-green-700/80 mt-1">Akun Anda aktif. Mulai buat rencana tabungan atau pilih hewan qurban dari katalog.</p>
                </div>
                <a href="/lautan-ternak-pantura/savings/create" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg font-black text-sm">
                    Mulai Rencana
                </a>
            </div>
        <?php endif; ?>
        
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Dashboard <span class="text-brand-primary">Tabungan</span></h1>
                <p class="mt-1 text-sm text-gray-400 font-bold uppercase tracking-widest">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Sohibul Qurban'); ?>!</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="/lautan-ternak-pantura/views/customer/profile" class="bg-white text-gray-700 px-6 py-3 rounded-2xl shadow-sm border border-gray-100 hover:bg-gray-50 transition-all text-sm font-black flex items-center gap-2">
                    <i class="fas fa-user-cog"></i> Profil
                </a>
                <a href="/lautan-ternak-pantura/marketplace" class="bg-brand-primary text-white px-6 py-3 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                    <i class="fas fa-search"></i> Cari Hewan
                </a>
            </div>
        </div>



        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Tabungan</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo number_format($totalSaved, 0, ',', '.'); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Target Menabung</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo $activePlan ? number_format($activePlan['target_amount'], 0, ',', '.') : '0'; ?></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100 flex items-center group hover:shadow-xl transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="ml-6">
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Cicilan / Bulan</p>
                    <p class="text-2xl font-black text-gray-900">Rp <?php echo $activePlan ? number_format($activePlan['monthly_target'], 0, ',', '.') : '0'; ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Rencana Tabungan Aktif -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden flex flex-col">
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

                        <?php if ($sohibulQurban): ?>
                        <div class="mb-8 p-5 rounded-2xl bg-brand-light/40 border border-brand-primary/10">
                            <div class="flex items-center gap-2 mb-3">
                                <i class="fas fa-user-circle text-brand-primary"></i>
                                <span class="text-[10px] font-black text-brand-primary uppercase tracking-widest">Sohibul Qurban</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-black text-gray-900"><?php echo htmlspecialchars($sohibulQurban['name']); ?></p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                        <?php
                                        $relMap = ['self' => 'Diri Sendiri', 'ayah' => 'Ayah', 'ibu' => 'Ibu', 'kakek' => 'Kakek', 'nenek' => 'Nenek', 'suami' => 'Suami', 'istri' => 'Istri', 'anak' => 'Anak', 'keluarga' => 'Keluarga', 'lainnya' => 'Lainnya'];
                                        echo $relMap[$sohibulQurban['relationship']] ?? $sohibulQurban['relationship'];
                                        ?>
                                        <?php if ($sohibulQurban['phone']): ?> &middot; <?php echo htmlspecialchars($sohibulQurban['phone']); ?><?php endif; ?>
                                    </p>
                                </div>
                                <?php if ($sohibulQurban['address']): ?>
                                <div class="text-right max-w-[180px]">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alamat</p>
                                    <p class="text-xs text-gray-600"><?php echo htmlspecialchars($sohibulQurban['address']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

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

                        <button onclick="openPaymentModal(<?php echo $activePlan['id']; ?>, <?php echo (int)$activePlan['monthly_target']; ?>)" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-3">
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
            <div class="bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden">
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
<div id="payment-modal" class="fixed inset-0 z-[1000] hidden overflow-y-auto bg-black/60 backdrop-blur-md p-4 items-start sm:items-center justify-center">
    <div class="mx-auto mt-8 mb-8 w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300 scale-90 opacity-0" id="modal-content" style="max-height:calc(100vh - 3rem);">
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 px-8 py-6">
            <h3 class="text-xl font-black text-gray-900 tracking-tight">Setor Tabungan</h3>
            <button type="button" onclick="closePaymentModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <i class="fas fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="payment-form" action="/lautan-ternak-pantura/api/savings/deposit" method="POST" enctype="multipart/form-data" class="flex min-h-0 flex-col">
            <div class="min-h-0 overflow-y-auto p-8 space-y-8">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="savings_plan_id" id="modal-plan-id">
                
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
                        <input type="number" name="amount" id="modal-amount" required class="w-full rounded-2xl border-2 border-transparent bg-gray-50 py-4 pl-14 pr-6 text-lg font-black outline-none transition-all focus:border-brand-primary/20 focus:bg-white">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Bukti Transfer (JPG/PNG/WEBP, Maks 2MB)</label>
                    <div id="drop-zone" class="relative group cursor-pointer">
                        <input type="file" name="payment_proof" id="proof-input" accept="image/jpeg,image/png,image/webp" required class="hidden">
                        <div id="upload-ui" class="w-full rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 py-10 px-4 text-center transition group-hover:border-brand-primary/30 group-hover:bg-brand-primary/5">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm text-gray-400 transition group-hover:text-brand-primary">
                                <i class="fas fa-cloud-upload-alt text-xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-400 group-hover:text-brand-primary transition-all" id="filename-text">Klik atau tarik gambar ke sini</p>
                        </div>
                        <div id="preview-container" class="hidden absolute inset-0 overflow-hidden rounded-3xl bg-white">
                            <img id="image-preview" class="h-full w-full object-contain">
                            <button type="button" onclick="resetImage()" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-black/40 text-white transition hover:bg-black/60">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 left-0 z-10 border-t border-gray-100 bg-white px-8 py-5">
                <button type="submit" class="w-full rounded-2xl bg-brand-primary py-5 text-sm font-black text-white shadow-xl shadow-brand-primary/20 transition hover:bg-brand-dark">
                    <i class="fas fa-check-circle mr-2"></i> Kirim Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(planId, amount) {
        document.getElementById('modal-plan-id').value = planId;
        document.getElementById('modal-amount').value = amount;
        
        const overlay = document.getElementById('payment-modal');
        const content = document.getElementById('modal-content');
        
        document.body.style.overflow = 'hidden';
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
        
        document.body.style.overflow = '';
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

