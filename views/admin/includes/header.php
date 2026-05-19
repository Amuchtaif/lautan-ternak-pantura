<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin - Lautan Ternak Pantura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#0d5bb5',
                            secondary: '#00a3e0',
                            light: '#e0f2fe',
                            dark: '#0a4286',
                            accent: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

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
                    'payment_sent': 'Konfirmasi pembayaran berhasil dikirim!',
                    'purchase': 'Pembelian stok berhasil dicatat!'
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
<body class="bg-gray-50 flex min-h-screen">
