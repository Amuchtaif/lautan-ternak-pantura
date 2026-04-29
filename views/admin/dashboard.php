<?php 
require_once '../../config/database.php';
require_once '../../includes/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex">
        
        <!-- Sidebar Menu -->
        <div class="w-64 bg-white rounded-xl shadow-sm border border-gray-100 p-5 mr-8 hidden md:block">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Admin Menu</h2>
            <nav class="space-y-2">
                <a href="#" class="flex items-center gap-3 px-3 py-2 bg-brand-light text-brand-green rounded-md font-medium text-sm transition">
                    <i class="fas fa-home w-5 text-center"></i> Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-users w-5 text-center"></i> Users
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-money-check-alt w-5 text-center"></i> Verifikasi Transfer
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-piggy-bank w-5 text-center"></i> Tabungan Qurban
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-md font-medium text-sm transition">
                    <i class="fas fa-shopping-cart w-5 text-center"></i> Transaksi Marketplace
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
                    <p class="mt-1 text-sm text-gray-600">Ringkasan aktivitas platform Lautan Ternak Pantura.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-brand-light text-brand-green mr-4">
                        <i class="fas fa-users text-xl w-6 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total User</p>
                        <p class="text-2xl font-bold text-gray-900">1,240</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-cow text-xl w-6 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Hewan Tersedia</p>
                        <p class="text-2xl font-bold text-gray-900">45</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-exclamation-circle text-xl w-6 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Perlu Verifikasi</p>
                        <p class="text-2xl font-bold text-gray-900">12</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-chart-line text-xl w-6 text-center"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Omset</p>
                        <p class="text-xl font-bold text-gray-900">Rp 125M</p>
                    </div>
                </div>
            </div>

            <!-- Recent Verifications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Menunggu Verifikasi Pembayaran</h3>
                    <a href="#" class="text-sm text-brand-green font-medium hover:text-brand-dark transition">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User / ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Pembayaran</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Siti Customer</div>
                                    <div class="text-xs text-gray-500">TRX-00123</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Cicilan Tabungan</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp 350.000
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    29 Apr 2026
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-brand-green hover:text-brand-dark mr-3 transition font-semibold">Verifikasi</button>
                                    <button class="text-red-600 hover:text-red-900 transition">Tolak</button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Agus Customer</div>
                                    <div class="text-xs text-gray-500">ORD-00992</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Pelunasan Hewan</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp 1.000.000
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    28 Apr 2026
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-brand-green hover:text-brand-dark mr-3 transition font-semibold">Verifikasi</button>
                                    <button class="text-red-600 hover:text-red-900 transition">Tolak</button>
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
