<?php require 'views/admin/includes/header.php'; ?>
<?php include 'views/admin/includes/sidebar.php'; ?>
<div class="flex-grow flex flex-col min-h-screen max-w-full overflow-x-hidden">
    <?php include 'views/admin/includes/topbar.php'; ?>
    <main class="p-8 space-y-8 flex-grow">
        <!-- Title and Stats Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Kelompok <span class="text-brand-primary">Qurban Sapi</span></h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Daftar kelompok sapi patungan (maksimal 7 peserta per kelompok).</p>
            </div>
        </div>

        <!-- Group Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php if (empty($groups)): ?>
                <div class="lg:col-span-2 bg-white rounded-3xl border border-dashed border-gray-200 p-12 text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center mb-4 text-gray-400 text-2xl">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="text-xl font-black text-gray-900">Belum ada kelompok qurban sapi</h2>
                    <p class="text-sm text-gray-500 mt-2">Kelompok akan dibentuk otomatis ketika setoran nasabah sapi patungan mencapai target.</p>
                </div>
            <?php else: ?>
                <?php foreach ($groups as $group): ?>
                    <?php
                    $members = $groupMembers[$group['id']] ?? [];
                    $status = $group['status'];
                    
                    // Status Badge Colors
                    $badgeClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                    if ($status === 'Penuh') {
                        $badgeClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                    } elseif ($status === 'Hewan Dibeli') {
                        $badgeClass = 'bg-purple-50 text-purple-600 border border-purple-100';
                    } elseif ($status === 'Disembelih') {
                        $badgeClass = 'bg-orange-50 text-orange-600 border border-orange-100';
                    } elseif ($status === 'Selesai') {
                        $badgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                    }
                    ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-xl transition-all duration-300">
                        <!-- Card Header -->
                        <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-leaf text-brand-primary"></i> <?php echo htmlspecialchars($group['group_code']); ?>
                                </h2>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Dibuat: <?php echo date('d M Y', strtotime($group['created_at'])); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-8 flex-grow space-y-6">
                            <!-- Progress & Members Count -->
                            <div>
                                <div class="flex justify-between items-center text-xs font-bold text-gray-500 mb-2">
                                    <span>Kapasitas Kelompok</span>
                                    <span><?php echo count($members); ?> / 7 Orang</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden p-0.5">
                                    <div class="h-full bg-brand-primary rounded-full transition-all duration-500" style="width: <?php echo (count($members) / 7) * 100; ?>%"></div>
                                </div>
                            </div>

                            <!-- Livestock Info -->
                            <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Detail Hewan Qurban</h3>
                                    <?php if ($status !== 'Selesai'): ?>
                                        <button onclick='openAssignModal(<?php echo $group['id']; ?>, "<?php echo htmlspecialchars($group['group_code']); ?>", <?php echo $group['livestock_id'] ?: 'null'; ?>)' class="text-[10px] font-black text-brand-primary hover:text-brand-dark uppercase tracking-wider">
                                            <i class="fas fa-edit mr-1"></i> <?php echo $group['livestock_id'] ? 'Ubah' : 'Tentukan'; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($group['livestock_id']): ?>
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-xl bg-brand-light text-brand-primary flex items-center justify-center text-lg shrink-0">
                                            <i class="fas fa-leaf"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-gray-900"><?php echo htmlspecialchars($group['breed']); ?> (<?php echo htmlspecialchars($group['livestock_code']); ?>)</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Harga: Rp <?php echo number_format($group['selling_price'], 0, ',', '.'); ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-xs font-bold text-gray-400 italic">Belum ada sapi yang ditugaskan ke kelompok ini.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Members List -->
                            <div class="space-y-3">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Daftar Peserta Patungan</h3>
                                <?php if (empty($members)): ?>
                                    <p class="text-xs font-bold text-gray-400 italic">Tidak ada peserta aktif dalam kelompok ini.</p>
                                <?php else: ?>
                                    <div class="divide-y divide-gray-50">
                                        <?php foreach ($members as $index => $m): ?>
                                            <div class="flex items-center justify-between py-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400"><?php echo $index + 1; ?></span>
                                                    <div>
                                                        <p class="text-sm font-black text-gray-900"><?php echo htmlspecialchars($m['customer_name']); ?></p>
                                                        <p class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($m['plan_code']); ?> · Saldo: Rp <?php echo number_format($m['current_amount'], 0, ',', '.'); ?></p>
                                                    </div>
                                                </div>
                                                <?php if ($status !== 'Selesai'): ?>
                                                    <button onclick='openMoveModal(<?php echo $m['id']; ?>, "<?php echo htmlspecialchars($m['customer_name']); ?>", <?php echo $group['id']; ?>, "<?php echo htmlspecialchars($group['group_code']); ?>")' class="h-8 w-8 rounded-lg bg-gray-50 hover:bg-brand-light hover:text-brand-primary text-gray-400 flex items-center justify-center transition" title="Pindahkan Kelompok">
                                                        <i class="fas fa-arrow-right-arrow-left text-xs"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <?php if ($status !== 'Selesai'): ?>
                            <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/20 flex gap-3 shrink-0">
                                <form action="/lautan-ternak-pantura/savings/updateGroup" method="POST" class="w-full flex gap-3">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    
                                    <select name="status" onchange="this.form.submit()" class="flex-grow px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-xs outline-none focus:border-brand-primary cursor-pointer">
                                        <option value="" disabled selected>Ubah Status Kelompok...</option>
                                        <option value="Menunggu Anggota" <?php echo $status === 'Menunggu Anggota' ? 'disabled' : ''; ?>>Menunggu Anggota</option>
                                        <option value="Penuh" <?php echo $status === 'Penuh' ? 'disabled' : ''; ?>>Penuh</option>
                                        <option value="Hewan Dibeli" <?php echo $status === 'Hewan Dibeli' ? 'disabled' : ''; ?>>Hewan Dibeli</option>
                                        <option value="Disembelih" <?php echo $status === 'Disembelih' ? 'disabled' : ''; ?>>Disembelih</option>
                                        <option value="Selesai">Tutup Kelompok (Selesai)</option>
                                    </select>
                                    
                                    <button type="submit" name="submit_status" value="1" class="hidden"></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Assign Livestock Modal -->
<div id="assign-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-black text-gray-900">Tetapkan Hewan Qurban</h3>
            <button onclick="closeAssignModal()" class="w-8 h-8 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200"><i class="fas fa-times"></i></button>
        </div>
        <form action="/lautan-ternak-pantura/savings/updateGroup" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="group_id" id="assign-group-id">
            <input type="hidden" name="action" value="assign_livestock">
            
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Kelompok</label>
                <input type="text" id="assign-group-code" readonly class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Pilih Sapi Tersedia</label>
                <select name="livestock_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary">
                    <option value="">-- Pilih Sapi --</option>
                    <?php foreach ($availableCows as $cow): ?>
                        <option value="<?php echo $cow['id']; ?>">
                            <?php echo htmlspecialchars($cow['breed']); ?> (<?php echo htmlspecialchars($cow['livestock_code']); ?>) - Rp <?php echo number_format($cow['selling_price'], 0, ',', '.'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-brand-primary text-white py-4 rounded-xl font-black hover:bg-brand-dark transition">Simpan Ketetapan</button>
        </form>
    </div>
</div>

<!-- Move Member Modal -->
<div id="move-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 opacity-0 transition-all duration-300">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-black text-gray-900">Pindahkan Anggota Kelompok</h3>
            <button onclick="closeMoveModal()" class="w-8 h-8 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200"><i class="fas fa-times"></i></button>
        </div>
        <form action="/lautan-ternak-pantura/savings/updateGroup" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="group_id" id="move-src-group-id">
            <input type="hidden" name="plan_id" id="move-plan-id">
            <input type="hidden" name="action" value="move_member">
            
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Nama Peserta</label>
                <input type="text" id="move-member-name" readonly class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Kelompok Asal</label>
                <input type="text" id="move-group-code" readonly class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-2">Kelompok Tujuan</label>
                <select name="target_group_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-bold outline-none focus:border-brand-primary">
                    <option value="">-- Pilih Kelompok --</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?php echo $g['id']; ?>" class="target-group-opt" data-id="<?php echo $g['id']; ?>">
                            <?php echo htmlspecialchars($g['group_code']); ?> (Kapasitas: <?php echo $g['member_count']; ?>/7) - <?php echo htmlspecialchars($g['status']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-brand-primary text-white py-4 rounded-xl font-black hover:bg-brand-dark transition">Pindahkan Anggota</button>
        </form>
    </div>
</div>

<script>
function openAssignModal(groupId, groupCode, selectedLivestockId) {
    document.getElementById('assign-group-id').value = groupId;
    document.getElementById('assign-group-code').value = groupCode;
    
    const select = document.querySelector('#assign-modal select[name="livestock_id"]');
    if (selectedLivestockId) {
        select.value = selectedLivestockId;
    } else {
        select.value = "";
    }

    const modal = document.getElementById('assign-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}

function closeAssignModal() {
    const modal = document.getElementById('assign-modal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function openMoveModal(planId, customerName, srcGroupId, groupCode) {
    document.getElementById('move-plan-id').value = planId;
    document.getElementById('move-member-name').value = customerName;
    document.getElementById('move-src-group-id').value = srcGroupId;
    document.getElementById('move-group-code').value = groupCode;

    // Filter target group dropdown to hide original group and groups with status Selesai
    const opts = document.querySelectorAll('.target-group-opt');
    opts.forEach(opt => {
        const id = parseInt(opt.dataset.id);
        if (id === srcGroupId) {
            opt.style.display = 'none';
            opt.disabled = true;
        } else {
            opt.style.display = 'block';
            opt.disabled = false;
        }
    });

    const modal = document.getElementById('move-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}

function closeMoveModal() {
    const modal = document.getElementById('move-modal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}
</script>
<?php require 'views/admin/includes/footer.php'; ?>
