<!-- Top Navigation -->
<header class="bg-white/80 backdrop-blur-md fixed top-0 right-0 left-0 lg:left-72 z-40 border-b border-gray-100 px-8 py-4 flex items-center justify-between shrink-0 h-20">
    <div class="flex items-center gap-4">
        <button onclick="toggleMobileSidebar()" class="lg:hidden text-gray-500 text-xl"><i class="fas fa-bars"></i></button>
    </div>

    <div class="flex items-center gap-6">
        <button class="relative p-2 text-gray-400 hover:text-brand-primary transition-colors">
            <i class="far fa-bell text-xl"></i>
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>
        <div class="h-10 w-[1px] bg-gray-100"></div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-black text-gray-900 leading-none"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></p>
                <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Super Admin</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-primary border-2 border-brand-primary/20">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
</header>
<!-- Spacer to occupy layout height of the fixed topbar -->
<div class="h-20 shrink-0"></div>
