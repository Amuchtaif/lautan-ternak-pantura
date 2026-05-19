<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Livestock Data with Search Support
$search = $_GET['search'] ?? '';
try {
    if ($search) {
        $stmt = $conn->prepare("SELECT * FROM livestock WHERE name LIKE ? OR breed LIKE ? OR code LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    } else {
        $stmt = $conn->query("SELECT * FROM livestock ORDER BY created_at DESC");
    }
    $livestockList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
    $livestockList = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hewan - Admin Dashboard</title>
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
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
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
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kelola <span class="text-brand-primary">Hewan Ternak</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Manajemen stok hewan kurban & aqiqah</p>
                </div>
            </div>



            <!-- Actions Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="relative w-full sm:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari hewan (Sapi, Kambing, ID...)" 
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-100 rounded-2xl outline-none focus:border-brand-primary transition-all text-sm font-medium shadow-sm"
                        onkeypress="if(event.key === 'Enter') window.location.href = '?search=' + this.value">
                </div>
            </div>

            <!-- Livestock Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Hewan / Info</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Berat & Stok</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] min-w-[170px]">Harga Jual/Beli</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($livestockList)): ?>
                                <tr>
                                    <td colspan="6" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Belum ada data hewan ternak.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($livestockList as $i => $item): ?>
                                <tr class="hover:bg-brand-light/20 transition-colors group" id="livestock-row-<?php echo $item['id']; ?>">
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 rounded-2xl bg-gray-100 overflow-hidden shrink-0 border border-gray-100">
                                                <img src="<?php echo htmlspecialchars($item['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-gray-900 leading-none capitalize"><?php echo htmlspecialchars($item['name']); ?></p>
                                                <p class="text-[10px] font-bold text-gray-400 mt-1.5 uppercase tracking-widest">
                                                    <?php echo htmlspecialchars($item['breed']); ?> &middot; <?php echo $item['gender'] === 'male' ? 'Jantan' : 'Betina'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <p class="text-xs font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-weight-hanging text-brand-primary w-4"></i> <?php echo number_format($item['weight'], 2); ?> kg</p>
                                            <p class="text-xs font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-cubes text-brand-primary w-4"></i> <?php echo htmlspecialchars($item['stock']); ?> ekor</p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1.5 min-w-[140px]">
                                            <div class="flex justify-between items-center bg-emerald-50 text-emerald-700 px-3 py-1 rounded-md text-[11px] font-black">
                                                <span>JUAL</span>
                                                <span>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center bg-gray-50 text-gray-600 px-3 py-1 rounded-md text-[11px] font-black border border-gray-100">
                                                <span>BELI</span>
                                                <span>Rp <?php echo number_format($item['purchase_price'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php 
                                            $statusClasses = [
                                                'available' => 'bg-green-50 text-green-600',
                                                'booked' => 'bg-amber-50 text-amber-600',
                                                'sold' => 'bg-gray-100 text-gray-500'
                                            ];
                                            $statusText = [
                                                'available' => 'Tersedia',
                                                'booked' => 'Dipesan',
                                                'sold' => 'Terjual'
                                            ];
                                            $currentClass = $statusClasses[$item['status']] ?? 'bg-gray-50 text-gray-400';
                                            $currentText = $statusText[$item['status']] ?? $item['status'];
                                        ?>
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full <?php echo $currentClass; ?> uppercase">
                                            <?php echo $currentText; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($item)); ?>)" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash"></i>
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

    <!-- Modal Edit/Add Livestock -->
    <div id="modal-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="modal-content" class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 flex flex-col">
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 shrink-0">
                <h3 id="modal-title" class="text-xl font-black text-gray-900 tracking-tight text-brand-dark">Edit Hewan Ternak</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
            </div>
            <div class="flex-grow overflow-y-auto custom-scrollbar">
                <form id="livestock-form" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="code" id="edit-code">
                
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Hewan</label>
                    <input type="text" name="name" id="edit-name" required placeholder="Contoh: Sapi Limosin Jumbo" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Ras / Breed</label>
                    <input type="text" name="breed" id="edit-breed" required placeholder="Contoh: Limosin" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Kelamin</label>
                    <div class="relative">
                        <select name="gender" id="edit-gender" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                            <option value="male">Jantan</option>
                            <option value="female">Betina</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Berat (kg)</label>
                    <input type="number" step="0.01" name="weight" id="edit-weight" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jumlah Stok</label>
                    <input type="number" name="stock" id="edit-stock" required class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm" value="1">
                </div>

                 <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Jual (Rp)</label>
                    <input type="text" name="price" id="edit-price" required placeholder="Contoh: 20.000.000" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-lg outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm" oninput="formatCurrency(this)">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Beli (Rp)</label>
                    <input type="text" name="purchase_price" id="edit-purchase-price" required placeholder="Contoh: 16.000.000" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-lg outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm" oninput="formatCurrency(this)">
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi / Kondisi</label>
                    <textarea name="description" id="edit-description" rows="2" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm" placeholder="Masukkan deskripsi detail mengenai kondisi hewan..."></textarea>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Status Stok</label>
                    <div class="relative">
                        <select name="status" id="edit-status" class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none">
                            <option value="available">Tersedia</option>
                            <option value="booked">Dipesan</option>
                            <option value="sold">Terjual</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <div class="space-y-2 md:col-span-2" id="image-upload-wrapper">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Foto Hewan</label>
                    <div id="drop-zone" class="w-full border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center cursor-pointer hover:border-brand-primary/40 hover:bg-brand-light/10 transition-all" onclick="document.getElementById('edit-image').click()">
                        <div id="upload-placeholder">
                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-cloud-arrow-up text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-sm font-bold text-gray-500">Klik untuk unggah foto baru</p>
                            <p class="text-[10px] font-bold text-gray-300 mt-1">JPG, PNG, WEBP (Maks. 2MB)</p>
                        </div>
                        <div id="image-preview" class="hidden">
                            <img id="preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-2xl mx-auto border-2 border-gray-100">
                            <p id="preview-name" class="text-xs font-bold text-gray-500 mt-2"></p>
                        </div>
                    </div>
                    <input type="file" name="image" id="edit-image" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewImage(this)">
                </div>

                <div class="md:col-span-2 pt-6 flex gap-4">
                    <button type="button" onclick="closeModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" id="submit-btn" class="flex-1 px-6 py-4 bg-brand-primary text-white rounded-2xl font-black text-sm hover:bg-brand-dark shadow-xl shadow-brand-primary/20 transition-all flex items-center justify-center gap-2">
                        <span>Simpan Data Hewan</span>
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="delete-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="delete-content" class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 p-10 text-center">
            <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Hapus Data?</h3>
            <p id="delete-message" class="text-sm text-gray-400 font-bold mb-8">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.</p>
            <input type="hidden" id="delete-id">
            <div class="flex gap-4">
                <button onclick="closeDeleteModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button onclick="executeDelete()" id="delete-btn" class="flex-1 px-6 py-4 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 shadow-xl shadow-red-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> <span>Ya, Hapus</span>
                </button>
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

        // Client-side pagination engine
        let currentPage = 1;
        let rowsPerPage = 10;
        let tableRows = [];

        function initPagination() {
            tableRows = Array.from(document.querySelectorAll('tbody tr')).filter(row => !row.cells[0].classList.contains('text-center'));
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
                // Limit visible pages if there are too many (simple sliding window)
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

        function showSuccessNotification(message) { showToast(message, 'success'); }
        function showErrorNotification(message) { showToast(message, 'error'); }

        function openModal(type, data = null) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('livestock-form');
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
            
            if (type === 'edit' && data) {
                title.innerText = 'Edit Hewan Ternak';
                document.getElementById('edit-id').value = data.id;
                document.getElementById('edit-code').value = data.code || '';
                document.getElementById('edit-name').value = data.name || '';
                document.getElementById('edit-breed').value = data.breed || '';
                document.getElementById('edit-gender').value = data.gender || 'male';
                document.getElementById('edit-weight').value = data.weight;
                document.getElementById('edit-stock').value = data.stock || 1;
                document.getElementById('edit-price').value = formatNumber(data.price);
                document.getElementById('edit-purchase-price').value = formatNumber(data.purchase_price);
                document.getElementById('edit-status').value = data.status;
                document.getElementById('edit-description').value = data.description || '';
                
                // Show existing image if available
                const imgUrl = data.image;
                if (imgUrl) {
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('image-preview').classList.remove('hidden');
                    document.getElementById('preview-img').src = imgUrl;
                    document.getElementById('preview-name').innerText = 'Gambar saat ini';
                } else {
                    resetImagePreview();
                }
            } else {
                title.innerText = 'Tambah Hewan Baru';
                form.reset();
                document.getElementById('edit-id').value = '';
                document.getElementById('edit-code').value = '';
                document.getElementById('edit-name').value = '';
                document.getElementById('edit-breed').value = '';
                document.getElementById('edit-gender').value = 'male';
                document.getElementById('edit-weight').value = '';
                document.getElementById('edit-stock').value = '1';
                document.getElementById('edit-price').value = '';
                document.getElementById('edit-purchase-price').value = '';
                document.getElementById('edit-status').value = 'available';
                document.getElementById('edit-description').value = '';
                resetImagePreview();
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 2 * 1024 * 1024) {
                    showErrorNotification('Ukuran file maksimal 2MB');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('image-preview').classList.remove('hidden');
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('preview-name').innerText = file.name;
                };
                reader.readAsDataURL(file);
            }
        }

        function resetImagePreview() {
            document.getElementById('upload-placeholder').classList.remove('hidden');
            document.getElementById('image-preview').classList.add('hidden');
            document.getElementById('preview-img').src = '';
            document.getElementById('preview-name').innerText = '';
            document.getElementById('edit-image').value = '';
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        function openDeleteModal(id, name) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-message').innerText = `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak bisa dibatalkan.`;
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
        }

        function closeDeleteModal() {
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function executeDelete() {
            const id = document.getElementById('delete-id').value;
            const btn = document.getElementById('delete-btn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menghapus...</span>';
            btn.disabled = true;
            try {
                const res = await fetch(`/lautan-ternak-pantura/api/admin/delete_livestock?id=${id}`);
                const data = await res.json();
                if (data.success) {
                    closeDeleteModal();
                    showSuccessNotification(data.message);
                    const row = document.getElementById(`livestock-row-${id}`);
                    row.classList.add('opacity-0', '-translate-y-4');
                    setTimeout(() => row.remove(), 300);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Gagal menghubungi server');
            }
            btn.innerHTML = '<i class="fas fa-trash"></i> <span>Ya, Hapus</span>';
            btn.disabled = false;
        }

        document.getElementById('livestock-form').onsubmit = async function(e) {
            e.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            const formData = new FormData(this);
            if (formData.has('price')) {
                formData.set('price', formData.get('price').replace(/\D/g, ""));
            }
            if (formData.has('purchase_price')) {
                formData.set('purchase_price', formData.get('purchase_price').replace(/\D/g, ""));
            }
            
            const isEdit = formData.get('id') && formData.get('id') !== '';
            const url = isEdit ? '/lautan-ternak-pantura/api/admin/update_livestock' : '/lautan-ternak-pantura/api/admin/add_livestock';

            const btn = document.getElementById('submit-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menyimpan...</span>';
            btn.disabled = true;

            try {
                const res = await fetch(url, { 
                    method: 'POST', 
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    showSuccessNotification(data.message);
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorNotification(data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                showErrorNotification('Koneksi bermasalah atau data tidak valid');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };
    </script>
</body>
</html>
