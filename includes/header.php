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
    <link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Toast Container */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
    </style>
    <script>
        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            
            let bgColor = 'bg-[#0f965d]'; 
            let icon = 'fa-check';
            let title = 'BERHASIL!';
            
            if (type === 'error') {
                bgColor = 'bg-[#dc2626]'; 
                icon = 'fa-xmark';
                title = 'GAGAL!';
            } else if (type === 'warning') {
                bgColor = 'bg-[#f59e0b]'; 
                icon = 'fa-triangle-exclamation';
                title = 'PERINGATAN!';
            } else if (type === 'info') {
                bgColor = 'bg-[#3b82f6]'; 
                icon = 'fa-info';
                title = 'INFO!';
            }
            
            toast.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-5 min-w-[320px] max-w-[420px] transition-all duration-500 transform translate-x-10 opacity-0 pointer-events-auto`;
            
            toast.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas ${icon} text-sm text-white"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1 text-white/90">${title}</p>
                    <p class="text-sm font-bold leading-tight text-white">${message}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white transition-colors shrink-0">
                    <i class="fas fa-times text-lg"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('translate-x-10', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                const msg = urlParams.get('success');
                const messages = {
                    '1': 'Profil Anda berhasil diperbarui!',
                    'verified': 'Pembayaran berhasil diverifikasi!',
                    'updated': 'Status transaksi berhasil diperbarui!',
                    'profile_updated': 'Profil Anda berhasil diperbarui!',
                    'added': 'Data berhasil ditambahkan!',
                    'deleted': 'Data berhasil dihapus!',
                    'plan_created': 'Rencana tabungan Anda telah berhasil dibuat! Mulai menabung sekarang.',
                    'payment_sent': 'Konfirmasi pembayaran berhasil dikirim!'
                };
                const displayMsg = messages[msg] || msg || 'Aksi berhasil dilakukan!';
                showToast(displayMsg, 'success');
                
                // Clean URL
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
            if (urlParams.has('error')) {
                const msg = urlParams.get('error');
                const errors = {
                    'invalid_data': 'Data yang dikirimkan tidak valid!',
                    'upload_failed': 'Unggah file bukti transfer gagal!',
                    'nama_sohibul_qurban_wajib_diisi': 'Nama Sohibul Qurban wajib diisi!'
                };
                const displayMsg = errors[msg] || msg || 'Terjadi kesalahan!';
                showToast(displayMsg, 'error');
                
                // Clean URL
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
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
                        <a href="/lautan-ternak-pantura/index" id="nav-beranda"
                            class="<?php echo $is_home && strpos($current_uri, '#') === false ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> nav-link inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Beranda</a>
                        <a href="/lautan-ternak-pantura/index#keunggulan" id="nav-keunggulan"
                            class="nav-link border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Keunggulan</a>
                        <a href="/lautan-ternak-pantura/index#alur" id="nav-alur"
                            class="nav-link border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Cara
                            Kerja</a>
                        <a href="/lautan-ternak-pantura/index#testimoni" id="nav-testimoni"
                            class="nav-link border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Testimoni</a>
                        <a href="/lautan-ternak-pantura/index#galeri" id="nav-galeri"
                            class="nav-link border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Galeri</a>
                        <a href="/lautan-ternak-pantura/marketplace" id="nav-marketplace"
                            class="<?php echo strpos($current_uri, 'marketplace') !== false ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> nav-link inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Katalog
                            Hewan</a>
                        <a href="/lautan-ternak-pantura/tabungan" id="nav-tabungan"
                            class="<?php echo strpos($current_uri, 'tabungan') !== false ? 'border-brand-primary text-brand-primary' : 'border-transparent text-gray-500 hover:border-brand-primary hover:text-gray-700'; ?> nav-link inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">Tabungan
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
                    class="<?php echo $is_home ? 'bg-brand-light text-brand-primary' : 'text-gray-600 hover:bg-brand-light hover:text-brand-primary'; ?> block px-3 py-2 rounded-md text-base font-medium mobile-nav-link" data-nav="beranda">Beranda</a>
                <a href="/lautan-ternak-pantura/index#keunggulan"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium mobile-nav-link" data-nav="keunggulan">Keunggulan</a>
                <a href="/lautan-ternak-pantura/index#alur"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium mobile-nav-link" data-nav="alur">Cara
                    Kerja</a>
                <a href="/lautan-ternak-pantura/index#testimoni"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium mobile-nav-link" data-nav="testimoni">Testimoni</a>
                <a href="/lautan-ternak-pantura/index#galeri"
                    class="text-gray-600 hover:bg-brand-light hover:text-brand-primary block px-3 py-2 rounded-md text-base font-medium mobile-nav-link" data-nav="galeri">Galeri</a>
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

    <script>
        // JS for handling active states for section anchors
        document.addEventListener('DOMContentLoaded', function() {
            const sections = ['keunggulan', 'alur', 'testimoni', 'galeri'];
            const navLinks = {
                'beranda': document.getElementById('nav-beranda'),
                'keunggulan': document.getElementById('nav-keunggulan'),
                'alur': document.getElementById('nav-alur'),
                'testimoni': document.getElementById('nav-testimoni'),
                'galeri': document.getElementById('nav-galeri')
            };
            
            const mobileLinks = document.querySelectorAll('.mobile-nav-link');

            function updateActiveLink() {
                const scrollPos = window.scrollY + 100;
                let activeSection = null;

                // Check if we are on the index page
                if (window.location.pathname.includes('index') || window.location.pathname === '/lautan-ternak-pantura/') {
                    for (const sectionId of sections) {
                        const el = document.getElementById(sectionId);
                        if (el && scrollPos >= el.offsetTop && scrollPos < el.offsetTop + el.offsetHeight) {
                            activeSection = sectionId;
                            break;
                        }
                    }

                    if (!activeSection && scrollPos < 500) {
                        activeSection = 'beranda';
                    }

                    // Update Desktop Links
                    Object.keys(navLinks).forEach(key => {
                        if (navLinks[key]) {
                            if (key === activeSection) {
                                navLinks[key].classList.remove('border-transparent', 'text-gray-500');
                                navLinks[key].classList.add('border-brand-primary', 'text-brand-primary');
                            } else {
                                navLinks[key].classList.add('border-transparent', 'text-gray-500');
                                navLinks[key].classList.remove('border-brand-primary', 'text-brand-primary');
                            }
                        }
                    });

                    // Update Mobile Links
                    mobileLinks.forEach(link => {
                        const navKey = link.getAttribute('data-nav');
                        if (navKey === activeSection) {
                            link.classList.add('bg-brand-light', 'text-brand-primary');
                            link.classList.remove('text-gray-600');
                        } else {
                            link.classList.remove('bg-brand-light', 'text-brand-primary');
                            link.classList.add('text-gray-600');
                        }
                    });
                }
            }

            window.addEventListener('scroll', updateActiveLink);
            updateActiveLink(); // Initial call
        });
    </script>

    <main class="flex-grow">