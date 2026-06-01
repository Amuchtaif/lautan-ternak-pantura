<?php require 'views/admin/includes/header.php'; ?>
<?php
$totalCustomers = (int)($stats['total_customers'] ?? 0);
$totalCollected = (float)($stats['total_collected'] ?? 0);
$completedPlans = (int)($stats['completed_plans'] ?? 0);
$dueSoon = (int)($stats['due_soon'] ?? 0);
?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Manajemen <span class="text-brand-primary">Tabungan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Pantau semua rencana dan setoran tabungan qurban.</p>
            </div>
            <a href="/lautan-ternak-pantura/savingsReport/daily" class="bg-white border border-gray-100 text-brand-primary px-5 py-3 rounded-lg font-black text-sm hover:bg-brand-light transition">
                <i class="fas fa-chart-line mr-2"></i>Laporan
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Nasabah</p><p class="text-2xl font-black text-gray-900 mt-2"><?php echo number_format($totalCustomers); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100 md:col-span-2"><p class="text-xs font-black text-gray-400 uppercase">Dana Terkumpul</p><p class="text-2xl font-black text-brand-primary mt-2">Rp <?php echo number_format($totalCollected, 0, ',', '.'); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Pending</p><p class="text-2xl font-black text-amber-600 mt-2"><?php echo number_format($pendingTransactions); ?></p></div>
            <div class="bg-white p-5 rounded-lg border border-gray-100"><p class="text-xs font-black text-gray-400 uppercase">Selesai</p><p class="text-2xl font-black text-green-600 mt-2"><?php echo number_format($completedPlans); ?></p></div>
        </div>

        <div class="bg-white rounded-lg border border-gray-100 p-5">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select name="status" class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
                    <option value="">Semua Status</option>
                    <?php foreach (['active', 'completed', 'overdue', 'cancelled'] as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($_GET['status'] ?? '') === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="customer" value="<?php echo htmlspecialchars($_GET['customer'] ?? ''); ?>" placeholder="Cari customer / kode" class="md:col-span-2 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg font-bold text-sm">
                <button class="bg-brand-primary text-white rounded-lg font-black text-sm">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="savings-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase w-16">No</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Kode</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Nasabah</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Target</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($plans)): ?>
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 font-bold">Data tabungan belum ada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($plans as $i => $plan): ?>
                                <?php $progress = $plan['target_amount'] > 0 ? min(100, round(($plan['current_amount'] / $plan['target_amount']) * 100, 2)) : 0; ?>
                                <tr>
                                    <td class="px-6 py-4"><p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p></td>
                                    <td class="px-6 py-4"><p class="font-black text-brand-primary"><?php echo htmlspecialchars($plan['plan_code']); ?></p><p class="text-xs text-gray-400"><?php echo htmlspecialchars($plan['livestock_target']); ?></p></td>
                                    <td class="px-6 py-4"><p class="font-black text-gray-900"><?php echo htmlspecialchars($plan['customer_name']); ?></p><p class="text-xs text-gray-400"><?php echo htmlspecialchars($plan['customer_email']); ?></p></td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-700">Rp <?php echo number_format($plan['target_amount'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4 min-w-48"><div class="flex justify-between text-xs font-bold text-gray-500 mb-1"><span>Rp <?php echo number_format($plan['current_amount'], 0, ',', '.'); ?></span><span><?php echo $progress; ?>%</span></div><div class="h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-brand-primary" style="width: <?php echo $progress; ?>%"></div></div></td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $plan['status'] === 'completed' ? 'bg-green-50 text-green-600' : ($plan['status'] === 'cancelled' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'); ?>"><?php echo htmlspecialchars($plan['status']); ?></span></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex gap-2">
                                            <a href="/lautan-ternak-pantura/savings/adminDetail/<?php echo (int)$plan['id']; ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-light text-brand-primary hover:bg-brand-primary hover:text-white transition" title="Detail"><i class="fas fa-eye"></i></a>
                                            <button type="button"
                                                onclick='openEditModal(<?php echo json_encode([
                                                    'id' => (int)$plan['id'],
                                                    'livestock_target' => $plan['livestock_target'],
                                                    'target_amount' => (float)$plan['target_amount'],
                                                    'duration_month' => (int)$plan['duration_month'],
                                                    'target_date' => $plan['target_date'],
                                                    'status' => $plan['status'],
                                                    'notes' => $plan['notes'] ?? ''
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition" title="Edit"><i class="fas fa-pen"></i></button>
                                            <button type="button" onclick="openDeleteModal(<?php echo (int)$plan['id']; ?>, '<?php echo htmlspecialchars($plan['plan_code'], ENT_QUOTES); ?>')" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition" title="Delete"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer with Pagination -->
            <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0">
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
        </div>
    </main>
</div>

<div id="edit-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-gray-100 p-6">
            <h3 class="text-xl font-black text-gray-900">Edit Data Tabungan</h3>
            <button type="button" onclick="closeEditModal()" class="h-10 w-10 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200"><i class="fas fa-times"></i></button>
        </div>
        <form id="edit-form" class="grid gap-4 p-6 sm:grid-cols-2">
            <input type="hidden" name="id" id="edit-id">
            <div class="sm:col-span-2"><label class="text-xs font-black uppercase text-gray-400">Target Qurban</label><input name="livestock_target" id="edit-target" class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-bold outline-none focus:border-brand-primary"></div>
            <div><label class="text-xs font-black uppercase text-gray-400">Target Nominal</label><input name="target_amount" id="edit-amount" type="number" min="100000" class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-bold outline-none focus:border-brand-primary"></div>
            <div><label class="text-xs font-black uppercase text-gray-400">Durasi Bulan</label><input name="duration_month" id="edit-duration" type="number" min="1" max="60" class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-bold outline-none focus:border-brand-primary"></div>
            <div><label class="text-xs font-black uppercase text-gray-400">Target Tanggal</label><input name="target_date" id="edit-date" type="date" class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-bold outline-none focus:border-brand-primary"></div>
            <div><label class="text-xs font-black uppercase text-gray-400">Status</label><select name="status" id="edit-status" class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 font-bold outline-none focus:border-brand-primary"><option value="active">Active</option><option value="completed">Completed</option><option value="overdue">Overdue</option><option value="cancelled">Cancelled</option></select></div>
            <div class="sm:col-span-2"><label class="text-xs font-black uppercase text-gray-400">Catatan</label><textarea name="notes" id="edit-notes" rows="3" class="mt-2 w-full resize-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-brand-primary"></textarea></div>
            <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="rounded-xl bg-gray-100 px-5 py-3 font-black text-gray-600">Batal</button>
                <button type="submit" id="edit-submit" class="rounded-xl bg-brand-primary px-5 py-3 font-black text-white hover:bg-brand-dark">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-4"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 class="text-xl font-black text-gray-900">Hapus Data Tabungan?</h3>
        <p class="mt-2 text-sm text-gray-600">Data akan dibatalkan agar transaksi terkait tetap aman dan bisa diaudit. Kode: <span id="delete-code" class="font-black"></span></p>
        <input type="hidden" id="delete-id">
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()" class="rounded-xl bg-gray-100 px-5 py-3 font-black text-gray-600">Batal</button>
            <button type="button" onclick="deletePlan()" id="delete-submit" class="rounded-xl bg-red-600 px-5 py-3 font-black text-white hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<script>
function openEditModal(plan) {
    document.getElementById('edit-id').value = plan.id;
    document.getElementById('edit-target').value = plan.livestock_target || '';
    document.getElementById('edit-amount').value = Math.round(plan.target_amount || 0);
    document.getElementById('edit-duration').value = plan.duration_month || 1;
    document.getElementById('edit-date').value = plan.target_date || '';
    document.getElementById('edit-status').value = plan.status || 'active';
    document.getElementById('edit-notes').value = plan.notes || '';
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}
function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}
function openDeleteModal(id, code) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-code').textContent = code;
    const modal = document.getElementById('delete-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}
function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}
document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('edit-submit');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';
    const res = await fetch('/lautan-ternak-pantura/api/admin/update_savings_plan', { method: 'POST', body: new FormData(this) });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 700);
    btn.disabled = false;
    btn.textContent = 'Simpan';
});
async function deletePlan() {
    const btn = document.getElementById('delete-submit');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';
    const res = await fetch('/lautan-ternak-pantura/api/admin/delete_savings_plan', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: document.getElementById('delete-id').value})
    });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 700);
    btn.disabled = false;
    btn.textContent = 'Delete';
}

// Client-side pagination engine
let currentPage = 1;
let rowsPerPage = 10;
let tableRows = [];

function initPagination() {
    tableRows = Array.from(document.querySelectorAll('#savings-table tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
    showPage(1);
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

    // Apply opacity styles for disabled state
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
