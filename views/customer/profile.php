<?php 
require_once '../../config/database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /lautan-ternak-pantura/views/auth/login");
    exit;
}

$userId = $_SESSION['user_id'];
$user = [];

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}

require_once '../../includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <a href="/lautan-ternak-pantura/views/customer/dashboard" class="text-brand-primary text-sm font-bold hover:underline flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>



        <div class="bg-white rounded-3xl shadow-sm border border-gray-50 overflow-hidden">
            <div class="px-10 py-8 border-b border-gray-50 bg-gray-50/50">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Profil Saya</h1>
                <p class="text-sm text-gray-400 font-bold mt-1">Kelola data diri dan kata sandi Anda</p>
            </div>

            <form action="/lautan-ternak-pantura/api/auth/update_profile" method="POST" class="p-10 space-y-8">
                <!-- Data Diri -->
                <div>
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-user-circle text-brand-primary"></i> Data Diri
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">No. Telepon</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Alamat</label>
                            <textarea name="address" rows="2"
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold resize-none"
                                placeholder="Alamat lengkap"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Ubah Kata Sandi -->
                <div class="border-t border-gray-100 pt-8">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-lock text-brand-primary"></i> Ubah Kata Sandi
                    </h3>
                    <p class="text-xs text-gray-400 font-bold mb-6 ml-1">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="cp"
                                    class="w-full px-5 pr-14 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold"
                                    placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
                                <button type="button" onclick="togglePw('cp')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-primary"><i class="far fa-eye" id="eye-cp"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Kata Sandi Baru</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="np"
                                    class="w-full px-5 pr-14 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold"
                                    placeholder="Min. 8 karakter">
                                <button type="button" onclick="togglePw('np')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-primary"><i class="far fa-eye" id="eye-np"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">Konfirmasi Baru</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="cnp"
                                    class="w-full px-5 pr-14 py-4 bg-gray-50 border-2 border-transparent focus:border-brand-primary/10 focus:bg-white rounded-2xl outline-none transition-all text-gray-700 font-bold"
                                    placeholder="Ulangi sandi baru">
                                <button type="button" onclick="togglePw('cnp')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-primary"><i class="far fa-eye" id="eye-cnp"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-primary text-white py-5 rounded-2xl font-black text-base shadow-xl shadow-brand-primary/20 hover:bg-brand-dark hover:-translate-y-0.5 transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePw(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById('eye-' + id);
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
