<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Back -->
        <div class="mb-8">
            <a href="/lautan-ternak-pantura/marketplace" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-brand-primary transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8">
                
                <!-- Image Gallery -->
                <div class="relative h-96 lg:h-full min-h-[400px]">
                    <img src="<?php echo htmlspecialchars($livestock['image'] ?? $livestock['image_url']); ?>" alt="Hewan" class="absolute inset-0 w-full h-full object-cover">
                </div>

                <!-- Product Info -->
                <div class="p-8 sm:p-10 lg:p-12 flex flex-col justify-center">
                    <div class="mb-6">
                        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 capitalize leading-tight">
                            <?php echo htmlspecialchars($livestock['name'] ?? $livestock['type']); ?>
                        </h1>
                        <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest"><?php echo htmlspecialchars($livestock['breed']); ?></p>
                        
                        <p class="text-3xl font-black text-brand-primary mt-6">
                            Rp <?php echo number_format($livestock['price'], 0, ',', '.'); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-2xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-weight-hanging"></i>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[9px] font-black uppercase tracking-wider mb-0.5">Berat</span>
                                <span class="font-black text-gray-900 text-sm"><?php echo htmlspecialchars($livestock['weight']); ?> kg</span>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[9px] font-black uppercase tracking-wider mb-0.5">Usia</span>
                                <span class="font-black text-gray-900 text-sm"><?php echo htmlspecialchars($livestock['age']); ?> Bln</span>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl flex items-center gap-3 col-span-2 sm:col-span-1">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center text-lg shrink-0">
                                <i class="fas fa-venus-mars"></i>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[9px] font-black uppercase tracking-wider mb-0.5">Kelamin</span>
                                <span class="font-black text-gray-900 text-sm"><?php echo $livestock['gender'] === 'male' ? 'Jantan' : 'Betina'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 mb-10">
                        <div>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Deskripsi & Kondisi</h3>
                            <div class="text-sm font-bold text-gray-600 bg-gray-50/50 p-4 rounded-2xl border border-gray-100/50 leading-relaxed">
                                <p><?php echo htmlspecialchars($livestock['description'] ?? $livestock['health_condition'] ?? 'Sehat wal afiat, dirawat berkala oleh peternak profesional.'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                        <?php if ($livestock['status'] === 'available' && $livestock['stock'] > 0): ?>
                            <a href="/lautan-ternak-pantura/sales/checkout/<?php echo $livestock['id']; ?>" class="flex-1 inline-flex justify-center items-center gap-2 bg-brand-primary text-white py-4 px-6 rounded-2xl hover:bg-brand-dark transition-all font-black text-sm uppercase tracking-widest shadow-xl shadow-brand-primary/20">
                                <i class="fas fa-shopping-bag"></i> Beli Langsung
                            </a>
                        <?php else: ?>
                            <button disabled class="flex-1 inline-flex justify-center items-center gap-2 bg-gray-200 text-gray-400 py-4 px-6 rounded-2xl font-black text-sm uppercase tracking-widest cursor-not-allowed">
                                <i class="fas fa-shopping-bag"></i> Habis Terjual
                            </button>
                        <?php endif; ?>
                        
                        <a href="/lautan-ternak-pantura/tabungan#form-registrasi" class="flex-1 inline-flex justify-center items-center gap-2 bg-white text-brand-primary border-2 border-brand-primary py-4 px-6 rounded-2xl hover:bg-brand-light/20 transition-all font-black text-sm uppercase tracking-widest">
                            <i class="fas fa-piggy-bank"></i> Program Tabungan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
