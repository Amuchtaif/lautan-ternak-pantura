<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit();
}

// Fetch Users Data (exclude admin accounts)
try {
    $stmt = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
    $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Dashboard</title>
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
    <link rel="icon" type="image/ico" href="/lautan-ternak-pantura/assets/images/favicon.ico">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar-link.active { background-color: rgba(13, 91, 181, 0.1); color: #0d5bb5; border-right: 4px solid #0d5bb5; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <!-- Top Navigation -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-gray-100 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="lg:hidden text-gray-500 text-xl"><i class="fas fa-bars"></i></button>
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Kelola Pengguna</h2>
                    <p class="text-xs text-gray-400 font-bold">Manajemen akun pelanggan terdaftar</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-gray-900 leading-none"><?php echo $_SESSION['name']; ?></p>
                    <p class="text-[10px] font-bold text-brand-primary uppercase tracking-widest mt-1">Super Admin</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-brand-primary/10 flex items-center justify-center text-brand-primary border-2 border-brand-primary/20">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow">
            
            <!-- Notification Container -->
            <div id="notification-container" class="fixed top-8 right-8 z-[100] flex flex-col gap-3"></div>

            <!-- Actions Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="relative w-full sm:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Cari nama, email, atau role..." 
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-100 rounded-2xl outline-none focus:border-brand-primary transition-all text-sm font-medium shadow-sm">
                </div>
                <button onclick="openModal('add')" class="bg-brand-primary text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-brand-primary/20 hover:bg-brand-dark transition-all flex items-center gap-3">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna Baru
                </button>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-16">No</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Profil / Email</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Peran</th>
                                <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Terdaftar</th>
                                <th class="px-6 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($usersList)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-10 text-center text-gray-400 font-bold">
                                        Belum ada data pengguna.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usersList as $i => $user): ?>
                                <tr class="hover:bg-brand-light/20 transition-colors group" id="user-row-<?php echo $user['id']; ?>">
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-gray-400"><?php echo $i + 1; ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-brand-primary/10 flex items-center justify-center font-bold text-brand-primary text-sm">
                                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-gray-900 leading-none"><?php echo $user['name']; ?></p>
                                                <p class="text-[10px] font-bold text-gray-400 mt-1"><?php echo $user['email']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full bg-green-50 text-green-600 uppercase tracking-wider">
                                            Pelanggan
                                        </span>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-xs font-bold text-gray-400"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                                    </td>
                                    <td class="px-6 py-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit" class="w-9 h-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-brand-primary hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <button onclick="openResetModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" title="Reset Password" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-key text-xs"></i>
                                            </button>
                                            <button onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" title="Hapus" class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Edit User -->
    <div id="modal-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1000] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="modal-content" class="bg-white rounded-[2.5rem] w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 flex flex-col">
            <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 shrink-0">
                <h3 id="modal-title" class="text-xl font-black text-gray-900 tracking-tight">Edit Pengguna</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all"><i class="fas fa-xmark text-xl"></i></button>
            </div>
            <div class="flex-grow overflow-y-auto custom-scrollbar">
                <form id="user-form" class="p-10 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nama Lengkap</label>
                    <div class="relative group">
                        <i class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-primary transition-colors text-sm"></i>
                        <input type="text" name="name" id="edit-name" placeholder="Masukkan nama lengkap..." required 
                            class="w-full pl-12 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                    </div>
                </div>

                <div class="space-y-2 md:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Alamat Email</label>
                    <div class="relative group">
                        <i class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-primary transition-colors text-sm"></i>
                        <input type="email" name="email" id="edit-email" placeholder="email@contoh.com" required 
                            class="w-full pl-12 pr-6 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm">
                    </div>
                </div>

                <div class="space-y-2 md:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Peran Akses</label>
                    <div class="relative group">
                        <i class="fas fa-user-tag absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-primary transition-colors text-sm"></i>
                        <select name="role" id="edit-role" class="w-full pl-12 pr-12 py-4 bg-gray-50 border-2 border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none cursor-pointer">
                            <option value="customer">Pelanggan / Pekurban</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>

                <div class="md:col-span-2 pt-6 flex gap-4">
                    <button type="button" onclick="closeModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" id="submit-btn" class="flex-1 px-6 py-4 bg-brand-primary text-white rounded-2xl font-black text-sm hover:bg-brand-dark shadow-xl shadow-brand-primary/20 transition-all flex items-center justify-center gap-2">
                        <span>Simpan Data Pengguna</span>
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="delete-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="delete-content" class="bg-white rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 p-10 text-center">
            <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash text-red-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Hapus Data?</h3>
            <p id="delete-message" class="text-sm text-gray-400 font-bold mb-8">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.</p>
            <input type="hidden" id="delete-id">
            <div class="flex gap-4">
                <button onclick="closeDeleteModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button onclick="executeDelete()" id="delete-btn" class="flex-1 px-6 py-4 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 shadow-xl shadow-red-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> <span>Ya, Hapus</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div id="reset-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[1001] hidden items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div id="reset-content" class="bg-white rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl transition-all duration-300 scale-90 opacity-0 p-10 text-center">
            <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-key text-amber-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Reset Password?</h3>
            <p id="reset-message" class="text-sm text-gray-400 font-bold mb-3">Password akan direset ke default.</p>
            <div class="bg-amber-50 rounded-2xl px-5 py-3 mb-8 inline-block">
                <p class="text-xs font-black text-amber-600"><i class="fas fa-info-circle mr-2"></i>Password baru: <span class="font-mono">password123</span></p>
            </div>
            <input type="hidden" id="reset-id">
            <div class="flex gap-4">
                <button onclick="closeResetModal()" class="flex-1 px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all">Batal</button>
                <button onclick="executeReset()" id="reset-btn" class="flex-1 px-6 py-4 bg-amber-500 text-white rounded-2xl font-black text-sm hover:bg-amber-600 shadow-xl shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-key"></i> <span>Ya, Reset</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function showSuccessNotification(message) { createNotification('success', message); }
        function showErrorNotification(message) { createNotification('error', message); }

        function createNotification(type, message) {
            const container = document.getElementById('notification-container');
            const id = 'notif-' + Date.now();
            const bgColor = type === 'success' ? 'bg-[#0f965d]' : 'bg-[#dc2626]';
            const icon = type === 'success' ? 'fa-check' : 'fa-xmark';
            const title = type === 'success' ? 'BERHASIL!' : 'GAGAL!';

            const notification = document.createElement('div');
            notification.id = id;
            notification.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-5 min-w-[320px] transition-all duration-500 transform translate-x-10 opacity-0`;
            notification.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0"><i class="fas ${icon} text-sm"></i></div>
                <div class="flex-grow">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1">${title}</p>
                    <p class="text-sm font-bold">${message}</p>
                </div>
                <button onclick="removeNotification('${id}')" class="text-white/50 hover:text-white transition-colors"><i class="fas fa-xmark text-lg"></i></button>
            `;
            container.appendChild(notification);

            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('translate-x-10', 'opacity-0');
            }, 10);

            setTimeout(() => removeNotification(id), 4000);
        }

        function removeNotification(id) {
            const el = document.getElementById(id);
            if (el) { el.classList.add('opacity-0', 'translate-x-10'); setTimeout(() => el.remove(), 300); }
        }

        function openModal(type, data = null) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('user-form');
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            // Animation start
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
            
            if (type === 'edit' && data) {
                title.innerText = 'Edit Pengguna';
                document.getElementById('edit-id').value = data.id;
                document.getElementById('edit-name').value = data.name;
                document.getElementById('edit-email').value = data.email;
                document.getElementById('edit-role').value = data.role;
            } else {
                title.innerText = 'Tambah Pengguna Baru';
                form.reset();
                document.getElementById('edit-id').value = '';
            }
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function confirmDelete(id) {
            // This is now handled by openDeleteModal
        }

        function openDeleteModal(id, name) {
            document.getElementById('delete-id').value = id;
            document.getElementById('delete-message').innerText = `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak bisa dibatalkan.`;
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
        }

        function closeDeleteModal() {
            const overlay = document.getElementById('delete-overlay');
            const content = document.getElementById('delete-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function executeDelete() {
            const id = document.getElementById('delete-id').value;
            const btn = document.getElementById('delete-btn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menghapus...</span>';
            btn.disabled = true;
            try {
                const res = await fetch(`/lautan-ternak-pantura/api/admin/delete_user?id=${id}`);
                const data = await res.json();
                if (data.success) {
                    closeDeleteModal();
                    showSuccessNotification(data.message);
                    const row = document.getElementById(`user-row-${id}`);
                    row.classList.add('opacity-0', '-translate-x-4');
                    setTimeout(() => row.remove(), 300);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Gagal menghubungi server');
            }
            btn.innerHTML = '<i class="fas fa-trash"></i> <span>Ya, Hapus</span>';
            btn.disabled = false;
        }

        function openResetModal(id, name) {
            document.getElementById('reset-id').value = id;
            document.getElementById('reset-message').innerText = `Password untuk "${name}" akan direset ke default.`;
            const overlay = document.getElementById('reset-overlay');
            const content = document.getElementById('reset-content');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                content.classList.remove('opacity-0', 'scale-90');
            }, 10);
        }

        function closeResetModal() {
            const overlay = document.getElementById('reset-overlay');
            const content = document.getElementById('reset-content');
            content.classList.add('opacity-0', 'scale-90');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
        }

        async function executeReset() {
            const id = document.getElementById('reset-id').value;
            const btn = document.getElementById('reset-btn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Mereset...</span>';
            btn.disabled = true;
            try {
                const res = await fetch('/lautan-ternak-pantura/api/admin/reset_password', {
                    method: 'POST',
                    body: JSON.stringify({ id: id }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    closeResetModal();
                    showSuccessNotification(data.message);
                } else {
                    showErrorNotification(data.message);
                }
            } catch (err) {
                showErrorNotification('Gagal menghubungi server');
            }
            btn.innerHTML = '<i class="fas fa-key"></i> <span>Ya, Reset</span>';
            btn.disabled = false;
        }

        document.getElementById('user-form').onsubmit = async function(e) {
            e.preventDefault();
            if (!this.checkValidity()) { this.reportValidity(); return; }

            // Collect data into an object
            const formData = new FormData(this);
            const dataObj = {};
            formData.forEach((value, key) => { dataObj[key] = value; });

            const isEdit = dataObj.id && dataObj.id !== '';
            const url = isEdit ? '/lautan-ternak-pantura/api/admin/update_user' : '/lautan-ternak-pantura/api/admin/add_user';

            const btn = document.getElementById('submit-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Menyimpan...</span>';
            btn.disabled = true;

            try {
                const res = await fetch(url, { 
                    method: 'POST', 
                    body: JSON.stringify(dataObj),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    showSuccessNotification(data.message);
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorNotification(data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                showErrorNotification('Koneksi bermasalah atau data tidak valid');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };
    </script>
</body>
</html>
