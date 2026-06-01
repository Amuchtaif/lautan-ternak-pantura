<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php
    $topbarTitle = 'Penjualan Hewan';
    $topbarSubtitle = 'Monitor dan verifikasi transaksi penjualan ternak';
    require_once 'views/admin/includes/topbar.php';
    ?>
    <main class="p-8 space-y-8 flex-grow">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kelola <span
                        class="text-brand-primary">Penjualan Hewan</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Daftar transaksi penjualan, uang muka (DP), dan cicilan pelunasan</p>
            </div>
            <button onclick="openCreateSaleModal()"
                class="bg-brand-primary text-white px-6 py-3.5 rounded-2xl shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all text-sm font-black flex items-center gap-2">
                <i class="fas fa-plus"></i> Catat Penjualan Baru
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
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast("<?php echo addslashes(htmlspecialchars($_SESSION['error'])); ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-brand-light text-brand-primary flex items-center justify-center text-2xl">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
                    <h3 class="text-2xl font-black text-gray-900"><?php echo count($salesList); ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Nilai Transaksi</p>
                    <h3 class="text-2xl font-black text-gray-900">Rp <?php 
                        $totalVal = array_sum(array_column($salesList, 'total_price'));
                        echo number_format($totalVal, 0, ',', '.'); 
                    ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pesanan Pending</p>
                    <h3 class="text-2xl font-black text-gray-900"><?php 
                        $pendingCount = count(array_filter($salesList, function($s) { return $s['sale_status'] === 'pending'; }));
                        echo $pendingCount; 
                    ?></h3>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <form method="GET" action="/lautan-ternak-pantura/sales/index" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="relative sm:col-span-2">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Cari invoice, nama pelanggan, hewan, peternak..." 
                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-transparent rounded-xl outline-none focus:border-brand-primary focus:bg-white transition-all text-sm font-bold text-gray-700">
                </div>
                <div>
                    <div class="relative">
                        <select name="payment_status" class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-transparent rounded-xl outline-none focus:border-brand-primary focus:bg-white transition-all text-sm font-bold text-gray-700 appearance-none">
                            <option value="">-- Status Bayar --</option>
                            <option value="unpaid" <?php echo ($payment_status === 'unpaid') ? 'selected' : ''; ?>>Belum Bayar (Unpaid)</option>
                            <option value="partial" <?php echo ($payment_status === 'partial') ? 'selected' : ''; ?>>DP / Sebagian (Partial)</option>
                            <option value="paid" <?php echo ($payment_status === 'paid') ? 'selected' : ''; ?>>Lunas (Paid)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="relative flex-grow">
                        <select name="sale_status" class="w-full pl-4 pr-10 py-3 bg-gray-50 border border-transparent rounded-xl outline-none focus:border-brand-primary focus:bg-white transition-all text-sm font-bold text-gray-700 appearance-none">
                            <option value="">-- Status Pesanan --</option>
                            <option value="pending" <?php echo ($sale_status === 'pending') ? 'selected' : ''; ?>>Menunggu (Pending)</option>
                            <option value="processing" <?php echo ($sale_status === 'processing') ? 'selected' : ''; ?>>Diproses (Processing)</option>
                            <option value="completed" <?php echo ($sale_status === 'completed') ? 'selected' : ''; ?>>Selesai (Completed)</option>
                            <option value="cancelled" <?php echo ($sale_status === 'cancelled') ? 'selected' : ''; ?>>Batal (Cancelled)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                    <button type="submit" class="bg-brand-primary hover:bg-brand-dark text-white px-5 rounded-xl transition-all shadow-md shadow-brand-primary/20 flex items-center justify-center">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="sales-table">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Invoice & Pelanggan</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Hewan & Peternak</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tipe & Status Bayar</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status Pesanan</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Harga</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($salesList)): ?>
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-gray-400 font-bold">
                                    Belum ada data penjualan masuk.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salesList as $i => $sale): ?>
                                <tr class="hover:bg-brand-light/10 transition-all duration-200">
                                    <td class="px-8 py-6">
                                        <span class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-gray-900">
                                            #<?php echo htmlspecialchars($sale['invoice_code']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 font-bold uppercase mt-0.5">
                                            <?php echo htmlspecialchars($sale['customer_name']); ?>
                                        </p>
                                        <p class="text-[9px] text-gray-400 mt-0.5 font-bold">
                                            <i class="fab fa-whatsapp text-emerald-500 mr-1"></i><?php echo htmlspecialchars($sale['customer_phone'] ?: '-'); ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-bold text-gray-700 capitalize">
                                            <?php echo htmlspecialchars($sale['livestock_name']); ?>
                                        </p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">
                                            Peternak: <?php echo htmlspecialchars($sale['peternak_name']); ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1.5 items-start">
                                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded <?php echo ($sale['payment_type'] === 'dp') ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'; ?>">
                                                <?php echo strtoupper($sale['payment_type']); ?>
                                            </span>
                                            <?php
                                            $payClasses = [
                                                'unpaid' => 'bg-red-50 text-red-500',
                                                'partial' => 'bg-amber-50 text-amber-500 border border-amber-200',
                                                'paid' => 'bg-emerald-50 text-emerald-600 font-bold'
                                            ][$sale['payment_status']] ?? 'bg-gray-50 text-gray-400';
                                            $payLabel = [
                                                'unpaid' => 'Belum Bayar',
                                                'partial' => 'DP / Sebagian',
                                                'paid' => 'Lunas'
                                            ][$sale['payment_status']] ?? $sale['payment_status'];
                                            ?>
                                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded <?php echo $payClasses; ?>">
                                                <?php echo $payLabel; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php
                                        $saleClasses = [
                                            'pending' => 'bg-amber-50 text-amber-600',
                                            'processing' => 'bg-blue-50 text-blue-600',
                                            'completed' => 'bg-emerald-50 text-emerald-600',
                                            'cancelled' => 'bg-red-50 text-red-500'
                                        ][$sale['sale_status']] ?? 'bg-gray-50 text-gray-400';
                                        ?>
                                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-full <?php echo $saleClasses; ?>">
                                            <?php echo $sale['sale_status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-brand-primary">
                                            Rp <?php echo number_format($sale['total_price'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="text-[9px] text-gray-400 font-bold mt-0.5">
                                            <?php echo date('d M Y', strtotime($sale['created_at'])); ?>
                                        </p>
                                    </td>
                                    <td class="px-8 py-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/lautan-ternak-pantura/sales/detail/<?php echo $sale['id']; ?>"
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-all"
                                                title="Detail & Ledger Pembayaran">
                                                <i class="fas fa-search-plus text-xs"></i>
                                            </a>
                                            <button onclick="openDeleteModal(<?php echo $sale['id']; ?>, '<?php echo htmlspecialchars($sale['invoice_code']); ?>')"
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-all"
                                                title="Batalkan & Hapus">
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
            <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0">
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
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
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

<!-- Delete Confirmation Modal -->
<div id="delete-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-2xl max-w-sm w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300 text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fas fa-trash-can"></i>
        </div>
        <h3 class="text-lg font-black text-gray-900 mb-2">Batalkan Penjualan?</h3>
        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-6">
            Menghapus transaksi <span id="delete-invoice-code" class="text-gray-900 font-extrabold"></span> akan mengembalikan stok hewan ke inventori. Tindakan ini tidak dapat dibatalkan.
        </p>

        <form action="/lautan-ternak-pantura/sales/delete" method="POST" class="flex gap-3">
            <input type="hidden" id="delete-id" name="id">
            <button type="button" onclick="closeModal('delete')"
                class="flex-1 px-5 py-3.5 border border-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Batal</button>
            <button type="submit"
                class="flex-1 px-5 py-3.5 bg-red-500 text-white rounded-xl text-sm font-black hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
    function openDeleteModal(id, invoiceCode) {
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-invoice-code').innerText = invoiceCode;
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
        tableRows = Array.from(document.querySelectorAll('#sales-table tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
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

<!-- CREATE SALE MODAL POPUP -->
<div id="createSaleModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto no-scrollbar shadow-2xl border border-gray-100 flex flex-col transform scale-95 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Catat <span class="text-brand-primary">Transaksi Penjualan</span></h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Registrasi penjualan ternak langsung secara manual oleh Admin</p>
            </div>
            <button onclick="closeCreateSaleModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 flex items-center justify-center transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form method="POST" action="/lautan-ternak-pantura/sales/create" enctype="multipart/form-data" onsubmit="return validateModalPricingForm(event)" class="p-8 space-y-6">
            
            <!-- Customer Type Selection -->
            <div class="bg-brand-light/20 p-6 rounded-2xl border border-brand-primary/10 space-y-4">
                <h3 class="text-xs font-black text-brand-primary uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-user"></i> Informasi Pelanggan
                </h3>

                <!-- Selection between New or Registered Customer -->
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex items-center justify-between p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 leading-none">Pelanggan Baru</p>
                            <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Belum Terdaftar</p>
                        </div>
                        <input type="radio" name="customer_type" value="new" checked
                            class="accent-brand-primary h-4 w-4" onchange="toggleCustomerType('new')">
                    </label>
                    <label class="relative flex items-center justify-between p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 leading-none">Pelanggan Terdaftar</p>
                            <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Sudah Memiliki Akun</p>
                        </div>
                        <input type="radio" name="customer_type" value="registered"
                            class="accent-brand-primary h-4 w-4" onchange="toggleCustomerType('registered')">
                    </label>
                </div>

                <!-- Dropdown for Registered Customer (Hidden by Default) -->
                <div class="space-y-1.5 hidden" id="registered_customer_container">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Pelanggan Terdaftar</label>
                    <div class="relative">
                        <select id="modal_registered_customer_id" class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all font-bold text-xs appearance-none" onchange="handleCustomerSelectChange()">
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($customerList as $cust): ?>
                                <option value="<?php echo $cust['id']; ?>" data-name="<?php echo htmlspecialchars($cust['full_name']); ?>" data-phone="<?php echo htmlspecialchars($cust['phone']); ?>">
                                    <?php echo htmlspecialchars($cust['full_name']); ?> (<?php echo htmlspecialchars($cust['phone'] ?: 'No WA Kosong'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- Name and Phone Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Pelanggan</label>
                        <input type="text" id="modal_customer_name" name="customer_name" required placeholder="Nama lengkap pelanggan..." 
                            class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold text-gray-700">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">No. WhatsApp / HP</label>
                        <input type="text" id="modal_customer_phone" name="customer_phone" required placeholder="Contoh: 08123456789..." 
                            class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Order Details Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Select Livestock -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Hewan Ternak</label>
                    <div class="relative">
                        <select name="livestock_id" id="modal_livestock_id" required onchange="handleModalLivestockChange()"
                            class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                            <option value="">-- Pilih Hewan --</option>
                            <?php if (!empty($livestockList)): ?>
                                <?php foreach ($livestockList as $live): ?>
                                    <option value="<?php echo $live['id']; ?>" data-price="<?php echo $live['selling_price']; ?>" data-stock="<?php echo $live['stock']; ?>">
                                        <?php echo htmlspecialchars($live['breed']); ?> - Rp <?php echo number_format($live['selling_price'], 0, ',', '.'); ?> (Stok: <?php echo $live['stock']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas / Jumlah (Ekor)</label>
                    <input type="number" name="qty" id="modal_qty" value="1" min="1" required oninput="modalCalculateTotal()"
                        class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                </div>
            </div>

            <!-- Payment Section -->
            <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100 space-y-5">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-credit-card text-brand-primary"></i> Spesifikasi Pembayaran
                </h3>

                <!-- Row 1: Payment Type (Jenis Pembayaran) -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Pembayaran</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-between p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Uang Muka (DP)</p>
                                <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Pembayaran Bertahap</p>
                            </div>
                            <input type="radio" name="payment_type" value="dp" checked
                                class="accent-brand-primary h-4 w-4" onchange="toggleModalPaymentType('dp')">
                        </label>
                        <label class="relative flex items-center justify-between p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Lunas / Full</p>
                                <p class="text-[8px] text-gray-400 font-bold uppercase mt-1">Bayar Sekaligus</p>
                            </div>
                            <input type="radio" name="payment_type" value="lunas"
                                class="accent-brand-primary h-4 w-4" onchange="toggleModalPaymentType('lunas')">
                        </label>
                    </div>
                </div>

                <!-- Row 2: Amount & Method (Symmetric Side-by-Side) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Amount Input -->
                    <div class="space-y-1.5 transition-all duration-300" id="modal_amount_input_container">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Uang Muka (DP) (Rp)</label>
                        <input type="text" id="modal_payment_amount" name="payment_amount" required onkeyup="formatCurrency(this); modalCalculateTotal()" placeholder="Contoh: 1.000.000..." 
                            class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all text-xs font-bold text-gray-700">
                    </div>

                    <!-- Payment Method Select -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Metode Pembayaran</label>
                        <div class="relative">
                            <select name="payment_method" id="modal_payment_method" required
                                class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all font-bold text-xs appearance-none">
                                <option value="Transfer Bank Manual">Transfer Bank Manual</option>
                                <option value="Tunai / Cash">Tunai / Cash</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Destination Cash Account Selector -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Rekening Kas Penerima Pembayaran</label>
                    <div class="relative">
                        <select name="cash_account_id" required
                            class="w-full px-5 py-3.5 bg-white border border-gray-100 rounded-xl outline-none focus:border-brand-primary transition-all font-bold text-xs appearance-none cursor-pointer">
                            <option value="">-- Pilih Rekening Kas Penerima --</option>
                            <?php foreach ($accountsList as $acc): ?>
                                <option value="<?php echo $acc['id']; ?>">
                                    <?php echo htmlspecialchars($acc['name']); ?> (Saldo: Rp <?php echo number_format($acc['current_balance'], 0, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- Row 3: Optional Payment Proof File Input -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Upload Bukti Pembayaran (Opsional)</label>
                    <div class="relative flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 hover:border-brand-primary/50 bg-white rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-lg mb-1.5"></i>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Format: JPG, PNG, WEBP (Max 2MB)</p>
                            </div>
                            <input type="file" name="payment_proof" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Row 4: Dynamic Calculations Summary -->
                <div class="bg-white p-4 rounded-xl border border-gray-100 space-y-2.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-gray-400">Harga Satuan:</span>
                        <span class="font-black text-gray-900" id="modal_label_price">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-gray-400">Total Transaksi:</span>
                        <span class="font-black text-brand-primary" id="modal_label_total">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-xs hidden" id="modal_remaining_container">
                        <span class="font-bold text-gray-400">Sisa Tagihan Pelunasan:</span>
                        <span class="font-black text-orange-500" id="modal_label_remaining">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-1.5">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Penjualan (Opsional)</label>
                <textarea name="notes" placeholder="Catatan opsional mengenai pengiriman atau request khusus..."
                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white transition-all font-bold text-xs min-h-[80px]"></textarea>
            </div>

            <!-- Footer Buttons -->
            <div class="flex gap-4 border-t border-gray-50 pt-6">
                <button type="button" onclick="closeCreateSaleModal()"
                    class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-4 rounded-xl font-black text-xs uppercase tracking-widest transition-all text-center flex items-center justify-center">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 bg-brand-primary text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Catat Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let modalActivePrice = 0;
    let modalActiveStock = 0;

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (value !== "") {
            input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
        } else {
            input.value = "";
        }
    }

    function formatNumber(num) {
        if (!num) return "Rp 0";
        return "Rp " + new Intl.NumberFormat("id-ID").format(Math.round(num));
    }

    function openCreateSaleModal() {
        // Reset Customer Type to New Customer (default)
        const radioNew = document.querySelector('input[name="customer_type"][value="new"]');
        if (radioNew) {
            radioNew.checked = true;
            toggleCustomerType('new');
        }
        
        // Reset Registered Customer Select
        const custSelect = document.getElementById('modal_registered_customer_id');
        if (custSelect) {
            custSelect.selectedIndex = 0;
        }

        // Reset inputs
        const nameInput = document.getElementById('modal_customer_name');
        if (nameInput) {
            nameInput.value = "";
            nameInput.readOnly = false;
            nameInput.classList.remove('bg-gray-50', 'text-gray-500');
        }
        const phoneInput = document.getElementById('modal_customer_phone');
        if (phoneInput) {
            phoneInput.value = "";
            phoneInput.readOnly = false;
            phoneInput.classList.remove('bg-gray-50', 'text-gray-500');
        }

        // Reset Livestock Select
        const liveSelect = document.getElementById('modal_livestock_id');
        if (liveSelect) {
            liveSelect.selectedIndex = 0;
        }
        modalActivePrice = 0;
        modalActiveStock = 0;
        document.getElementById('modal_label_price').innerText = "Rp 0";

        // Reset Quantity
        const qtyInput = document.getElementById('modal_qty');
        if (qtyInput) {
            qtyInput.value = 1;
        }

        // Reset Payment Type to DP (default)
        const radioDP = document.querySelector('input[name="payment_type"][value="dp"]');
        if (radioDP) {
            radioDP.checked = true;
            toggleModalPaymentType('dp');
        }
        
        // Reset Payment Amount
        const payAmtInput = document.getElementById('modal_payment_amount');
        if (payAmtInput) {
            payAmtInput.value = "";
        }

        // Recalculate
        modalCalculateTotal();

        const modal = document.getElementById('createSaleModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeCreateSaleModal() {
        const modal = document.getElementById('createSaleModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        document.body.classList.remove('overflow-hidden');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function toggleCustomerType(type) {
        const registeredContainer = document.getElementById('registered_customer_container');
        const nameInput = document.getElementById('modal_customer_name');
        const phoneInput = document.getElementById('modal_customer_phone');
        
        if (type === 'registered') {
            registeredContainer.classList.remove('hidden');
            nameInput.readOnly = true;
            phoneInput.readOnly = true;
            nameInput.classList.add('bg-gray-50', 'text-gray-500');
            phoneInput.classList.add('bg-gray-50', 'text-gray-500');
            handleCustomerSelectChange();
        } else {
            registeredContainer.classList.add('hidden');
            nameInput.readOnly = false;
            phoneInput.readOnly = false;
            nameInput.classList.remove('bg-gray-50', 'text-gray-500');
            phoneInput.classList.remove('bg-gray-50', 'text-gray-500');
            nameInput.value = "";
            phoneInput.value = "";
        }
    }

    function handleCustomerSelectChange() {
        const select = document.getElementById('modal_registered_customer_id');
        const selectedOption = select.options[select.selectedIndex];
        const nameInput = document.getElementById('modal_customer_name');
        const phoneInput = document.getElementById('modal_customer_phone');
        
        if (selectedOption && select.value !== "") {
            nameInput.value = selectedOption.getAttribute('data-name') || "";
            phoneInput.value = selectedOption.getAttribute('data-phone') || "";
        } else {
            nameInput.value = "";
            phoneInput.value = "";
        }
    }

    function handleModalLivestockChange() {
        const select = document.getElementById('modal_livestock_id');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && select.value !== "") {
            modalActivePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            modalActiveStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
            
            document.getElementById('modal_label_price').innerText = formatNumber(modalActivePrice);
            
            const qtyInput = document.getElementById('modal_qty');
            qtyInput.max = modalActiveStock;
        } else {
            modalActivePrice = 0;
            modalActiveStock = 0;
            document.getElementById('modal_label_price').innerText = "Rp 0";
        }
        
        modalCalculateTotal();
    }

    function modalCalculateTotal() {
        const qty = parseInt(document.getElementById('modal_qty').value) || 1;
        const total = modalActivePrice * qty;
        
        document.getElementById('modal_label_total').innerText = formatNumber(total);
        
        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        const payAmtInput = document.getElementById('modal_payment_amount');
        const remainingContainer = document.getElementById('modal_remaining_container');
        const remainingLabel = document.getElementById('modal_label_remaining');
        
        if (payType === 'lunas') {
            const cleanVal = Math.round(total).toString();
            payAmtInput.value = new Intl.NumberFormat("id-ID").format(cleanVal);
            remainingContainer.classList.add('hidden');
        } else {
            // DP payment type
            const rawAmt = parseFloat(payAmtInput.value.replace(/\D/g, "")) || 0;
            const remaining = Math.max(0, total - rawAmt);
            remainingLabel.innerText = formatNumber(remaining);
            remainingContainer.classList.remove('hidden');
        }
    }

    function toggleModalPaymentType(type) {
        const container = document.getElementById('modal_amount_input_container');
        const label = container.querySelector('label');
        const payAmtInput = document.getElementById('modal_payment_amount');
        
        if (type === 'lunas') {
            container.classList.add('opacity-60');
            payAmtInput.readOnly = true;
            label.innerText = "Jumlah Pembayaran Lunas (Otomatis) (Rp)";
        } else {
            container.classList.remove('opacity-60');
            payAmtInput.readOnly = false;
            payAmtInput.value = "";
            label.innerText = "Jumlah Uang Muka (DP) (Rp)";
        }
        
        modalCalculateTotal();
    }

    function validateModalPricingForm(event) {
        // Validate Customer Type Select
        const customerType = document.querySelector('input[name="customer_type"]:checked').value;
        if (customerType === 'registered') {
            const customerSelect = document.getElementById('modal_registered_customer_id');
            if (customerSelect.value === "") {
                showToast('Silakan pilih pelanggan terdaftar terlebih dahulu!', 'error');
                event.preventDefault();
                return false;
            }
        }

        const select = document.getElementById('modal_livestock_id');
        if (select.value === "") {
            showToast('Silakan pilih hewan terlebih dahulu!', 'error');
            event.preventDefault();
            return false;
        }

        const qty = parseInt(document.getElementById('modal_qty').value) || 0;
        if (qty > modalActiveStock) {
            showToast('Jumlah melebihi stok yang tersedia!', 'error');
            event.preventDefault();
            return false;
        }

        const payType = document.querySelector('input[name="payment_type"]:checked').value;
        const total = modalActivePrice * qty;
        
        const payAmtInput = document.getElementById('modal_payment_amount');
        const rawAmt = parseFloat(payAmtInput.value.replace(/\D/g, "")) || 0;

        if (payType === 'dp') {
            if (rawAmt <= 0) {
                showToast('Pembayaran DP harus diisi dan lebih dari 0!', 'error');
                event.preventDefault();
                return false;
            }
            if (rawAmt >= total) {
                showToast('Pembayaran DP tidak boleh melebihi atau sama dengan total harga. Gunakan tipe Lunas.', 'error');
                event.preventDefault();
                return false;
            }
        } else {
            if (rawAmt < total) {
                showToast('Nominal pelunasan tidak mencukupi total harga transaksi!', 'error');
                event.preventDefault();
                return false;
            }
        }

        // Set raw numeric values back to inputs before submission
        payAmtInput.value = rawAmt;
        return true;
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
