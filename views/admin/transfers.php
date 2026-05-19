<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Pending Transactions
try {
    $stmt = $conn->query("
        SELECT st.*, sp.target_amount, u.name as customer_name, u.email as customer_email 
        FROM savings_transactions st
        JOIN savings_plans sp ON st.plan_id = sp.id
        JOIN users u ON sp.customer_id = u.id
        ORDER BY st.created_at DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Transfer - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#0d5bb5',
                            secondary: '#00a3e0',
                            light: '#e0f2fe',
                            dark: '#0a4286',
                            accent: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <!-- Top Navigation -->
        <?php include 'includes/topbar.php'; ?>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Verifikasi <span class="text-brand-primary">Transfer</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Validasi bukti pembayaran tabungan pelanggan</p>
                </div>
            </div>



            <!-- Transactions Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full" id="transfers-table">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pelanggan</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Jumlah</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Bukti</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Tanggal</th>
                                <th class="px-6 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="7" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Belum ada transaksi transfer.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $i => $tx): ?>
                                <tr class="hover:bg-brand-light/20 transition-colors group" id="tx-row-<?php echo $tx['id']; ?>">
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div>
                                            <p class="text-sm font-black text-gray-900 leading-none"><?php echo htmlspecialchars($tx['customer_name']); ?></p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1"><?php echo htmlspecialchars($tx['customer_email']); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-brand-primary">Rp <?php echo number_format($tx['amount']); ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <button onclick="viewProof('<?php echo htmlspecialchars($tx['proof_of_payment']); ?>')" class="text-brand-secondary hover:text-brand-primary flex items-center gap-2 font-bold text-xs">
                                            <i class="fas fa-image"></i> Lihat Bukti
                                        </button>
                                    </td>
                                    <td class="px-6 py-6">
                                        <?php 
                                            $statusClasses = [
                                                'pending' => 'bg-amber-50 text-amber-600',
                                                'verified' => 'bg-green-50 text-green-600',
                                                'rejected' => 'bg-red-50 text-red-600'
                                            ];
                                            $statusText = [
                                                'pending' => 'Menunggu',
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak'
                                            ];
                                            $class = $statusClasses[$tx['status']] ?? 'bg-gray-50 text-gray-400';
                                            $text = $statusText[$tx['status']] ?? $tx['status'];
                                        ?>
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full <?php echo $class; ?> uppercase tracking-wider">
                                            <?php echo $text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-xs font-bold text-gray-400"><?php echo date('d M Y H:i', strtotime($tx['created_at'])); ?></p>
                                    </td>
                                    <td class="px-6 py-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <?php if ($tx['status'] === 'pending'): ?>
                                                <button onclick="updateStatus(<?php echo $tx['id']; ?>, 'verified')" title="Verifikasi" class="w-9 h-9 rounded-md bg-green-50 text-green-500 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                                <button onclick="updateStatus(<?php echo $tx['id']; ?>, 'rejected')" title="Tolak" class="w-9 h-9 rounded-md bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                    <i class="fas fa-xmark text-xs"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button onclick="openDeleteModal(<?php echo $tx['id']; ?>, 'Rp <?php echo number_format($tx['amount']); ?>', '<?php echo addslashes($tx['customer_name']); ?>')" title="Hapus" class="w-9 h-9 rounded-md bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash text-xs"></i>
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

    <!-- Modal View Proof -->
    <div id="proof-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="proof-content" class="bg-white rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl transition-all duration-300 scale-95 opacity-0">
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-xl font-black text-gray-900 tracking-tight">Bukti Transfer</h3>
                <button onclick="closeProof()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
            </div>
            <div class="p-8 text-center">
                <img id="proof-img" src="" alt="Bukti Transfer" class="max-w-full h-auto rounded-2xl mx-auto shadow-lg">
            </div>
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50 flex justify-end">
                <button onclick="closeProof()" class="px-6 py-3 bg-brand-primary text-white rounded-lg font-black text-sm hover:bg-brand-dark transition-all">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="delete-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="delete-content" class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-95 opacity-0 p-10 text-center">
            <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Hapus Transaksi Tabungan?</h3>
            <p id="delete-message" class="text-sm text-gray-400 font-bold mb-8">Apakah Anda yakin ingin menghapus data transaksi tabungan ini? Tindakan ini tidak dapat dibatalkan.</p>
            <input type="hidden" id="delete-id">
            <div class="flex gap-4">
                <button onclick="closeDeleteModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-lg font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button onclick="executeDelete()" id="delete-btn" class="flex-1 px-6 py-4 bg-red-500 text-white rounded-lg font-black text-sm hover:bg-red-600 shadow-xl shadow-red-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> <span>Ya, Hapus</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function showSuccessNotification(message) { showToast(message, 'success'); }
        function showErrorNotification(message) { showToast(message, 'error'); }

        function viewProof(url) {
            const overlay = document.getElementById('proof-overlay');
            const content = document.getElementById('proof-content');
            const img = document.getElementById('proof-img');
            
            // Handle placeholder or actual URL
            if (!url.startsWith('http') && !url.startsWith('/')) {
                img.src = '/lautan-ternak-pantura/assets/uploads/proofs/' + url;
            } else {
                img.src = url;
            }
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-95');
            }, 10);
        }

        function closeProof() {
            const overlay = document.getElementById('proof-overlay');
            const content = document.getElementById('proof-content');
            content.classList.add('opacity-0', 'scale-95');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function updateStatus(id, status) {
            const action = status === 'verified' ? 'memverifikasi' : 'menolak';
            if (!confirm(`Apakah Anda yakin ingin ${action} transaksi ini?`)) return;

            try {
                const res = await fetch('/lautan-ternak-pantura/api/admin/verify_transfer', {
                    method: 'POST',
                    body: JSON.stringify({ id, status }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    showSuccessNotification(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Koneksi bermasalah atau data tidak valid');
            }
        }

        function openDeleteModal(id, amount, customer) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-message').innerText = `Apakah Anda yakin ingin menghapus transaksi tabungan sebesar ${amount} oleh pelanggan "${customer}"? Tindakan ini tidak dapat dibatalkan.`;
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-95');
            }, 10);
        }

        function closeDeleteModal() {
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            content.classList.add('opacity-0', 'scale-95');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function executeDelete() {
            const id = document.getElementById('delete-id').value;
            const btn = document.getElementById('delete-btn');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menghapus...</span>';
            btn.disabled = true;
            try {
                const res = await fetch(`/lautan-ternak-pantura/api/admin/delete_saving_transaction?id=${id}`, {
                    method: 'POST'
                });
                const data = await res.json();
                if (data.success) {
                    closeDeleteModal();
                    showSuccessNotification(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Gagal menghubungi server');
            }
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }

        // Client-side pagination engine
        let currentPage = 1;
        let rowsPerPage = 10;
        let tableRows = [];

        function initPagination() {
            tableRows = Array.from(document.querySelectorAll('#transfers-table tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
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
</body>
</html>
