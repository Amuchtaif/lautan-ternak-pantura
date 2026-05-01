<?php 
require_once '../../config/database.php';
require_once '../../includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex">
        
        <!-- Sidebar Menu -->
        <div class="w-64 bg-white rounded-xl shadow-sm border border-gray-100 p-5 mr-8 hidden md:block">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Peternak Menu</h2>
            <nav class="space-y-2">
                <a href="#" class="flex items-center gap-3 px-3 py-2 bg-brand-light text-brand-primary rounded-md font-medium text-sm transition">
                    <i class="fas fa-home w-5 text-center"></i> Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-paw w-5 text-center"></i> Kelola Hewan
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-clipboard-list w-5 text-center"></i> Pesanan Masuk
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-wallet w-5 text-center"></i> Pendapatan
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Peternak</h1>
                    <p class="mt-1 text-sm text-gray-600">Selamat datang, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Budi Peternak'; ?>!</p>
                </div>
                <button class="bg-brand-primary text-white px-4 py-2 rounded-md shadow-sm hover:bg-brand-dark transition text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Hewan
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-brand-light text-brand-primary mr-4">
                        <i class="fas fa-paw text-2xl w-8 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Hewan Tersedia</p>
                        <p class="text-2xl font-bold text-gray-900">15 Ekor</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-clipboard-check text-2xl w-8 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Pesanan Selesai</p>
                        <p class="text-2xl font-bold text-gray-900">8</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-wallet text-2xl w-8 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Pendapatan Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">Rp 45M</p>
                    </div>
                </div>
            </div>

            <!-- Hewan Saya -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Hewan Ternak Saya</h3>
                    <a href="#" class="text-sm text-brand-primary font-medium hover:text-brand-dark transition">Kelola Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hewan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-lg object-cover" src="https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Sapi Brahma</div>
                                            <div class="text-xs text-gray-500">Qurban</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">350 kg</div>
                                    <div class="text-xs text-gray-500">24 Bulan</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp 21.000.000
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Tersedia</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-brand-primary hover:text-brand-dark mr-3 transition"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-600 hover:text-red-900 transition"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-lg object-cover" src="https://images.unsplash.com/photo-1511117833895-4b473c0b85d6?auto=format&fit=crop&q=80" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Kambing Etawa</div>
                                            <div class="text-xs text-gray-500">Aqiqah</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">40 kg</div>
                                    <div class="text-xs text-gray-500">16 Bulan</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp 4.000.000
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Dipesan</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-gray-400 cursor-not-allowed mr-3" disabled><i class="fas fa-edit"></i></button>
                                    <button class="text-gray-400 cursor-not-allowed" disabled><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
