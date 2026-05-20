<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Premium Header & Filters -->
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between mb-12 gap-8">
            <div class="max-w-xl">
                <h2 class="text-base text-brand-secondary font-bold tracking-widest uppercase mb-2">Katalog Kami</h2>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">Pilih Hewan <span class="text-brand-primary">Terbaik</span></h1>
                <p class="text-gray-500 text-lg leading-relaxed">Pilih hewan qurban atau aqiqah premium yang telah kami seleksi ketat sesuai standar kesehatan dan syariat Islam.</p>
            </div>
            
            <div class="flex flex-wrap gap-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 items-center">
                <form class="flex flex-wrap gap-4 w-full sm:w-auto" method="GET" action="/lautan-ternak-pantura/marketplace">
                    <div class="relative min-w-[140px]">
                        <select name="type" class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border-none focus:ring-2 focus:ring-brand-primary/20 sm:text-sm rounded-xl cursor-pointer font-bold text-gray-700">
                            <option value="">Semua Kategori</option>
                            <option value="qurban" <?php echo (isset($_GET['type']) && $_GET['type'] == 'qurban') ? 'selected' : ''; ?>>Qurban</option>
                            <option value="aqiqah" <?php echo (isset($_GET['type']) && $_GET['type'] == 'aqiqah') ? 'selected' : ''; ?>>Aqiqah</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    <button type="submit" class="bg-brand-primary text-white px-8 py-3 rounded-xl hover:bg-brand-dark transition-all shadow-md shadow-brand-primary/20 text-sm font-black flex items-center gap-2">
                        <i class="fas fa-filter text-xs"></i> Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Animal Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach($livestocks as $item): ?>
            <div class="group relative bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 flex flex-col">
                <!-- Image Container -->
                <div class="relative h-64 overflow-hidden p-4">
                    <div class="w-full h-full rounded-2xl overflow-hidden relative">
                        <img src="<?php echo htmlspecialchars($item['image'] ?? $item['image_url']); ?>" alt="Hewan" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        

                    </div>
                </div>

                <!-- Content -->
                <div class="px-8 pb-8 pt-2 flex-grow flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 capitalize leading-tight"><?php echo htmlspecialchars($item['name'] ?? $item['type']); ?></h3>

                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Harga Satuan</p>
                            <span class="text-xl font-black text-brand-primary">Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-8">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-light/50 rounded-lg text-[10px] font-black text-brand-primary border border-brand-primary/10">
                            <i class="fas fa-weight-hanging opacity-60"></i> <?php echo htmlspecialchars($item['weight']); ?> KG
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 rounded-lg text-[10px] font-black text-blue-600 border border-blue-100">
                            <i class="fas fa-paw opacity-60"></i> <?php echo htmlspecialchars($item['breed']); ?>
                        </div>
                    </div>
                    
                    <a href="/lautan-ternak-pantura/livestock/detail/<?php echo htmlspecialchars($item['id']); ?>" class="w-full inline-flex justify-center items-center gap-3 bg-brand-primary text-white py-4 px-6 rounded-2xl hover:bg-brand-dark transition-all font-black text-sm uppercase tracking-widest shadow-lg shadow-brand-primary/20 group/btn overflow-hidden relative">
                        <span class="relative z-10 flex items-center gap-2">
                            Lihat Detail <i class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1 transition-transform"></i>
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($livestocks)): ?>
                <div class="col-span-full text-center py-24 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                        <i class="fas fa-search text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Hewan Tidak Ditemukan</h3>
                    <p class="text-gray-500">Coba ubah kriteria filter Anda untuk menemukan hewan kurban lainnya.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
