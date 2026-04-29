<?php 
require_once 'config/database.php';
require_once 'includes/header.php'; 

// Fetch livestock data
$livestocks = [];
if (isset($conn)) {
    $query = "SELECT l.*, u.name as breeder_name FROM livestock l JOIN users u ON l.breeder_id = u.id WHERE l.status = 'available'";

    // Apply filters if any
    $filter_type = isset($_GET['type']) ? $_GET['type'] : '';
    $filter_category = isset($_GET['category']) ? $_GET['category'] : '';

    if($filter_type) $query .= " AND l.type = :type";
    if($filter_category) $query .= " AND l.category = :category";

    $stmt = $conn->prepare($query);
    if($filter_type) $stmt->bindParam(':type', $filter_type);
    if($filter_category) $stmt->bindParam(':category', $filter_category);
    $stmt->execute();
    $livestocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Marketplace Hewan</h1>
                <p class="mt-2 text-sm text-gray-600">Pilih hewan qurban atau aqiqah terbaik langsung dari peternak.</p>
            </div>
            
            <!-- Filters -->
            <form class="mt-4 md:mt-0 flex gap-4" method="GET" action="marketplace.php">
                <select name="type" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-green focus:border-brand-green sm:text-sm rounded-md shadow-sm border">
                    <option value="">Semua Hewan</option>
                    <option value="sapi" <?php echo (isset($filter_type) && $filter_type == 'sapi') ? 'selected' : ''; ?>>Sapi</option>
                    <option value="kambing" <?php echo (isset($filter_type) && $filter_type == 'kambing') ? 'selected' : ''; ?>>Kambing</option>
                </select>
                <select name="category" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-green focus:border-brand-green sm:text-sm rounded-md shadow-sm border">
                    <option value="">Semua Kategori</option>
                    <option value="qurban" <?php echo (isset($filter_category) && $filter_category == 'qurban') ? 'selected' : ''; ?>>Qurban</option>
                    <option value="aqiqah" <?php echo (isset($filter_category) && $filter_category == 'aqiqah') ? 'selected' : ''; ?>>Aqiqah</option>
                </select>
                <button type="submit" class="bg-brand-green text-white px-4 py-2 rounded-md hover:bg-brand-dark transition shadow-sm text-sm font-medium">Filter</button>
            </form>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($livestocks as $item): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition flex flex-col">
                <div class="relative h-48">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Hewan" class="w-full h-full object-cover">
                    <div class="absolute top-2 right-2 bg-brand-green text-white text-xs font-bold px-2 py-1 rounded shadow">
                        <?php echo ucfirst(htmlspecialchars($item['category'])); ?>
                    </div>
                </div>
                <div class="p-5 flex-grow flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-semibold text-gray-900 capitalize"><?php echo htmlspecialchars($item['type']); ?></h3>
                        <span class="text-lg font-bold text-brand-brown">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4"><i class="fas fa-user-alt mr-1 text-gray-400"></i> <?php echo htmlspecialchars($item['breeder_name']); ?></p>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <div><span class="text-gray-500 block text-xs uppercase tracking-wider">Berat</span><span class="font-medium text-gray-900"><?php echo htmlspecialchars($item['weight']); ?> kg</span></div>
                        <div><span class="text-gray-500 block text-xs uppercase tracking-wider">Umur</span><span class="font-medium text-gray-900"><?php echo htmlspecialchars($item['age']); ?> bln</span></div>
                    </div>
                    
                    <p class="text-sm text-gray-600 line-clamp-2 flex-grow mb-4">
                        <i class="fas fa-heartbeat text-brand-green mr-1"></i> <?php echo htmlspecialchars($item['health_condition']); ?>
                    </p>
                    
                    <a href="#" class="w-full text-center block bg-brand-green text-white py-2 rounded-md hover:bg-brand-dark transition font-medium shadow-sm">Beli / Booking</a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($livestocks)): ?>
                <div class="col-span-1 sm:grid-cols-2 lg:col-span-3 text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                    <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada hewan yang tersedia untuk kriteria ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
