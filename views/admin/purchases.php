<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Pembelian Hewan';
    $topbarSubtitle = 'Catat pembelian hewan dari penyuplai';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Histori <span
                        class="text-brand-primary">Pembelian Stok</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Catat pembelian hewan dari
                    penyuplai untuk menambah stok inventori</p>
            </div>
            <button type="button" onclick="openPurchaseModal()"
                class="bg-brand-primary text-white px-6 py-3.5 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                <i class="fas fa-plus"></i> Catat Pembelian Baru
            </button>
        </div>

        <!-- Session/GET Toast Alerts -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['success_msg'])); ?>", "success");
                });
            </script>
            <?php unset($_SESSION['success_msg']); ?>
        <?php elseif (isset($_GET['success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("Pembelian stok berhasil dicatat!", "success");
                });
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['error'])); ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="purchases-table">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">
                                No</th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Nama Hewan</th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Berat & Qty</th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Harga (Beli / Jual)</th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Pembeli / Tanggal</th>
                            <th
                                class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Catatan</th>
                            <th
                                class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-28">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($purchasesList)): ?>
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-gray-400 font-bold">
                                    Belum ada catatan pembelian stok masuk.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchasesList as $i => $purch): ?>
                                <tr class="hover:bg-brand-light/10 transition-all duration-200">
                                    <td class="px-8 py-6">
                                        <span class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-gray-900 capitalize">
                                            <?php echo htmlspecialchars($purch['livestock_name']); ?>
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">
                                            <?php echo htmlspecialchars($purch['breed']); ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-bold text-gray-700"><i
                                                class="fas fa-weight-hanging text-brand-primary mr-1 text-[11px]"></i><?php echo $purch['weight']; ?>
                                            kg</p>
                                        <p class="text-xs font-black text-gray-400 mt-1"><i
                                                class="fas fa-calculator text-gray-400 mr-1 text-[11px]"></i><?php echo $purch['qty']; ?>
                                            ekor</p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2.5">
                                                <span
                                                    class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-red-50 text-red-500 w-11 text-center shrink-0">Beli</span>
                                                <span class="text-xs font-bold text-gray-700">Rp
                                                    <?php echo number_format($purch['purchase_price'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2.5">
                                                <span
                                                    class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-emerald-50 text-emerald-600 w-11 text-center shrink-0">Jual</span>
                                                <span class="text-xs font-black text-brand-primary">Rp
                                                    <?php echo number_format($purch['selling_price'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div>
                                            <p class="text-xs font-bold text-gray-700">
                                                <?php echo htmlspecialchars($purch['admin_name']); ?>
                                            </p>
                                            <p class="text-[9px] text-gray-400 mt-0.5">
                                                <?php echo date('d M Y, H:i', strtotime($purch['purchased_at'])); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-xs text-gray-500 font-medium max-w-[150px] truncate"
                                            title="<?php echo htmlspecialchars($purch['notes']); ?>">
                                            <?php echo htmlspecialchars($purch['notes'] ?: '-'); ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                onclick='openEditModal(<?php echo htmlspecialchars(json_encode($purch), ENT_QUOTES, "UTF-8"); ?>)'
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-all"
                                                title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $purch['id']; ?>)"
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-all"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer with Pagination -->
            <div
                class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tampilkan</span>
                    <div class="relative">
                        <select id="entries-per-page" onchange="changeEntriesPerPage(this.value)"
                            class="pl-4 pr-10 py-2 bg-white border border-gray-100 rounded-lg outline-none focus:border-brand-primary text-xs font-bold text-gray-700 appearance-none cursor-pointer shadow-sm">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <i
                            class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">data</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="prevPage()" id="prev-btn"
                        class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i
                            class="fas fa-chevron-left text-xs"></i></button>
                    <div id="page-numbers" class="flex items-center gap-1.5">
                        <!-- Dynamic page numbers -->
                    </div>
                    <button onclick="nextPage()" id="next-btn"
                        class="w-8 h-8 rounded-lg border border-gray-100 bg-white flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm"><i
                            class="fas fa-chevron-right text-xs"></i></button>
                </div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider" id="entries-info"></span>
            </div>
        </div>
    </main>
</div>

<!-- Edit Purchase Modal -->
<div id="edit-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div
        class="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-pen-to-square text-brand-primary"></i> Edit Pembelian Stok
            </h3>
            <button onclick="closeModal('edit')"
                class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="/lautan-ternak-pantura/purchase/edit" method="POST" class="space-y-4">
            <input type="hidden" id="edit-id" name="id">

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nama Hewan</label>
                <input type="text" id="edit-name" name="livestock_name" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Breed /
                    Jenis</label>
                <input type="text" id="edit-breed" name="breed" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Berat
                        (kg)</label>
                    <input type="number" step="0.01" id="edit-weight" name="weight" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Qty
                        (ekor)</label>
                    <input type="number" id="edit-qty" name="qty" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Harga Beli
                        (Rp)</label>
                    <input type="number" id="edit-purchase-price" name="purchase_price" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Harga Jual
                        (Rp)</label>
                    <input type="number" id="edit-selling-price" name="selling_price" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Catatan</label>
                <textarea id="edit-notes" name="notes" rows="2"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('edit')"
                    class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Batal</button>
                <button type="submit"
                    class="px-6 py-3 bg-brand-primary text-white rounded-xl text-sm font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Purchase Modal -->
<div id="delete-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div
        class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300 text-center">
        <div
            class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fas fa-trash-can"></i>
        </div>
        <h3 class="text-lg font-black text-gray-900 mb-2">Hapus Catatan Pembelian?</h3>
        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-6">Tindakan ini tidak dapat dibatalkan
        </p>

        <form action="/lautan-ternak-pantura/purchase/delete" method="POST" class="flex gap-3">
            <input type="hidden" id="delete-id" name="id">
            <button type="button" onclick="closeModal('delete')"
                class="flex-1 px-5 py-3.5 border border-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Batal</button>
            <button type="submit"
                class="flex-1 px-5 py-3.5 bg-red-500 text-white rounded-xl text-sm font-black hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">Ya,
                Hapus</button>
        </form>
    </div>
</div>

<script>
    // Modal Animation Trigger
    function openEditModal(data) {
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-name').value = data.livestock_name;
        document.getElementById('edit-breed').value = data.breed;
        document.getElementById('edit-weight').value = data.weight;
        document.getElementById('edit-qty').value = data.qty;
        document.getElementById('edit-purchase-price').value = data.purchase_price;
        document.getElementById('edit-selling-price').value = data.selling_price;
        document.getElementById('edit-notes').value = data.notes || '';

        const modal = document.getElementById('edit-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function openDeleteModal(id) {
        document.getElementById('delete-id').value = id;
        const modal = document.getElementById('delete-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closeModal(type) {
        const modal = document.getElementById(type + '-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Client-side pagination engine
    let currentPage = 1;
    let rowsPerPage = 10;
    let tableRows = [];

    function initPagination() {
        tableRows = Array.from(document.querySelectorAll('#purchases-table tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
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
                btn.className = `w-8 h-8 rounded-lg text-xs font-black transition-all shadow-sm ${currentPage === i
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

<!-- Create Purchase Modal -->
<div id="purchase-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" onclick="closePurchaseModal()">
        </div>

        <!-- Centering trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div
            class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100/50">
            <div class="bg-white p-8 sm:p-10 relative">
                <!-- Close Button -->
                <button type="button" onclick="closePurchaseModal()"
                    class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center">
                    <i class="fas fa-times text-sm"></i>
                </button>

                <!-- Modal Title -->
                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Catat <span
                            class="text-brand-primary">Pembelian Stok Baru</span></h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Tambahkan pengadaan hewan
                        baru langsung ke inventori marketplace</p>
                </div>

                <!-- Form -->
                <form method="POST" action="/lautan-ternak-pantura/purchase/create" enctype="multipart/form-data"
                    onsubmit="return validatePurchaseModalForm(event)" class="space-y-6">

                    <!-- Pilihan Tipe Pembelian -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tipe
                            Pembelian</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label
                                class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-plus-circle text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Hewan Baru</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Registrasi stok
                                            baru</p>
                                    </div>
                                </div>
                                <input type="radio" name="purchase_type" value="new" checked
                                    class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('new')">
                            </label>
                            <label
                                class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-database text-brand-primary text-base"></i>
                                    <div class="text-left">
                                        <p class="text-xs font-black text-gray-900 leading-none">Hewan Terdaftar</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Tambah stok
                                            terdaftar</p>
                                    </div>
                                </div>
                                <input type="radio" name="purchase_type" value="existing"
                                    class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('existing')">
                            </label>
                        </div>
                    </div>

                    <!-- Pilihan Hewan dari Database (Hanya Tampil jika Existing) -->
                    <div id="existing-livestock-field" class="space-y-4 hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Select -->
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih
                                    Hewan dari Data</label>
                                <div class="relative">
                                    <select name="livestock_id" id="modal_livestock_id"
                                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                                        <option value="">-- Pilih Hewan --</option>
                                        <?php if (!empty($livestockList)): ?>
                                            <?php foreach ($livestockList as $live): ?>
                                                <option value="<?php echo $live['id']; ?>">
                                                    <?php echo htmlspecialchars($live['name']); ?>
                                                    [<?php echo htmlspecialchars($live['code']); ?>] - Rp
                                                    <?php echo number_format($live['price'], 0, ',', '.'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>

                            <!-- Qty -->
                            <div class="space-y-1.5">
                                <label
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas
                                    / Jumlah (Ekor)</label>
                                <input type="number" name="qty" value="1" min="1" required
                                    class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan
                                Pembelian (Internal)</label>
                            <textarea name="notes" placeholder="Contoh: Dibeli dari supplier Pak Slamet..."
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[50px]"></textarea>
                        </div>
                    </div>

                    <!-- Input Data Hewan Baru (Grid layout) -->
                    <div id="new-livestock-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama
                                Hewan</label>
                            <input type="text" name="livestock_name" required placeholder="Contoh: Sapi Limosin A3"
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>

                        <!-- Qty -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas
                                / Jumlah (Ekor)</label>
                            <input type="number" name="qty" value="1" min="1" required
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>

                        <!-- Breed -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Ras /
                                Breed</label>
                            <input type="text" name="breed" required placeholder="Contoh: Limosin, Etawa..."
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>

                        <!-- Gender -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis
                                Kelamin</label>
                            <div class="relative">
                                <select name="gender"
                                    class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                                    <option value="">--PILIH--</option>
                                    <option value="male">Jantan</option>
                                    <option value="female">Betina</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Weight -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Berat
                                Hewan (kg)</label>
                            <input type="number" step="0.01" name="weight" required placeholder="Contoh: 350.5"
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>

                        <!-- Age -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Usia
                                Hewan (Bulan)</label>
                            <input type="number" name="age" required placeholder="Contoh: 24"
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>

                        <!-- Purchase Price -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga
                                Beli Pemasok</label>
                            <div class="relative">
                                <span
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                                <input type="text" id="modal_purchase_price" name="purchase_price" required
                                    oninput="formatModalCurrency(this)"
                                    class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                            </div>
                        </div>

                        <!-- Selling Price -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga
                                Jual Marketplace</label>
                            <div class="relative">
                                <span
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                                <input type="text" id="modal_selling_price" name="selling_price" required
                                    oninput="formatModalCurrency(this)"
                                    class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi
                                Hewan untuk Marketplace</label>
                            <textarea name="description" placeholder="Contoh: Nafsu makan baik, sehat..."
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[60px]"></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan
                                Pembelian (Internal)</label>
                            <textarea name="notes" placeholder="Contoh: Dibeli dari supplier Pak Slamet..."
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[60px]"></textarea>
                        </div>

                        <!-- Upload Gambar Hewan -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Gambar
                                Hewan Ternak</label>
                            <div
                                class="relative flex flex-col items-center justify-center border-2 border-dashed border-gray-200 hover:border-brand-primary/40 hover:bg-brand-light/5 rounded-2xl p-6 transition-all duration-300 group cursor-pointer">
                                <input type="file" name="image" accept="image/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this)">
                                <div class="text-center space-y-2 pointer-events-none">
                                    <div
                                        class="w-12 h-12 rounded-full bg-brand-light text-brand-primary flex items-center justify-center mx-auto transition-transform group-hover:scale-110">
                                        <i class="fas fa-image text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-gray-700">Pilih gambar atau drop file di sini
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Format: JPG, PNG,
                                            WEBP (Max 2MB)</p>
                                    </div>
                                </div>
                                <!-- Image Preview Container -->
                                <div id="image-preview-container" class="hidden mt-4 w-full flex justify-center">
                                    <img id="image-preview-img" src="#" alt="Preview"
                                        class="max-h-40 rounded-xl object-contain border border-gray-100 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex gap-4 border-t border-gray-50 pt-5 mt-2">
                        <button type="button" onclick="closePurchaseModal()"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-3.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 bg-brand-primary text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Simpan & Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openPurchaseModal() {
        const modal = document.getElementById('purchase-modal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset type to new on modal open
        const radioNew = document.querySelector('input[name="purchase_type"][value="new"]');
        if (radioNew) {
            radioNew.checked = true;
            togglePurchaseType('new');
        }
    }

    function closePurchaseModal() {
        const modal = document.getElementById('purchase-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function formatModalCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function previewImage(input) {
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview-img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImg.src = "#";
            previewContainer.classList.add('hidden');
        }
    }

    function togglePurchaseType(type) {
        const newFields = document.getElementById('new-livestock-fields');
        const existingField = document.getElementById('existing-livestock-field');

        const newInputs = newFields.querySelectorAll('input, select, textarea');
        const existingInputs = existingField.querySelectorAll('input, select, textarea');

        if (type === 'existing') {
            newFields.classList.add('hidden');
            existingField.classList.remove('hidden');

            newInputs.forEach(input => {
                input.disabled = true;
                if (input.hasAttribute('required')) {
                    input.setAttribute('data-was-required', 'true');
                    input.removeAttribute('required');
                }
            });
            existingInputs.forEach(input => {
                input.disabled = false;
                if (input.getAttribute('data-was-required') === 'true' || input.id === 'modal_livestock_id' || input.name === 'qty') {
                    input.setAttribute('required', 'true');
                }
            });
        } else {
            newFields.classList.remove('hidden');
            existingField.classList.add('hidden');

            newInputs.forEach(input => {
                input.disabled = false;
                if (input.getAttribute('data-was-required') === 'true') {
                    input.setAttribute('required', 'true');
                }
            });
            existingInputs.forEach(input => {
                input.disabled = true;
                input.removeAttribute('required');
            });
        }
    }

    function validatePurchaseModalForm(event) {
        const purchaseType = document.querySelector('input[name="purchase_type"]:checked').value;

        if (purchaseType === 'new') {
            const purchasePriceInput = document.getElementById('modal_purchase_price');
            const sellingPriceInput = document.getElementById('modal_selling_price');

            const purchasePrice = parseFloat(purchasePriceInput.value.replace(/\D/g, ""));
            const sellingPrice = parseFloat(sellingPriceInput.value.replace(/\D/g, ""));

            if (sellingPrice < purchasePrice) {
                showToast('Harga jual tidak boleh lebih kecil dari harga beli!', 'error');
                event.preventDefault();
                return false;
            }

            // Set raw numeric values back to inputs before submission
            purchasePriceInput.value = purchasePrice;
            sellingPriceInput.value = sellingPrice;
        } else {
            const existingSelect = document.getElementById('modal_livestock_id');
            if (existingSelect.value === "") {
                showToast('Silakan pilih hewan terdaftar terlebih dahulu!', 'error');
                event.preventDefault();
                return false;
            }
        }

        return true;
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>