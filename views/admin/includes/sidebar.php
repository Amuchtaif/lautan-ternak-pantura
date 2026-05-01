<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar -->
<aside class="w-72 bg-white border-r border-gray-100 flex flex-col sticky top-0 h-screen hidden lg:flex">
    <div class="p-8">
        <a href="/lautan-ternak-pantura/index" class="flex items-center gap-3">
            <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
            <div class="leading-tight">
                <span class="text-xl font-black text-brand-primary tracking-tighter uppercase block">LTP Admin</span>
                <span
                    class="text-brand-secondary text-[10px] font-bold tracking-widest uppercase block -mt-1">Manajemen</span>
            </div>
        </a>
    </div>

    <nav class="flex-grow px-4 space-y-1">
        <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Menu Utama</p>

        <a href="dashboard"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-xl font-bold text-sm transition-all group <?php echo $currentPage == 'dashboard' ? 'active' : 'text-gray-500 hover:bg-gray-50'; ?>">
            <i class="fa-solid fa-house text-lg"></i>
            <span>Beranda Dashboard</span>
        </a>

        <a href="users"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-xl font-bold text-sm transition-all group <?php echo $currentPage == 'users' ? 'active' : 'text-gray-500 hover:bg-gray-50'; ?>">
            <i class="fa-solid fa-users text-lg"></i>
            <span>Kelola Pengguna</span>
        </a>

        <a href="transfers"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-xl font-bold text-sm transition-all group <?php echo $currentPage == 'transfers' ? 'active' : 'text-gray-500 hover:bg-gray-50'; ?>">
            <i class="fa-solid fa-receipt text-lg"></i>
            <span>Verifikasi Transfer</span>
        </a>

        <a href="livestock"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-xl font-bold text-sm transition-all group <?php echo $currentPage == 'livestock' ? 'active' : 'text-gray-500 hover:bg-gray-50'; ?>">
            <i class="fa-solid fa-paw text-lg"></i>
            <span>Data Hewan Ternak</span>
        </a>

        <a href="#"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-gray-500 hover:bg-gray-50 rounded-xl font-bold text-sm transition-all group">
            <i class="fa-solid fa-piggy-bank text-lg"></i>
            <span>Program Tabungan</span>
        </a>

        <a href="#"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-gray-500 hover:bg-gray-50 rounded-xl font-bold text-sm transition-all group">
            <i class="fa-solid fa-shopping-cart text-lg"></i>
            <span>Transaksi Pasar</span>
        </a>
    </nav>

    <div class="p-6 mt-auto border-t border-gray-50">
        <a href="/lautan-ternak-pantura/api/auth/logout"
            class="flex items-center gap-4 px-4 py-3.5 text-red-500 hover:bg-red-50 rounded-xl font-bold text-sm transition-all group">
            <i class="fas fa-right-from-bracket text-lg"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</aside>