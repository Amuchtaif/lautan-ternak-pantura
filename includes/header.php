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

        /* Sleek Mobile Sidebar styling */
        .mobile-sidebar-link {
            border-left: 4px solid transparent;
            transition: all 0.2s ease-in-out;
        }
        
        .mobile-sidebar-link.active {
            background: linear-gradient(90deg, rgba(13, 91, 181, 0.08) 0%, rgba(13, 91, 181, 0) 100%) !important;
            color: #0d5bb5 !important;
            border-left-color: #0d5bb5 !important;
            font-weight: 800 !important;
            padding-left: 1.5rem !important;
        }
        
        .mobile-sidebar-link.active i {
            color: #0d5bb5 !important;
            transform: scale(1.15);
        }

        .mobile-sidebar-link:not(.active):hover {
            border-left-color: rgba(13, 91, 181, 0.3) !important;
            background-color: rgba(13, 91, 181, 0.02) !important;
            color: #0d5bb5 !important;
            padding-left: 1.25rem;
        }

        .mobile-sidebar-link:hover i {
            transform: scale(1.1);
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
                    'nama_sohibul_qurban_wajib_diisi': 'Nama Sohibul Qurban wajib diisi!',
                    'username_taken': 'Username sudah digunakan!',
                    'username_invalid': 'Username minimal 4 karakter dan hanya boleh huruf, angka, titik, strip, atau underscore!',
                    'password_weak': 'Password minimal 8 karakter serta mengandung huruf dan angka!',
                    'password_mismatch': 'Konfirmasi password tidak cocok!',
                    'email_taken': 'Email sudah digunakan!',
                    'email_invalid': 'Format email tidak valid!',
                    'livestock_unavailable': 'Hewan target tidak tersedia!'
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
    <nav id="main-navbar" class="bg-white sticky top-0 z-50 transition-all duration-300 border-b border-transparent">
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
                        $dashboard_url = $_SESSION['role'] === 'customer' ? '/lautan-ternak-pantura/customer/dashboard' : '/lautan-ternak-pantura/views/' . $_SESSION['role'] . '/dashboard';
                        ?>
                        <a href="<?php echo $dashboard_url; ?>"
                            class="hidden md:inline-flex text-gray-500 hover:text-brand-primary font-medium text-sm transition">Dashboard</a>
                        <a href="/lautan-ternak-pantura/api/auth/logout"
                            class="hidden md:inline-flex text-red-500 hover:text-red-700 font-medium text-sm transition">Logout</a>
                    <?php else: ?>
                        <a href="/lautan-ternak-pantura/auth/login"
                            class="hidden md:inline-flex items-center gap-2 bg-brand-primary/10 text-brand-primary px-4 py-2 rounded-full text-sm font-semibold hover:bg-brand-primary hover:text-white transition-all shadow-sm border border-brand-primary/20">
                            <i class="fas fa-user-circle text-lg"></i> Masuk / Daftar
                        </a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button type="button"
                            onclick="toggleMobileMenu()"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-brand-primary hover:bg-gray-100 focus:outline-none transition">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Sidebar Backdrop -->
        <div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300 md:hidden" onclick="toggleMobileMenu()"></div>

        <!-- Mobile Sidebar Drawer -->
        <aside id="mobile-sidebar" class="w-72 bg-white flex flex-col fixed left-0 top-0 h-screen z-50 transition-all duration-300 transform -translate-x-full md:hidden shadow-2xl">
            <!-- Close Button -->
            <button onclick="toggleMobileMenu()" class="w-8 h-8 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center absolute top-6 right-6">
                <i class="fas fa-times"></i>
            </button>

            <!-- Brand Header -->
            <div class="p-6 border-b border-gray-100 shrink-0">
                <a href="/lautan-ternak-pantura/index" class="flex items-center gap-3">
                    <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <div class="leading-tight">
                        <span class="text-lg font-black text-brand-primary tracking-tighter uppercase block">LTP</span>
                        <span class="text-brand-secondary text-[9px] font-bold tracking-widest uppercase block -mt-1">Lautan Ternak</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-grow p-4 space-y-1 overflow-y-auto no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
                <p class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Menu Utama</p>
                
                <a href="/lautan-ternak-pantura/index" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500 <?php echo $is_home && strpos($current_uri, '#') === false ? 'active' : ''; ?>" data-nav="beranda">
                    <i class="fas fa-home text-lg w-6 text-center"></i>
                    <span>Beranda</span>
                </a>
                <a href="/lautan-ternak-pantura/index#keunggulan" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500" data-nav="keunggulan">
                    <i class="fas fa-star text-lg w-6 text-center"></i>
                    <span>Keunggulan</span>
                </a>
                <a href="/lautan-ternak-pantura/index#alur" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500" data-nav="alur">
                    <i class="fas fa-route text-lg w-6 text-center"></i>
                    <span>Cara Kerja</span>
                </a>
                <a href="/lautan-ternak-pantura/index#testimoni" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500" data-nav="testimoni">
                    <i class="fas fa-comments text-lg w-6 text-center"></i>
                    <span>Testimoni</span>
                </a>
                <a href="/lautan-ternak-pantura/index#galeri" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500" data-nav="galeri">
                    <i class="fas fa-images text-lg w-6 text-center"></i>
                    <span>Galeri</span>
                </a>
                <a href="/lautan-ternak-pantura/marketplace" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500 <?php echo strpos($current_uri, 'marketplace') !== false ? 'active' : ''; ?>" data-nav="marketplace">
                    <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
                    <span>Katalog Hewan</span>
                </a>
                <a href="/lautan-ternak-pantura/tabungan" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500 <?php echo strpos($current_uri, 'tabungan') !== false ? 'active' : ''; ?>" data-nav="tabungan">
                    <i class="fas fa-gem text-lg w-6 text-center"></i>
                    <span>Tabungan Qurban</span>
                </a>

                <div class="my-4 border-t border-gray-100"></div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $dashboard_url = $_SESSION['role'] === 'customer' ? '/lautan-ternak-pantura/customer/dashboard' : '/lautan-ternak-pantura/views/' . $_SESSION['role'] . '/dashboard';
                    ?>
                    <a href="<?php echo $dashboard_url; ?>" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-gray-500">
                        <i class="fas fa-th-large text-lg w-6 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/lautan-ternak-pantura/api/auth/logout" class="mobile-sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg font-bold text-sm transition-all text-red-500 hover:bg-red-50">
                        <i class="fas fa-right-from-bracket text-lg w-6 text-center"></i>
                        <span>Keluar Sistem</span>
                    </a>
                <?php else: ?>
                    <a href="/lautan-ternak-pantura/auth/login" class="flex items-center gap-4 px-4 py-3 rounded-xl font-bold text-sm transition-all bg-brand-primary text-white hover:bg-brand-dark justify-center shadow-md mx-2">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span>Masuk / Daftar</span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>
    </nav>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('mobile-sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
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
            
            const mobileLinks = document.querySelectorAll('.mobile-sidebar-link');
            const navbar = document.getElementById('main-navbar');

            function updateActiveLink() {
                const scrollPos = window.scrollY;
                const scrollPosWithOffset = scrollPos + 100;
                let activeSection = null;

                if (scrollPos > 10) {
                    navbar.classList.add('bg-white/80', 'backdrop-blur-md', 'shadow-sm', 'border-gray-100');
                    navbar.classList.remove('bg-white', 'border-transparent');
                } else {
                    navbar.classList.add('bg-white', 'border-transparent');
                    navbar.classList.remove('bg-white/80', 'backdrop-blur-md', 'shadow-sm', 'border-gray-100');
                }

                // Check if we are on the index page
                if (window.location.pathname.includes('index') || window.location.pathname === '/lautan-ternak-pantura/') {
                    for (const sectionId of sections) {
                        const el = document.getElementById(sectionId);
                        if (el && scrollPosWithOffset >= el.offsetTop && scrollPosWithOffset < el.offsetTop + el.offsetHeight) {
                            activeSection = sectionId;
                            break;
                        }
                    }

                    if (!activeSection && scrollPosWithOffset < 500) {
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
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }
            }

            window.addEventListener('scroll', updateActiveLink);
            updateActiveLink(); // Initial call
        });
    </script>

    <main class="flex-grow">
