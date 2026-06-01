<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Kas & Rekening Bank';
    $topbarSubtitle = 'Pengelolaan saldo tunai dan akun bank operasional LTP';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary shadow-sm">
                    <i class="fas fa-building-columns text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kas & <span class="text-brand-primary">Bank</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Kelola rekening penampungan modal, operasional & tabungan.</p>
                </div>
            </div>
            <button onclick="openAddModal()" class="bg-brand-primary hover:bg-brand-dark text-white px-6 py-3.5 rounded-2xl shadow-xl shadow-brand-primary/25 transition-all text-xs font-black uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-plus-circle text-sm"></i> Tambah Rekening
            </button>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['success_msg'])); ?>", "success");
                });
            </script>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['error'])); ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Cash Accounts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if (empty($accounts)): ?>
                <div class="col-span-3 bg-white rounded-3xl p-16 border border-gray-100 text-center text-gray-400 font-bold">
                    <i class="fas fa-wallet text-4xl mb-4 text-gray-200 block"></i>
                    Belum ada rekening kas terdaftar. Silakan tambahkan rekening kas baru.
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): ?>
                    <?php
                    $isBank = $acc['type'] === 'bank';
                    $themeClass = $isBank ? 'bg-gradient-to-tr from-brand-primary/5 to-blue-500/5 border-brand-primary/10' : 'bg-gradient-to-tr from-emerald-50 to-teal-100/50 border-emerald-200/50';
                    $iconClass = $isBank ? 'bg-brand-primary text-white' : 'bg-emerald-500 text-white';
                    $badgeClass = $acc['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500';
                    ?>
                    <div class="bg-white border rounded-3xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between space-y-6 <?php echo $themeClass; ?>">
                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 rounded-2xl flex items-center justify-center text-sm shadow-md <?php echo $iconClass; ?>">
                                        <i class="fas <?php echo $isBank ? 'fa-building-columns' : 'fa-wallet'; ?>"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-gray-900 tracking-tight leading-none"><?php echo htmlspecialchars($acc['name']); ?></h3>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1.5 block"><?php echo $isBank ? $acc['bank_name'] : 'Kas Tunai'; ?></span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded <?php echo $badgeClass; ?>">
                                    <?php echo $acc['status']; ?>
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 line-clamp-2 min-h-8">
                                <?php echo htmlspecialchars($acc['description'] ?: 'Tidak ada keterangan.'); ?>
                            </p>

                            <?php if ($isBank): ?>
                                <div class="bg-white/60 backdrop-blur-sm border border-gray-100/80 rounded-xl p-3 flex justify-between items-center text-xs font-bold text-gray-500">
                                    <span>No. Rekening:</span>
                                    <span class="text-gray-800 font-black tracking-wide font-mono"><?php echo htmlspecialchars($acc['account_number']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-gray-100/80 pt-4 flex justify-between items-end">
                            <div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider block">Saldo Terkini</span>
                                <h4 class="text-xl font-extrabold text-gray-900 mt-1">Rp <?php echo number_format($acc['current_balance'], 0, ',', '.'); ?></h4>
                            </div>
                            
                            <div class="flex gap-1.5 print:hidden">
                                <button onclick='openEditModal(<?php echo json_encode($acc, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="h-9 w-9 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 flex items-center justify-center text-xs shadow-sm transition-all">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteModal(<?php echo $acc['id']; ?>, '<?php echo htmlspecialchars($acc['name'], ENT_QUOTES); ?>')" class="h-9 w-9 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-red-600 hover:border-red-100 flex items-center justify-center text-xs shadow-sm transition-all">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Floating Add Modal -->
<div id="add-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-plus-circle text-brand-primary"></i> Tambah Rekening Baru
            </h3>
            <button onclick="closeModal('add')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/cash" method="POST" class="space-y-4" onsubmit="return validateForm('add')">
            <input type="hidden" name="action" value="add">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Rekening</label>
                <input type="text" name="name" required placeholder="Contoh: BCA Utama, Kas Kecil..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Jenis Rekening</label>
                <div class="relative">
                    <select name="type" id="add-type" onchange="toggleAccountFields('add')" class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="bank">Bank (Rekening Transfer)</option>
                        <option value="cash">Kas Tunai (Fisik / Cash)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div id="add-bank-fields" class="space-y-4">
                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Bank</label>
                    <input type="text" name="bank_name" id="add-bank_name" placeholder="Contoh: BCA, BSI, Mandiri..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nomor Rekening</label>
                    <input type="text" name="account_number" id="add-account_number" placeholder="Nomor rekening bank..." class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Saldo Awal (Rp)</label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="text" name="opening_balance" oninput="formatCurrency(this)" placeholder="0" class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Status Keaktifan</label>
                <div class="relative">
                    <select name="status" class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none">
                        <option value="active">Active (Aktif)</option>
                        <option value="inactive">Inactive (Nonaktif)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Keterangan / Deskripsi</label>
                <textarea name="description" placeholder="Catatan kegunaan rekening kas..." rows="2" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('add')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-edit text-brand-primary"></i> Edit Rekening Kas
            </h3>
            <button onclick="closeModal('edit')" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="/lautan-ternak-pantura/finance/cash" method="POST" class="space-y-4" onsubmit="return validateForm('edit')">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Rekening</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Jenis Rekening</label>
                <div class="relative">
                    <select name="type" id="edit-type" onchange="toggleAccountFields('edit')" class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none cursor-pointer">
                        <option value="bank">Bank (Rekening Transfer)</option>
                        <option value="cash">Kas Tunai (Fisik / Cash)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div id="edit-bank-fields" class="space-y-4">
                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nama Bank</label>
                    <input type="text" name="bank_name" id="edit-bank_name" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Nomor Rekening</label>
                    <input type="text" name="account_number" id="edit-account_number" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Status Keaktifan</label>
                <div class="relative">
                    <select name="status" id="edit-status" class="w-full pl-5 pr-10 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 appearance-none">
                        <option value="active">Active (Aktif)</option>
                        <option value="inactive">Inactive (Nonaktif)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Keterangan / Deskripsi</label>
                <textarea name="description" id="edit-description" rows="2" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-xs font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                <button type="button" onclick="closeModal('edit')" class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all uppercase tracking-wider">Batal</button>
                <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded-xl text-xs font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20 uppercase tracking-wider">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300 animate-all">
        <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-4 text-lg">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-lg font-black text-gray-900">Hapus Rekening Kas?</h3>
        <p class="mt-2 text-xs text-gray-500 leading-relaxed">Apakah Anda yakin ingin menghapus rekening <span id="delete-name" class="font-black text-gray-700"></span>? Akun kas yang sudah memiliki riwayat transaksi arus kas tidak dapat dihapus untuk audit.</p>
        
        <form action="/lautan-ternak-pantura/finance/cash" method="POST" class="mt-6 flex justify-end gap-3">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete-id">
            <button type="button" onclick="closeModal('delete')" class="px-5 py-3 border border-gray-100 text-gray-500 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all">Batal</button>
            <button type="submit" class="px-5 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black transition-all shadow-md shadow-red-500/10">Hapus</button>
        </form>
    </div>
</div>

<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function toggleAccountFields(prefix) {
        const typeSelect = document.getElementById(prefix + '-type');
        const bankFields = document.getElementById(prefix + '-bank-fields');
        const bankName = document.getElementById(prefix + '-bank_name');
        const accountNum = document.getElementById(prefix + '-account_number');

        if (typeSelect.value === 'cash') {
            bankFields.classList.add('hidden');
            bankName.removeAttribute('required');
            accountNum.removeAttribute('required');
            bankName.value = '';
            accountNum.value = '';
        } else {
            bankFields.classList.remove('hidden');
            bankName.setAttribute('required', 'true');
            accountNum.setAttribute('required', 'true');
        }
    }

    function openAddModal() {
        const modal = document.getElementById('add-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleAccountFields('add');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openEditModal(acc) {
        document.getElementById('edit-id').value = acc.id;
        document.getElementById('edit-name').value = acc.name;
        document.getElementById('edit-type').value = acc.type;
        document.getElementById('edit-bank_name').value = acc.bank_name || '';
        document.getElementById('edit-account_number').value = acc.account_number || '';
        document.getElementById('edit-status').value = acc.status;
        document.getElementById('edit-description').value = acc.description || '';

        const modal = document.getElementById('edit-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleAccountFields('edit');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openDeleteModal(id, name) {
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-name').innerText = name;
        const modal = document.getElementById('delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closeModal(prefix) {
        const modal = document.getElementById(prefix + '-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function validateForm(prefix) {
        const typeSelect = document.getElementById(prefix + '-type').value;
        if (typeSelect === 'bank') {
            const bankName = document.getElementById(prefix + '-bank_name').value.trim();
            const accountNum = document.getElementById(prefix + '-account_number').value.trim();
            if (bankName === '' || accountNum === '') {
                showToast('Nama bank dan nomor rekening wajib diisi untuk rekening jenis Bank!', 'error');
                return false;
            }
        }
        return true;
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
