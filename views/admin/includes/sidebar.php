<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentUri = $_SERVER['REQUEST_URI'];
?>
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
    }
    
    /* Sleek Active Sidebar Highlight & Focus styling */
    .sidebar-link {
        border-left: 4px solid transparent;
        transition: all 0.2s ease-in-out;
    }
    
    .sidebar-link.active {
        background: linear-gradient(90deg, rgba(13, 91, 181, 0.08) 0%, rgba(13, 91, 181, 0) 100%) !important;
        color: #0d5bb5 !important;
        border-left-color: #0d5bb5 !important;
        font-weight: 800 !important;
        padding-left: 1.5rem !important;
    }
    
    .sidebar-link.active i {
        color: #0d5bb5 !important;
        transform: scale(1.15);
    }

    .sidebar-link:not(.active):hover {
        border-left-color: rgba(13, 91, 181, 0.3) !important;
        background-color: rgba(13, 91, 181, 0.02) !important;
        color: #0d5bb5 !important;
        padding-left: 1.25rem;
    }

    .sidebar-link:hover i {
        transform: scale(1.1);
    }
</style>
<!-- Dummy Spacer to occupy space in flex flow on desktop -->
<div class="w-72 shrink-0 hidden lg:block"></div>

<!-- Sidebar Backdrop for Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden" onclick="toggleMobileSidebar()"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="w-72 bg-white border-r border-gray-100 flex flex-col fixed left-0 top-0 h-screen z-50 transition-all duration-300 transform -translate-x-full lg:translate-x-0 lg:z-30 lg:flex">
    <!-- Close Button for Mobile -->
    <button onclick="toggleMobileSidebar()" class="lg:hidden w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center absolute top-9 right-6">
        <i class="fas fa-times"></i>
    </button>

    <div class="p-8 shrink-0">
        <a href="/lautan-ternak-pantura/index" class="flex items-center gap-3">
            <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
            <div class="leading-tight">
                <span class="text-xl font-black text-brand-primary tracking-tighter uppercase block">LTP Admin</span>
                <span
                    class="text-brand-secondary text-[10px] font-bold tracking-widest uppercase block -mt-1">Manajemen</span>
            </div>
        </a>
    </div>

    <nav class="flex-grow px-4 space-y-1 overflow-y-auto no-scrollbar"
        style="scrollbar-width: none; -ms-overflow-style: none;">
        <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Menu Utama</p>

        <a href="/lautan-ternak-pantura/views/admin/dashboard"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo $currentPage == 'dashboard' || strpos($currentUri, '/views/admin/dashboard') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-house text-lg"></i>
            <span>Dashboard</span>
        </a>

        <a href="/lautan-ternak-pantura/views/admin/users"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo $currentPage == 'users' || strpos($currentUri, '/views/admin/users') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-user text-lg"></i>
            <span>Kelola Pengguna</span>
        </a>

        <a href="/lautan-ternak-pantura/savings/management"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/savings/management') !== false || strpos($currentUri, '/savings/adminDetail') !== false || $currentPage == 'transfers' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-receipt text-lg"></i>
            <span>Tabungan Qurban</span>
        </a>

        <a href="/lautan-ternak-pantura/savings/groups"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/savings/groups') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-users text-lg"></i>
            <span>Kelompok Qurban Sapi</span>
        </a>

        <a href="/lautan-ternak-pantura/livestock/index"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/livestock') !== false || $currentPage == 'livestock' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-paw text-lg"></i>
            <span>Data Hewan Ternak</span>
        </a>

        <a href="/lautan-ternak-pantura/sales/index"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/sales') !== false || $currentPage == 'sales' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-shopping-cart text-lg"></i>
            <span>Penjualan Hewan</span>
        </a>

        <a href="/lautan-ternak-pantura/purchase/index"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/purchase/') !== false || strpos($currentUri, '/purchase_create') !== false || $currentPage == 'purchases' || $currentPage == 'purchase_create' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
            <span>Pembelian Hewan</span>
        </a>

        <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest pt-6 mb-4">Keuangan</p>

        <a href="/lautan-ternak-pantura/finance/cash"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/finance/cash') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-building-columns text-lg"></i>
            <span>Kas & Bank</span>
        </a>

        <a href="/lautan-ternak-pantura/finance/investor"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/finance/investor') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
            <span>Modal Investor</span>
        </a>

        <a href="/lautan-ternak-pantura/finance/operasional"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/finance/operasional') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-money-bill-wave text-lg"></i>
            <span>Dana Operasional</span>
        </a>

        <a href="/lautan-ternak-pantura/finance/arusKas"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/finance/arusKas') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-receipt text-lg"></i>
            <span>Arus Kas</span>
        </a>

        <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest pt-6 mb-4">Laporan Penjualan</p>

        <a href="/lautan-ternak-pantura/report/daily"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/report/daily') !== false || $currentPage == 'reports_daily' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-chart-line text-lg"></i>
            <span>Laporan Harian</span>
        </a>

        <a href="/lautan-ternak-pantura/report/monthly"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/report/monthly') !== false || $currentPage == 'reports_monthly' ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span>Laporan Bulanan</span>
        </a>

        <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest pt-6 mb-4">Laporan Tabungan</p>

        <a href="/lautan-ternak-pantura/savingsReport/daily"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/savingsReport/daily') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-calendar-day text-lg"></i>
            <span>Harian Tabungan</span>
        </a>

        <a href="/lautan-ternak-pantura/savingsReport/monthly"
            class="sidebar-link flex items-center gap-4 px-4 py-3.5 rounded-r-lg font-bold text-sm transition-all group <?php echo strpos($currentUri, '/savingsReport/monthly') !== false ? 'active text-brand-primary' : 'text-gray-500'; ?>">
            <i class="fa-solid fa-chart-column text-lg"></i>
            <span>Bulanan Tabungan</span>
        </a>
    </nav>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const activeLink = document.querySelector('.sidebar-link.active');
        if (activeLink) {
            activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.remove('hidden');
            setTimeout(() => backdrop.classList.add('opacity-100'), 10);
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.remove('opacity-100');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }
    </script>

    <div class="p-6 mt-auto border-t border-gray-50 shrink-0">
        <a href="/lautan-ternak-pantura/api/auth/logout"
            class="flex items-center gap-4 px-4 py-3.5 text-red-500 hover:bg-red-50 rounded-lg font-bold text-sm transition-all group">
            <i class="fas fa-right-from-bracket text-lg"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</aside>
