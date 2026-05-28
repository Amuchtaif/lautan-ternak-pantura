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
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-12">
                                No</th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Detail Hewan</th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Pemasok & Tanggal</th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Harga Satuan</th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-48">
                                Status Pembayaran</th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                Catatan</th>
                            <th class="px-4 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-32">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($purchasesList)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 font-bold">
                                    Belum ada catatan pembelian stok masuk.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchasesList as $i => $purch): ?>
                                <tr class="hover:bg-brand-light/10 transition-all duration-200">
                                    <td class="px-4 py-6">
                                        <span class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></span>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-black text-gray-900 capitalize">
                                                <?php echo htmlspecialchars($purch['livestock_name']); ?>
                                            </p>
                                            <?php if (($purch['payment_type'] ?? 'lunas') === 'dp'): ?>
                                                <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-amber-50 text-amber-600 border border-amber-100">DP</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-emerald-50 text-emerald-600 border border-emerald-100">Lunas</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                            Jenis: <span class="text-gray-600 capitalize"><?php echo htmlspecialchars($purch['breed']); ?></span>
                                        </p>
                                        <div class="space-y-1 mt-1.5 border-t border-gray-50 pt-1.5">
                                            <p class="text-[11px] font-semibold text-gray-500 flex items-center gap-1.5">
                                                <i class="fas fa-weight-hanging text-brand-primary text-[10px] w-3.5"></i>
                                                Berat: <span class="text-gray-800 font-bold"><?php echo number_format($purch['weight'], 2, ',', '.'); ?> kg</span>
                                            </p>
                                            <p class="text-[11px] font-semibold text-gray-500 flex items-center gap-1.5">
                                                <i class="fas fa-calculator text-gray-400 text-[10px] w-3.5"></i>
                                                Jumlah: <span class="text-gray-800 font-bold"><?php echo $purch['qty']; ?> ekor</span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div>
                                            <p class="text-xs font-black text-gray-700 capitalize flex items-center gap-1.5">
                                                <i class="fas fa-user-tie text-gray-400"></i><?php echo htmlspecialchars($purch['peternak_name'] ?? '-'); ?>
                                            </p>
                                            <div class="flex items-center gap-1.5 text-[10px] text-gray-400 font-bold mt-1">
                                                <span>Oleh: <?php echo htmlspecialchars($purch['admin_name']); ?></span>
                                                <span class="text-gray-300">|</span>
                                                <span><?php echo date('d M Y', strtotime($purch['purchased_at'])); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-red-50 text-red-500 w-11 text-center shrink-0">Beli</span>
                                                <span class="text-xs font-bold text-gray-700">Rp <?php echo number_format($purch['purchase_price'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-emerald-50 text-emerald-600 w-11 text-center shrink-0">Jual</span>
                                                <span class="text-xs font-bold text-brand-primary">Rp <?php echo number_format($purch['selling_price'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <?php 
                                        $total = floatval($purch['total_purchase']);
                                        $paid = floatval($purch['amount_paid'] ?? 0);
                                        $remaining = max(0, $total - $paid);
                                        ?>
                                        <div class="bg-gray-50/80 rounded-xl p-2.5 border border-gray-100/50 space-y-1 w-44 shadow-sm">
                                            <div class="flex justify-between items-center text-[9px] text-gray-400 font-bold uppercase tracking-wider">
                                                <span>Total:</span>
                                                <span class="text-gray-700 font-black">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center text-[9px] text-emerald-600 font-bold uppercase tracking-wider">
                                                <span>Bayar:</span>
                                                <span class="font-black">Rp <?php echo number_format($paid, 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="border-t border-dashed border-gray-200 my-1"></div>
                                            <div class="flex justify-between items-center">
                                                <?php if ($remaining > 0): ?>
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 px-1 py-0.5 rounded border border-amber-200/30">Kurang</span>
                                                    <span class="text-[10px] font-black text-amber-600">Rp <?php echo number_format($remaining, 0, ',', '.'); ?></span>
                                                <?php else: ?>
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-1 py-0.5 rounded border border-emerald-200/30 flex items-center gap-1">
                                                        <i class="fas fa-check text-[7px]"></i> Lunas
                                                    </span>
                                                    <span class="text-[10px] font-black text-emerald-600">Rp 0</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6">
                                        <p class="text-xs text-gray-500 font-medium max-w-[120px] truncate"
                                            title="<?php echo htmlspecialchars($purch['notes']); ?>">
                                            <?php if ($purch['notes']): ?>
                                                <i class="far fa-sticky-note text-gray-400 mr-1"></i><?php echo htmlspecialchars($purch['notes']); ?>
                                            <?php else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                    <td class="px-4 py-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <?php if ($remaining > 0): ?>
                                                <button
                                                    onclick='openPayoffModal(<?php echo htmlspecialchars(json_encode($purch), ENT_QUOTES, "UTF-8"); ?>)'
                                                    class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-all duration-200 hover:shadow-lg hover:shadow-emerald-500/20 active:scale-95"
                                                    title="Bayar / Lunasi DP">
                                                    <i class="fas fa-wallet text-xs"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button
                                                onclick='openLedgerModal(<?php echo htmlspecialchars(json_encode($purch), ENT_QUOTES, "UTF-8"); ?>)'
                                                class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-all duration-200 hover:shadow-lg hover:shadow-amber-500/20 active:scale-95"
                                                title="Riwayat Ledger / Pembayaran">
                                                <i class="fas fa-history text-xs"></i>
                                            </button>
                                            <button
                                                onclick='openEditModal(<?php echo htmlspecialchars(json_encode($purch), ENT_QUOTES, "UTF-8"); ?>)'
                                                class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/20 active:scale-95"
                                                title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $purch['id']; ?>)"
                                                class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all duration-200 hover:shadow-lg hover:shadow-red-500/20 active:scale-95"
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
        <form action="/lautan-ternak-pantura/purchase/edit" method="POST" onsubmit="return validateEditPricingForm(event)" class="space-y-4">
            <input type="hidden" id="edit-id" name="id">

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nama Hewan</label>
                <input type="text" id="edit-name" name="livestock_name" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Kategori Hewan</label>
                <div class="relative">
                    <select id="edit-breed" name="breed" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all appearance-none cursor-pointer">
                        <option value="kambing">Kambing</option>
                        <option value="sapi">Sapi</option>
                        <option value="domba">Domba</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                </div>
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
                    <input type="text" id="edit-purchase-price" name="purchase_price" required oninput="formatCurrency(this)"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Harga Jual
                        (Rp)</label>
                    <input type="text" id="edit-selling-price" name="selling_price" required oninput="formatCurrency(this)"
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

<!-- Pay Off Purchase Modal -->
<div id="payoff-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div
        class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-wallet text-brand-primary"></i> Catat Pembayaran / Pelunasan
            </h3>
            <button onclick="closePayoffModal()"
                class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="/lautan-ternak-pantura/purchase/recordPayment" method="POST" onsubmit="return validatePayoffForm(event)" class="space-y-5">
            <input type="hidden" id="payoff-purchase-id" name="purchase_id">

            <div class="bg-brand-light/10 p-5 rounded-2xl border border-brand-primary/5 space-y-2">
                <div class="flex justify-between text-xs font-bold text-gray-500">
                    <span>Total Pembelian:</span>
                    <span class="text-gray-800 font-black" id="payoff-label-total">Rp 0</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-500">
                    <span>Sudah Dibayar:</span>
                    <span class="text-emerald-600 font-black" id="payoff-label-paid">Rp 0</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-500 border-t border-dashed border-gray-100 pt-2">
                    <span>Sisa Kekurangan:</span>
                    <span class="text-orange-500 font-black" id="payoff-label-remaining">Rp 0</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Pembayaran / Pelunasan (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                    <input type="text" id="payoff-amount" name="payment_amount" required oninput="formatCurrency(this)" placeholder="Masukkan nominal pembayaran..."
                        class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-black text-gray-700 transition-all">
                </div>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1 px-1">Defauled ke sisa tagihan untuk pelunasan cepat</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Tanggal Pembayaran</label>
                <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl outline-none focus:border-brand-primary focus:bg-white text-sm font-bold text-gray-700 transition-all animate-all">
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1 px-1">Tanggal pencatatan transaksi ledger pembayaran</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closePayoffModal()"
                    class="px-6 py-3 border border-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Batal</button>
                <button type="submit"
                    class="px-6 py-3 bg-brand-primary text-white rounded-xl text-sm font-black hover:bg-brand-dark transition-all shadow-lg shadow-brand-primary/20">Catat Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Purchase Ledger History Modal -->
<div id="ledger-modal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div
        class="bg-white rounded-2xl max-w-2xl w-full p-8 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <i class="fas fa-history text-brand-primary"></i> Riwayat Ledger Pembayaran
            </h3>
            <button onclick="closeLedgerModal()"
                class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 font-bold uppercase tracking-wider block">Kode Pembelian:</span>
                    <span id="ledger-info-code" class="font-black text-gray-700">-</span>
                </div>
                <div>
                    <span class="text-gray-400 font-bold uppercase tracking-wider block">Mitra Peternak:</span>
                    <span id="ledger-info-breeder" class="font-black text-gray-700">-</span>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">No</th>
                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kode Bayar</th>
                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nominal</th>
                            <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-payments-body" class="divide-y divide-gray-50 text-xs">
                        <!-- Dynamic payment rows will be inserted here -->
                    </tbody>
                </table>
            </div>

            <div class="bg-brand-light/10 p-4 rounded-xl border border-brand-primary/5 flex justify-between items-center text-xs">
                <span class="text-gray-500 font-bold">Total Terbayar:</span>
                <span id="ledger-total-paid" class="font-black text-brand-primary text-sm">Rp 0</span>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-gray-50 mt-6">
            <button type="button" onclick="closeLedgerModal()"
                class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">Tutup</button>
        </div>
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

    function formatNumber(num) {
        if (num === null || num === undefined || num === "") return "";
        let val = Math.round(parseFloat(num)).toString();
        return new Intl.NumberFormat("id-ID").format(val);
    }

    function validateEditPricingForm(event) {
        const purchasePriceInput = document.getElementById('edit-purchase-price');
        const sellingPriceInput = document.getElementById('edit-selling-price');

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
        return true;
    }

    // Modal Animation Trigger
    function openEditModal(data) {
        document.getElementById('edit-id').value = data.id;
        
        let breedVal = data.breed || '';
        let category = '';
        let nameSuffix = breedVal;

        const lowerBreed = breedVal.toLowerCase();
        if (lowerBreed.startsWith('sapi ')) {
            category = 'sapi';
            nameSuffix = breedVal.substring(5);
        } else if (lowerBreed.startsWith('kambing ')) {
            category = 'kambing';
            nameSuffix = breedVal.substring(8);
        } else if (lowerBreed.startsWith('domba ')) {
            category = 'domba';
            nameSuffix = breedVal.substring(6);
        } else if (lowerBreed === 'sapi' || lowerBreed === 'kambing' || lowerBreed === 'domba') {
            category = lowerBreed;
            nameSuffix = '';
        }

        document.getElementById('edit-name').value = nameSuffix || breedVal;
        
        const breedSelect = document.getElementById('edit-breed');
        if (breedSelect) {
            if (category) {
                breedSelect.value = category;
            } else {
                const exists = Array.from(breedSelect.options).some(option => option.value === data.breed);
                if (!exists && data.breed) {
                    const opt = document.createElement('option');
                    opt.value = data.breed;
                    opt.text = data.breed.charAt(0).toUpperCase() + data.breed.slice(1);
                    breedSelect.add(opt);
                }
                breedSelect.value = data.breed;
            }
        }

        document.getElementById('edit-weight').value = data.weight;
        document.getElementById('edit-qty').value = data.qty;
        document.getElementById('edit-purchase-price').value = formatNumber(data.purchase_price);
        document.getElementById('edit-selling-price').value = formatNumber(data.selling_price);
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

    let payoffActiveRemaining = 0;

    function openPayoffModal(data) {
        document.getElementById('payoff-purchase-id').value = data.id;
        
        const total = parseFloat(data.total_purchase) || 0;
        const paid = parseFloat(data.amount_paid) || 0;
        payoffActiveRemaining = Math.max(0, total - paid);

        document.getElementById('payoff-label-total').innerText = formatNumber(total);
        document.getElementById('payoff-label-paid').innerText = formatNumber(paid);
        document.getElementById('payoff-label-remaining').innerText = formatNumber(payoffActiveRemaining);

        const amountInput = document.getElementById('payoff-amount');
        amountInput.value = formatNumber(payoffActiveRemaining).replace('Rp ', '');

        const modal = document.getElementById('payoff-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closePayoffModal() {
        const modal = document.getElementById('payoff-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function validatePayoffForm(event) {
        const amountInput = document.getElementById('payoff-amount');
        const amount = parseFloat(amountInput.value.replace(/\D/g, "")) || 0;

        if (amount <= 0) {
            showToast('Jumlah pembayaran harus lebih besar dari 0!', 'error');
            event.preventDefault();
            return false;
        }
        if (amount > payoffActiveRemaining) {
            showToast('Jumlah pembayaran melebihi sisa kekurangan Rp ' + formatNumber(payoffActiveRemaining).replace('Rp ', '') + '!', 'error');
            event.preventDefault();
            return false;
        }

        // Set raw numeric value before submission
        amountInput.value = amount;
        return true;
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
<div id="purchase-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto no-scrollbar shadow-2xl border border-gray-100 flex flex-col transform scale-95 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Catat <span class="text-brand-primary">Pembelian Stok Baru</span></h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Tambahkan pengadaan hewan baru langsung ke inventori marketplace</p>
            </div>
            <button type="button" onclick="closePurchaseModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 flex items-center justify-center transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form method="POST" action="/lautan-ternak-pantura/purchase/create" enctype="multipart/form-data"
            onsubmit="return validatePurchaseModalForm(event)" class="p-8 space-y-6">

            <!-- Pilihan Tipe Pembelian -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Tipe Pembelian</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-plus-circle text-brand-primary text-base"></i>
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Hewan Baru</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Registrasi stok baru</p>
                            </div>
                        </div>
                        <input type="radio" name="purchase_type" value="new" checked
                            class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('new')">
                    </label>
                    <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-database text-brand-primary text-base"></i>
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Hewan Terdaftar</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Tambah stok terdaftar</p>
                            </div>
                        </div>
                        <input type="radio" name="purchase_type" value="existing"
                            class="accent-brand-primary h-4 w-4" onchange="togglePurchaseType('existing')">
                    </label>
                </div>
            </div>

            <!-- Pilihan Metode Pembayaran Pemasok -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Metode Pembayaran Pemasok</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-hand-holding-usd text-brand-primary text-base"></i>
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Uang Muka (DP)</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Pembayaran Bertahap</p>
                            </div>
                        </div>
                        <input type="radio" name="payment_type" value="dp" required
                            class="accent-brand-primary h-4 w-4" onchange="toggleModalPaymentType('dp')">
                    </label>
                    <label class="relative flex items-center justify-between p-4 bg-gray-50 border-2 border-transparent rounded-2xl cursor-pointer hover:bg-gray-100 transition-all select-none has-[:checked]:border-brand-primary has-[:checked]:bg-brand-light/10">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-money-bill-wave text-brand-primary text-base"></i>
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-900 leading-none">Lunas / Full</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Bayar Sekaligus</p>
                            </div>
                        </div>
                        <input type="radio" name="payment_type" value="lunas" required
                            class="accent-brand-primary h-4 w-4" onchange="toggleModalPaymentType('lunas')">
                    </label>
                </div>
            </div>

            <!-- Input Jumlah Uang Muka DP (Hanya Tampil jika DP dipilih) -->
            <div id="modal_amount_paid_container" class="space-y-1.5 hidden transition-all duration-300">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Uang Muka (DP) (Rp)</label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="text" id="modal_amount_paid" name="amount_paid" oninput="formatModalCurrency(this)" placeholder="Contoh: 5.000.000..."
                        class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                </div>
            </div>

            <!-- Pilihan Hewan dari Database (Hanya Tampil jika Existing) -->
            <div id="existing-livestock-field" class="space-y-4 hidden">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Select -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Pilih Hewan dari Data</label>
                        <div class="relative">
                            <select name="livestock_id" id="modal_livestock_id"
                                class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                                <option value="">-- Pilih Hewan --</option>
                                <?php if (!empty($livestockList)): ?>
                                    <?php foreach ($livestockList as $live): ?>
                                        <option value="<?php echo $live['id']; ?>" data-price="<?php echo $live['price']; ?>">
                                            <?php echo htmlspecialchars($live['name']); ?> - Rp
                                            <?php echo number_format($live['price'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Qty -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas / Jumlah (Ekor)</label>
                        <input type="number" name="qty" value="1" min="1" required
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Pembelian (Internal)</label>
                    <textarea name="notes" placeholder="masukkan catatan"
                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[50px]"></textarea>
                </div>
            </div>

            <!-- Input Data Hewan Baru (Grid layout) -->
            <div id="new-livestock-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Name -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Hewan</label>
                    <input type="text" name="livestock_name" required placeholder="Contoh: Sapi Limosin A3"
                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                </div>

                <!-- Peternak Name -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Mitra Peternak / Supplier</label>
                    <input type="text" name="peternak_name" required placeholder="Contoh: Ahmad Peternak, Budi Supplier..."
                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                </div>

                <!-- Breed (replaced with category select) -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kategori Hewan</label>
                    <div class="relative">
                        <select name="breed" required
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none cursor-pointer">
                            <option value="">-- PILIH KATEGORI --</option>
                            <option value="kambing">Kambing</option>
                            <option value="sapi">Sapi</option>
                            <option value="domba">Domba</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- Gender -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Kelamin</label>
                    <div class="relative">
                        <select name="gender" required
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs appearance-none">
                            <option value="">--PILIH--</option>
                            <option value="male">Jantan</option>
                            <option value="female">Betina</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- Weight, Qty & Usia (Age) in one row -->
                <div class="grid grid-cols-3 gap-5 sm:col-span-2">
                    <!-- Weight -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Berat Hewan (kg)</label>
                        <input type="number" step="0.01" name="weight" required placeholder="Contoh: 350.5"
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                    </div>

                    <!-- Qty -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kuantitas (Ekor)</label>
                        <input type="number" name="qty" value="1" min="1" required
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                    </div>

                    <!-- Age -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Usia (Bulan)</label>
                        <input type="number" name="age" required placeholder="Contoh: 24"
                            class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                    </div>
                </div>

                <!-- Harga Beli & Jual in one row -->
                <div class="grid grid-cols-2 gap-5 sm:col-span-2">
                    <!-- Purchase Price -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Beli Pemasok</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                            <input type="text" id="modal_purchase_price" name="purchase_price" required
                                oninput="formatModalCurrency(this)"
                                class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>
                    </div>

                    <!-- Selling Price -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Jual Marketplace</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                            <input type="text" id="modal_selling_price" name="selling_price" required
                                oninput="formatModalCurrency(this)"
                                class="w-full pl-12 pr-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs">
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi Hewan untuk Marketplace</label>
                    <textarea name="description" placeholder="masukkan deskripsi"
                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[60px]"></textarea>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Catatan Pembelian (Internal)</label>
                    <textarea name="notes" placeholder="masukkan catatan"
                        class="w-full px-5 py-3.5 bg-gray-50 border-2 border-transparent rounded-xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-xs min-h-[60px]"></textarea>
                </div>

                <!-- Upload Gambar Hewan -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Gambar Hewan Ternak</label>
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
                                <p class="text-xs font-black text-gray-700">Pilih gambar atau drop file di sini</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Format: JPG, PNG, WEBP (Max 2MB)</p>
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

<script>
    function openPurchaseModal() {
        const modal = document.getElementById('purchase-modal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Reset type to new on modal open
        const radioNew = document.querySelector('input[name="purchase_type"][value="new"]');
        if (radioNew) {
            radioNew.checked = true;
            togglePurchaseType('new');
        }

        // Reset payment type selection
        document.querySelectorAll('input[name="payment_type"]').forEach(input => input.checked = false);
        
        const amountContainer = document.getElementById('modal_amount_paid_container');
        if (amountContainer) {
            amountContainer.classList.add('hidden');
        }
        const amountInput = document.getElementById('modal_amount_paid');
        if (amountInput) {
            amountInput.value = '';
            amountInput.removeAttribute('required');
        }

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function toggleModalPaymentType(type) {
        const amountContainer = document.getElementById('modal_amount_paid_container');
        const amountInput = document.getElementById('modal_amount_paid');
        
        if (type === 'dp') {
            amountContainer.classList.remove('hidden');
            amountInput.setAttribute('required', 'true');
        } else {
            amountContainer.classList.add('hidden');
            amountInput.removeAttribute('required');
            amountInput.value = '';
        }
    }

    function closePurchaseModal() {
        const modal = document.getElementById('purchase-modal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        document.body.classList.remove('overflow-hidden');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
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

        const paymentTypeChecked = document.querySelector('input[name="payment_type"]:checked');
        if (!paymentTypeChecked) {
            showToast('Silakan pilih metode pembayaran pemasok terlebih dahulu!', 'error');
            event.preventDefault();
            return false;
        }

        let purchasePrice = 0;
        let qty = 1;

        if (purchaseType === 'new') {
            const purchasePriceInput = document.getElementById('modal_purchase_price');
            const sellingPriceInput = document.getElementById('modal_selling_price');

            purchasePrice = parseFloat(purchasePriceInput.value.replace(/\D/g, "")) || 0;
            const sellingPrice = parseFloat(sellingPriceInput.value.replace(/\D/g, "")) || 0;

            if (sellingPrice < purchasePrice) {
                showToast('Harga jual tidak boleh lebih kecil dari harga beli!', 'error');
                event.preventDefault();
                return false;
            }

            // Set raw numeric values back to inputs before submission
            purchasePriceInput.value = purchasePrice;
            sellingPriceInput.value = sellingPrice;

            const newQtyInput = document.querySelector('#new-livestock-fields input[name="qty"]');
            qty = parseInt(newQtyInput.value) || 1;
        } else {
            const existingSelect = document.getElementById('modal_livestock_id');
            if (existingSelect.value === "") {
                showToast('Silakan pilih hewan terdaftar terlebih dahulu!', 'error');
                event.preventDefault();
                return false;
            }
            const selectedOption = existingSelect.options[existingSelect.selectedIndex];
            purchasePrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

            const existingQtyInput = document.querySelector('#existing-livestock-field input[name="qty"]');
            qty = parseInt(existingQtyInput.value) || 1;
        }

        if (paymentTypeChecked.value === 'dp') {
            const amountInput = document.getElementById('modal_amount_paid');
            const amountPaid = parseFloat(amountInput.value.replace(/\D/g, "")) || 0;
            const total = purchasePrice * qty;

            if (amountPaid <= 0) {
                showToast('Pembayaran awal (DP) harus diisi dan lebih besar dari 0!', 'error');
                event.preventDefault();
                return false;
            }
            if (amountPaid >= total) {
                showToast('Pembayaran DP tidak boleh melebihi atau sama dengan total harga beli. Gunakan tipe Lunas.', 'error');
                event.preventDefault();
                return false;
            }

            // Set raw numeric value before submission
            amountInput.value = amountPaid;
        }

        return true;
    }

    const allPurchasePayments = <?php echo json_encode($purchasePayments ?? []); ?>;

    function openLedgerModal(data) {
        document.getElementById('ledger-info-code').innerText = data.purchase_code;
        document.getElementById('ledger-info-breeder').innerText = data.peternak_name;

        const payments = allPurchasePayments.filter(p => parseInt(p.purchase_id) === parseInt(data.id));
        const body = document.getElementById('ledger-payments-body');
        body.innerHTML = '';

        let totalPaid = 0;

        if (payments.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400 font-bold">
                        Belum ada catatan pembayaran ledger.
                    </td>
                </tr>
            `;
        } else {
            payments.forEach((p, idx) => {
                const amount = parseFloat(p.payment_amount);
                totalPaid += amount;

                const dateObj = new Date(p.payment_date);
                const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                const formattedDate = dateObj.toLocaleDateString('id-ID', options);

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-all';
                row.innerHTML = `
                    <td class="px-4 py-3 font-bold text-gray-500">${idx + 1}</td>
                    <td class="px-4 py-3 font-semibold text-gray-600">${p.payment_code}</td>
                    <td class="px-4 py-3 text-gray-500">${formattedDate}</td>
                    <td class="px-4 py-3 font-black text-emerald-600">Rp ${formatNumber(amount).replace('Rp ', '')}</td>
                    <td class="px-4 py-3 text-gray-400 font-medium">${p.notes || '-'}</td>
                `;
                body.appendChild(row);
            });
        }

        document.getElementById('ledger-total-paid').innerText = 'Rp ' + formatNumber(totalPaid).replace('Rp ', '');

        const modal = document.getElementById('ledger-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
        }, 10);
    }

    function closeLedgerModal() {
        const modal = document.getElementById('ledger-modal');
        modal.classList.add('opacity-0');
        modal.firstElementChild.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>