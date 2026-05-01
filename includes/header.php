<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
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
                            primary: '#0d5bb5', /* Dark Blue from Logo Text */
                            secondary: '#00a3e0', /* Vibrant Cyan from Logo */
                            light: '#e0f2fe', /* Sky blue light */
                            dark: '#0a4286', /* Deeper Blue for hovers */
                            accent: '#f59e0b', /* Yellow/Orange from cow */
                            green: '#22c55e', /* Legacy green support */
                        }
                    }
                }
            }
        }
    </script>
    <link rel="icon" type="image/ico" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/lautan-ternak-pantura/index" class="flex-shrink-0 flex items-center py-2">
                        <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Lautan Ternak Pantura"
                            class="h-12 w-auto object-contain">
                    </a>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <?php
                        $current_uri = $_SERVER['REQUEST_URI'];
                        $is_home = (strpos($current_uri, 'index') !== false || $current_uri == '/lautan-ternak-pantura/') ? true : false;
                        ?>
                        <a href="/lautan-ternak-pantura/index"
                            class="<?php echo $is_home ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Beranda</a>
                        <a href="/lautan-ternak-pantura/index#keunggulan"
                            class="border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Keunggulan</a>
                        <a href="/lautan-ternak-pantura/index#alur"
                            class="border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Cara
                            Kerja</a>
                        <a href="/lautan-ternak-pantura/index#testimoni"
                            class="border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Testimoni</a>
                        <a href="/lautan-ternak-pantura/index#galeri"
                            class="border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Galeri</a>
                        <a href="/lautan-ternak-pantura/marketplace"
                            class="<?php echo strpos($current_uri, 'marketplace') !== false ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Katalog
                            Hewan</a>
                        <a href="/lautan-ternak-pantura/tabungan"
                            class="<?php echo strpos($current_uri, 'tabungan') !== false ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Tabungan
                            Qurban</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $dashboard_url = '/lautan-ternak-pantura/views/' . $_SESSION['role'] . '/dashboard.php';
                        ?>
                        <a href="<?php echo $dashboard_url; ?>"
                            class="hidden md:inline-flex text-gray-500 hover:text-brand-primary font-medium text-sm transition">Dashboard</a>
                        <a href="/lautan-ternak-pantura/api/auth/logout"
                            class="hidden md:inline-flex text-red-500 hover:text-red-700 font-medium text-sm transition">Logout</a>
                    <?php else: ?>
                        <a href="/lautan-ternak-pantura/views/auth/login"
                            class="hidden md:inline-flex items-center gap-2 bg-brand-primary/10 text-brand-primary px-4 py-2 rounded-full text-sm font-semibold hover:bg-brand-primary hover:text-white transition-all shadow-sm border border-brand-primary/20">
                            <i class="fas fa-user-circle text-lg"></i> Masuk / Daftar
                        </a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button type="button"
                            onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-brand-primary hover:bg-gray-100 focus:outline-none transition">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="md:hidden hidden bg-white border-t border-gray-100" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/lautan-ternak-pantura/index"
                    class="<?php echo $is_home ? 'bg-brand-light text-brand-primary' : 'text-gray-600 hover:bg-brand-light hover:text-brand-primary'; ?> block px-3 py-2 rounded-md text-base font-medium">Beranda</a>
                <a href="/lautan-ternak-pantura/index#keunggulan"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium">Keunggulan</a>
                <a href="/lautan-ternak-pantura/index#alur"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium">Cara
                    Kerja</a>
                <a href="/lautan-ternak-pantura/index#testimoni"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium">Testimoni</a>
                <a href="/lautan-ternak-pantura/index#galeri"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium">Galeri</a>
                <a href="/lautan-ternak-pantura/marketplace"
                    class="<?php echo strpos($current_uri, 'marketplace') !== false ? 'bg-brand-light text-brand-primary' : 'text-gray-600 hover:bg-brand-light hover:text-brand-primary'; ?> block px-3 py-2 rounded-md text-base font-medium">Katalog
                    Hewan</a>
                <a href="/lautan-ternak-pantura/tabungan"
                    class="<?php echo strpos($current_uri, 'tabungan') !== false ? 'bg-brand-light text-brand-primary' : 'text-gray-600 hover:bg-brand-light hover:text-brand-primary'; ?> block px-3 py-2 rounded-md text-base font-medium">Tabungan
                    Qurban</a>
                <hr class="border-gray-100 my-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $dashboard_url; ?>"
                        class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                    <a href="/lautan-ternak-pantura/api/auth/logout"
                        class="text-red-600 hover:bg-red-50 hover:text-red-700 block px-3 py-2 rounded-md text-base font-medium">Logout</a>
                <?php else: ?>
                    <a href="/lautan-ternak-pantura/views/auth/login"
                        class="bg-brand-primary text-white block px-3 py-4 rounded-xl text-center text-base font-bold shadow-md mx-2">Masuk
                        / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="flex-grow">