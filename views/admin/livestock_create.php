<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Hewan - Admin Dashboard</title>
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
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
        
        <?php include 'includes/topbar.php'; ?>

        <!-- Page Body -->
        <main class="p-8 space-y-8 flex-grow max-w-4xl">
            
            <!-- Breadcrumbs / Back button -->
            <div class="flex items-center gap-3">
                <a href="/lautan-ternak-pantura/livestock/index" class="w-10 h-10 rounded-xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-brand-primary hover:border-brand-primary/20 transition-all shadow-sm">
                    <i class="fas fa-chevron-left text-sm"></i>
                </a>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">Kembali ke inventori</p>
                    <h1 class="text-xl font-black text-gray-900 tracking-tight mt-1">Tambah Hewan Ternak Baru</h1>
                </div>
            </div>

            <!-- Error Notification -->
            <?php if (isset($errorMsg)): ?>
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3 text-sm font-bold">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span><?php echo $errorMsg; ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <form action="/lautan-ternak-pantura/livestock/create" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Kode Hewan -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Kode Hewan (Opsional)</label>
                            <input type="text" name="livestock_code" placeholder="Contoh: LTP-LIV-006 (Atau kosongkan untuk auto-generate)"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>



                        <!-- Ras / Breed -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis / Breed Hewan</label>
                            <input type="text" name="breed" required placeholder="Contoh: Sapi Brahman, Kambing Etawa"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Jenis Kelamin</label>
                            <div class="relative">
                                <select name="gender" required class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-bold text-sm appearance-none cursor-pointer">
                                    <option value="male">Jantan (Male)</option>
                                    <option value="female">Betina (Female)</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Umur -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Umur (Bulan)</label>
                            <input type="number" name="age" required min="1" placeholder="Masukkan umur dalam satuan bulan"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Berat -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Berat Hewan (kg)</label>
                            <input type="number" step="0.01" name="weight" required min="1" placeholder="Contoh: 350.5"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Mitra Peternak -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Mitra Peternak (Breeder)</label>
                            <input type="text" name="peternak_name" required placeholder="Contoh: Ahmad Peternak, Budi Peternak"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Harga Beli -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Beli (Rp)</label>
                            <input type="text" id="purchase_price" name="purchase_price" required placeholder="Contoh: 18.000.000" oninput="formatCurrency(this)"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Harga Jual -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Harga Jual (Rp)</label>
                            <input type="text" id="selling_price" name="selling_price" required placeholder="Contoh: 21.000.000" oninput="formatCurrency(this)"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Jumlah Stok -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Stok Awal (Ekor)</label>
                            <input type="number" name="stock" required min="0" value="1" placeholder="Masukkan jumlah stok hewan"
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm">
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Deskripsi & Kondisi</label>
                            <textarea name="description" rows="4" placeholder="Detail kondisi fisik, riwayat vaksin PMK, nafsu makan..."
                                class="w-full px-6 py-4 bg-gray-50 border border-transparent rounded-2xl outline-none focus:border-brand-primary/20 focus:bg-white transition-all font-semibold text-sm"></textarea>
                        </div>

                        <!-- Foto Hewan -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Foto Hewan</label>
                            <div id="drop-zone" class="w-full border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center cursor-pointer hover:border-brand-primary/40 hover:bg-brand-light/10 transition-all" onclick="document.getElementById('image-input').click()">
                                <div id="upload-placeholder">
                                    <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-cloud-arrow-up text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-600">Klik atau seret foto hewan ke sini</p>
                                    <p class="text-[10px] font-bold text-gray-300 mt-1.5">Mendukung format JPG, JPEG, PNG, WEBP, GIF (Maks. 2MB)</p>
                                </div>
                                <div id="image-preview" class="hidden">
                                    <img id="preview-img" src="" alt="Preview" class="w-48 h-48 object-cover rounded-2xl mx-auto border-4 border-white shadow-md">
                                    <p id="preview-name" class="text-xs font-bold text-brand-primary mt-3"></p>
                                    <button type="button" onclick="resetImagePreview(event)" class="mt-2 text-xs font-bold text-red-500 hover:underline">Hapus Foto</button>
                                </div>
                            </div>
                            <input type="file" name="image" id="image-input" accept="image/*" class="hidden" onchange="previewImage(this)">
                        </div>

                    </div>

                    <!-- Submit & Cancel Buttons -->
                    <div class="pt-6 flex flex-col sm:flex-row gap-4">
                        <a href="/lautan-ternak-pantura/livestock/index" class="flex-grow px-8 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-center text-sm hover:bg-gray-200 transition-all">
                            Batal
                        </a>
                        <button type="submit" class="flex-grow px-8 py-4 bg-brand-primary text-white rounded-2xl font-black text-sm hover:bg-brand-dark shadow-xl shadow-brand-primary/20 transition-all">
                            Simpan Data Hewan
                        </button>
                    </div>

                </form>
            </div>

        </main>
    </div>

    <script>
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, "");
            if (value !== "") {
                input.value = new Intl.NumberFormat("id-ID").format(parseInt(value));
            } else {
                input.value = "";
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('image-preview').classList.remove('hidden');
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('preview-name').innerText = file.name;
                };
                reader.readAsDataURL(file);
            }
        }

        function resetImagePreview(event) {
            event.stopPropagation();
            document.getElementById('upload-placeholder').classList.remove('hidden');
            document.getElementById('image-preview').classList.add('hidden');
            document.getElementById('preview-img').src = '';
            document.getElementById('preview-name').innerText = '';
            document.getElementById('image-input').value = '';
        }

        // Clean currency formatting before submitting
        document.querySelector('form').addEventListener('submit', function(e) {
            const pPrice = document.getElementById('purchase_price');
            const sPrice = document.getElementById('selling_price');
            pPrice.value = pPrice.value.replace(/\D/g, "");
            sPrice.value = sPrice.value.replace(/\D/g, "");
        });
    </script>
</body>
</html>
