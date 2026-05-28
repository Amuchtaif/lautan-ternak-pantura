<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'views/admin/includes/header.php';
require_once 'views/admin/includes/sidebar.php';
?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <?php include 'includes/topbar.php'; ?>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kelola <span class="text-brand-primary">Hewan Ternak</span></h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Manajemen inventori hewan kurban & aqiqah</p>
                </div>
            </div>

            <!-- Session Notifications using Toast -->
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

            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                <form method="GET" class="w-full flex flex-col md:flex-row gap-4">
                    <div class="relative flex-grow">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Cari breed, kode, nama peternak..." 
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all text-sm font-semibold">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-2xl font-bold text-sm hover:bg-gray-800 transition-all shrink-0">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Table / Catalog -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Hewan / Info</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Mitra Peternak</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kondisi & Stok</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] min-w-[200px]">Harga Jual/Beli</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($livestockList)): ?>
                                <tr>
                                    <td colspan="7" class="px-8 py-12 text-center text-gray-400 font-bold text-sm">
                                        Tidak ditemukan data hewan ternak.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($livestockList as $i => $item): ?>
                                <tr class="hover:bg-brand-light/10 transition-colors group">
                                    <td class="px-8 py-6">
                                        <p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 rounded-2xl bg-gray-100 overflow-hidden shrink-0 border border-gray-100 shadow-inner">
                                                <img src="<?php echo htmlspecialchars($item['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80'); ?>" alt="photo" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-gray-900 leading-none capitalize"><?php echo htmlspecialchars($item['name']); ?></p>
                                                
                                                <div class="flex gap-2 mt-1.5">
                                                    <span class="text-[9px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full uppercase tracking-wider"><?php echo $item['gender'] === 'male' ? 'Jantan' : 'Betina'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-xs font-black text-gray-800 capitalize"><?php echo htmlspecialchars($item['peternak_name']); ?></p>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <p class="text-xs font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-weight-hanging text-brand-primary w-4"></i> <?php echo number_format($item['weight'], 2); ?> kg</p>
                                            <p class="text-xs font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-calendar-alt text-brand-primary w-4"></i> <?php echo htmlspecialchars($item['age']); ?> bulan</p>
                                            <p class="text-xs font-bold text-gray-700 flex items-center gap-2"><i class="fas fa-cubes text-brand-primary w-4"></i> Stok: <span class="font-extrabold text-brand-primary"><?php echo htmlspecialchars($item['stock']); ?> ekor</span></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1.5 min-w-[150px]">
                                            <div class="flex justify-between items-center bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl text-[10px] font-black border border-emerald-100">
                                                <span>JUAL</span>
                                                <span>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center bg-gray-50 text-gray-600 px-3 py-1.5 rounded-xl text-[10px] font-black border border-gray-100">
                                                <span>BELI</span>
                                                <span>Rp <?php echo number_format($item['purchase_price'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php 
                                            $statusClasses = [
                                                'available' => 'bg-green-50 text-green-600 border-green-100',
                                                'reserved' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'sold' => 'bg-gray-100 text-gray-500 border-gray-200',
                                                'inactive' => 'bg-red-50 text-red-600 border-red-100'
                                            ];
                                            $statusText = [
                                                'available' => 'Tersedia',
                                                'reserved' => 'Dipesan',
                                                'sold' => 'Habis / Terjual',
                                                'inactive' => 'Tidak Aktif'
                                            ];
                                            $currentClass = $statusClasses[$item['status']] ?? 'bg-gray-50 text-gray-400';
                                            $currentText = $statusText[$item['status']] ?? $item['status'];
                                        ?>
                                        <span class="px-3 py-1.5 text-[9px] font-black rounded-full border uppercase <?php echo $currentClass; ?>">
                                            <?php echo $currentText; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="/lautan-ternak-pantura/livestock/edit/<?php echo $item['id']; ?>" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-pen text-sm"></i>
                                            </a>
                                            <button onclick="openDeleteModal(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="delete-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="delete-content" class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-trash text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-black text-gray-900 tracking-tight mb-2">Hapus Data Hewan?</h3>
            <p id="delete-message" class="text-sm text-gray-400 font-bold mb-6">Apakah Anda yakin ingin menghapus data ini dari sistem?</p>
            <form action="/lautan-ternak-pantura/livestock/delete" method="POST" class="flex gap-4">
                <input type="hidden" name="id" id="delete-id">
                <button type="button" onclick="closeDeleteModal()" class="flex-grow px-5 py-3.5 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button type="submit" class="flex-grow px-5 py-3.5 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 shadow-lg shadow-red-500/20 transition-all">Hapus</button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id, name) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-message').innerText = `Apakah Anda yakin ingin menghapus "${name}"? Data hewan ini akan dihapus permanen.`;
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
    </script>
<?php require_once 'views/admin/includes/footer.php'; ?>
