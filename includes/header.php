<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lautan Ternak Pantura - Qurban & Aqiqah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#15803d',
                            light: '#dcfce7',
                            dark: '#166534',
                            brown: '#854d0e',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/lautan-ternak-pantura/index.php" class="flex-shrink-0 flex items-center text-brand-green font-bold text-xl gap-2">
                        <i class="fas fa-leaf"></i>
                        LTP
                    </a>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/lautan-ternak-pantura/index.php" class="border-transparent text-gray-500 hover:border-brand-green hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Beranda</a>
                        <a href="/lautan-ternak-pantura/marketplace.php" class="border-transparent text-gray-500 hover:border-brand-green hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Marketplace</a>
                        <a href="/lautan-ternak-pantura/tabungan.php" class="border-transparent text-gray-500 hover:border-brand-green hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Tabungan Qurban</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php 
                            $dashboard_url = '/lautan-ternak-pantura/views/' . $_SESSION['role'] . '/dashboard.php';
                        ?>
                        <a href="<?php echo $dashboard_url; ?>" class="text-gray-500 hover:text-brand-green font-medium text-sm transition">Dashboard</a>
                        <a href="/lautan-ternak-pantura/api/auth/logout.php" class="text-red-500 hover:text-red-700 font-medium text-sm transition">Logout</a>
                    <?php else: ?>
                        <a href="/lautan-ternak-pantura/views/auth/login.php" class="text-brand-green font-medium hover:text-brand-dark transition">Masuk</a>
                        <a href="/lautan-ternak-pantura/views/auth/register.php" class="bg-brand-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition shadow-md">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow">
