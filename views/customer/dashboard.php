<?php 
require_once '../../config/database.php';
require_once '../../includes/header.php'; 

// Check if user is logged in and is a customer
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    // For demo purposes, we won't strictly redirect, just a warning
    // header("Location: /lautan-ternak-pantura/views/auth/login.php");
    // exit;
}
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Customer</h1>
                <p class="mt-1 text-sm text-gray-600">Selamat datang, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Siti Customer'; ?>!</p>
            </div>
            <a href="/lautan-ternak-pantura/marketplace.php" class="bg-brand-green text-white px-4 py-2 rounded-md shadow-sm hover:bg-brand-dark transition text-sm font-medium">Cari Hewan</a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-full bg-brand-light text-brand-green mr-4">
                    <i class="fas fa-wallet text-2xl w-8 text-center"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Tabungan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp 3.500.000</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-box text-2xl w-8 text-center"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Pesanan Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">1</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <i class="fas fa-clock text-2xl w-8 text-center"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tagihan Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900">Rp 350.000</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Rencana Tabungan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Rencana Tabungan Qurban</h3>
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Aktif</span>
                </div>
                <div class="p-6">
                    <div class="mb-4 flex justify-between items-end">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Target</p>
                            <p class="text-xl font-bold text-gray-900">Rp 3.500.000</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 mb-1">Terkumpul</p>
                            <p class="text-xl font-bold text-brand-green">Rp 1.050.000</p>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div class="bg-brand-green h-2.5 rounded-full" style="width: 30%"></div>
                    </div>
                    <p class="text-xs text-gray-500 text-right mb-6">30% Tercapai</p>

                    <button class="w-full bg-brand-light text-brand-dark border border-brand-green border-opacity-30 py-2 rounded-md font-medium hover:bg-green-100 transition shadow-sm">Bayar Cicilan (Rp 350.000)</button>
                </div>
            </div>

            <!-- Riwayat Pesanan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Pesanan</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-gray-100">
                        <li class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between transition cursor-pointer">
                            <div class="flex items-center">
                                <img class="h-12 w-12 rounded-lg object-cover" src="https://images.unsplash.com/photo-1511117833895-4b473c0b85d6?auto=format&fit=crop&w=100&q=80" alt="Kambing">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Kambing Super (Qurban)</p>
                                    <p class="text-xs text-gray-500">ORD-20230501-001</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>
                            </div>
                        </li>
                        <li class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between transition cursor-pointer">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Sapi 1/7 Bagian</p>
                                    <p class="text-xs text-gray-500">ORD-20220615-042</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Selesai</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
