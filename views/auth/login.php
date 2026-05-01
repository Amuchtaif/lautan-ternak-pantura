<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk / Daftar - Lautan Ternak Pantura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#0d5bb5',
                            secondary: '#00a3e0',
                            light: '#f8fafc',
                            dark: '#0a4286',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .form-transition { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center md:p-4 overflow-x-hidden">

    <!-- Main Wrapper - Reduced width and height -->
    <div id="main-container" class="relative w-full max-w-[950px] min-h-screen md:min-h-[620px] bg-white md:rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row-reverse">
        
        <!-- Form Section -->
        <div id="form-side" class="w-full md:w-1/2 flex flex-col justify-center px-8 sm:px-12 lg:px-14 py-12 md:py-8 bg-white z-20 form-transition">
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-sm font-bold flex items-center gap-3 animate-pulse">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Login Content -->
            <div id="login-content" class="form-transition opacity-100 scale-100">
                <div class="mb-8 flex items-center gap-3">
                    <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <div class="leading-tight">
                        <span class="text-xl font-black text-brand-primary tracking-tighter uppercase block">Lautan Ternak</span>
                        <span class="text-brand-secondary text-xs font-bold tracking-widest uppercase block -mt-1">Pantura</span>
                    </div>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-1 tracking-tight">Selamat Datang</h1>
                <p class="text-gray-400 mb-8 font-medium text-sm">Lanjutkan niat ibadah Anda bersama kami.</p>
                
                <form action="/lautan-ternak-pantura/api/auth/login" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Alamat Email</label>
                        <div class="relative group">
                            <i class="far fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-primary transition-colors text-lg"></i>
                            <input type="email" name="email" placeholder="contoh@email.com" required
                                class="w-full pl-14 pr-5 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold text-base">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Kata Sandi</label>
                        <div class="relative group">
                            <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-primary transition-colors text-lg"></i>
                            <input type="password" id="login-password" name="password" placeholder="••••••••" required
                                class="w-full pl-14 pr-14 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold text-base">
                            <button type="button" onclick="togglePassword('login-password')" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-primary transition-colors px-1">
                                <i class="far fa-eye text-lg" id="eye-login-password"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-gray-200 transition-all checked:bg-brand-primary checked:border-brand-primary">
                                <i class="fas fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"></i>
                            </div>
                            <span class="text-gray-500 font-bold group-hover:text-gray-700 transition">Ingat saya</span>
                        </label>
                        <a href="#" class="text-brand-primary font-black hover:text-brand-dark transition-colors">Lupa Kata Sandi?</a>
                    </div>
                    
                    <button type="submit" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-lg shadow-xl shadow-brand-primary/20 hover:bg-brand-dark hover:-translate-y-0.5 transition-all active:scale-[0.98] mt-2">
                        Masuk Sekarang
                    </button>
                    
                    <div class="text-center pt-2">
                        <p class="text-gray-400 font-bold text-sm">
                            Belum punya akun? 
                            <button type="button" onclick="toggleAuth()" class="text-brand-primary hover:underline ml-1 font-black">Daftar Akun Baru</button>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Register Content -->
            <div id="register-content" class="form-transition hidden opacity-0 scale-95">
                <div class="mb-6 flex items-center gap-3">
                    <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <div class="leading-tight">
                        <span class="text-xl font-black text-brand-primary tracking-tighter uppercase block">Lautan Ternak</span>
                        <span class="text-brand-secondary text-xs font-bold tracking-widest uppercase block -mt-1">Pantura</span>
                    </div>
                </div>
                
                <h1 class="text-3xl font-black text-gray-900 mb-1 tracking-tight">Buat Akun</h1>
                <p class="text-gray-400 mb-6 font-medium text-sm">Mulai perjalanan kurban Anda bersama kami.</p>
                
                <form action="/lautan-ternak-pantura/api/auth/register" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" placeholder="Nama Anda" required
                                class="w-full px-5 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Daftar Sebagai</label>
                            <select name="role" class="w-full px-5 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-black cursor-pointer">
                                <option value="customer">Pembeli / Pekurban</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Alamat Email</label>
                        <input type="email" name="email" placeholder="email@contoh.com" required
                            class="w-full px-5 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Kata Sandi Baru</label>
                        <div class="relative">
                            <input type="password" id="register-password" name="password" placeholder="Minimal 8 karakter" required
                                class="w-full px-5 pr-14 py-5 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold">
                            <button type="button" onclick="togglePassword('register-password')" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-primary transition-colors px-1">
                                <i class="far fa-eye text-lg" id="eye-register-password"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-lg shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all transform active:scale-95 mt-4">
                        Daftar Sekarang
                    </button>
                    
                    <div class="text-center pt-4">
                        <p class="text-gray-400 font-bold text-sm">
                            Sudah punya akun? 
                            <button type="button" onclick="toggleAuth()" class="text-brand-primary hover:underline ml-1 font-black">Masuk Sekarang</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Section -->
        <div id="info-side" class="hidden md:flex w-1/2 h-full bg-brand-light relative overflow-hidden flex-col items-center justify-center p-12 form-transition">
            <!-- Decorative Elements -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand-secondary/5 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 text-center flex flex-col items-center">
                <div class="mb-10 transform hover:rotate-3 transition-transform duration-700">
                    <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Branding" class="w-60 h-auto filter drop-shadow-[0_20px_50px_rgba(13,91,181,0.2)]">
                </div>
                
                <h2 class="text-3xl font-black text-gray-900 mb-10 tracking-tight">Lautan Ternak Pantura</h2>
                
                <div class="glass-card p-10 rounded-[2.5rem] shadow-2xl max-w-sm text-left relative overflow-hidden border border-white/50">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i class="fas fa-shield-alt text-8xl text-brand-primary"></i>
                    </div>
                    <i class="fas fa-quote-left text-brand-primary/20 text-6xl absolute -top-1 -left-1"></i>
                    <p class="text-2xl font-extrabold text-brand-primary leading-[1.1] relative z-10 mb-6 italic tracking-tighter">
                        "Amanah dalam Setiap Kurban"
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-1.5 bg-brand-secondary rounded-full"></div>
                        <p class="text-gray-500 font-bold text-xs tracking-wide">Menjaga tradisi dengan kasih dan martabat.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const initialAction = urlParams.get('action');
        let isLogin = initialAction !== 'register';

        const mainContainer = document.getElementById('main-container');
        const loginContent = document.getElementById('login-content');
        const registerContent = document.getElementById('register-content');

        // Apply Initial State
        if (!isLogin) {
            applyState(false);
        }

        function toggleAuth() {
            isLogin = !isLogin;
            const activeContent = isLogin ? registerContent : loginContent;
            const targetContent = isLogin ? loginContent : registerContent;

            activeContent.classList.add('opacity-0', 'scale-95');
            
            setTimeout(() => {
                activeContent.classList.add('hidden');
                targetContent.classList.remove('hidden');
                
                applyState(isLogin);
                
                setTimeout(() => {
                    targetContent.classList.remove('opacity-0', 'scale-95');
                    targetContent.classList.add('opacity-100', 'scale-100');
                }, 50);
            }, 400);
        }

        function applyState(loginMode) {
            if (window.innerWidth >= 768) {
                mainContainer.style.flexDirection = loginMode ? 'row-reverse' : 'row';
            } else {
                mainContainer.style.flexDirection = 'column';
            }
        }

        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById('eye-' + inputId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        window.addEventListener('resize', () => applyState(isLogin));
    </script>
</body>
</html>
