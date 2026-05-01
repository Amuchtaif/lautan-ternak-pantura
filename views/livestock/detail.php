<?php require_once 'includes/header.php'; ?>

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
                    <img src="<?php echo htmlspecialchars($livestock['image_url']); ?>" alt="Hewan" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute top-4 left-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-sm text-brand-primary text-xs font-bold shadow-sm uppercase tracking-wider">
                            <?php echo htmlspecialchars($livestock['category']); ?>
                        </span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-8 sm:p-10 lg:p-12 flex flex-col justify-center">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 capitalize">
                                <?php echo htmlspecialchars($livestock['type']); ?>
                            </h1>
                            <span class="inline-flex items-center text-sm font-medium px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <i class="fas fa-check-circle mr-1.5"></i> <?php echo ucfirst(htmlspecialchars($livestock['status'])); ?>
                            </span>
                        </div>
                        <p class="text-3xl font-bold text-brand-primary mt-4">
                            Rp <?php echo number_format($livestock['price'], 0, ',', '.'); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-brand-secondary text-xl">
                                <i class="fas fa-weight-hanging"></i>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs uppercase tracking-wider mb-0.5">Berat</span>
                                <span class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($livestock['weight']); ?> kg</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-brand-accent text-xl">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-xs uppercase tracking-wider mb-0.5">Umur</span>
                                <span class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($livestock['age']); ?> bln</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 mb-10">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Kondisi Kesehatan</h3>
                            <div class="flex items-start gap-3 text-gray-600 bg-emerald-50/50 p-4 rounded-xl border border-emerald-50">
                                <i class="fas fa-heartbeat text-emerald-500 mt-1"></i>
                                <p class="leading-relaxed"><?php echo htmlspecialchars($livestock['health_condition']); ?></p>
                            </div>
                        </div>
                        
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                        <button onclick="alert('Fitur Pembelian Langsung sedang dikembangkan.')" class="flex-1 inline-flex justify-center items-center gap-2 bg-brand-primary text-white py-4 px-6 rounded-xl hover:bg-brand-dark transition-colors font-bold shadow-md hover:shadow-lg">
                            <i class="fas fa-shopping-cart"></i> Beli Langsung
                        </button>
                        <a href="/lautan-ternak-pantura/tabungan?livestock_id=<?php echo $livestock['id']; ?>" class="flex-1 inline-flex justify-center items-center gap-2 bg-white text-brand-primary border-2 border-brand-primary py-4 px-6 rounded-xl hover:bg-brand-light/20 transition-colors font-bold">
                            <i class="fas fa-piggy-bank"></i> Program Tabungan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
