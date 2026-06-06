<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Setoran Tabungan - <?php echo htmlspecialchars($trx['id']); ?></title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                height: auto !important;
                min-height: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-gray-100 p-8 sm:p-12 print-card relative overflow-hidden">
        
        <!-- Watermark / Decorative element -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-primary/5 rounded-full blur-3xl pointer-events-none no-print"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-secondary/5 rounded-full blur-3xl pointer-events-none no-print"></div>

        <!-- Print Action Header -->
        <div class="no-print mb-8 flex justify-between items-center gap-4 bg-slate-50 border border-slate-100 p-4 rounded-2xl">
            <span class="text-xs font-bold text-gray-500"><i class="fas fa-info-circle mr-1"></i>Halaman ini dioptimalkan untuk dicetak.</span>
            <div class="flex gap-3">
                <button onclick="window.close()" class="bg-white text-gray-700 px-4 py-2 border border-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 transition flex items-center gap-1.5">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button onclick="window.print()" class="bg-brand-primary text-white px-5 py-2 rounded-xl text-sm font-black shadow-lg shadow-brand-primary/20 hover:bg-brand-dark transition flex items-center gap-1.5">
                    <i class="fas fa-print"></i> Cetak Kuitansi
                </button>
            </div>
        </div>

        <!-- Receipt Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-dashed border-gray-200 pb-8 gap-6">
            <div class="flex items-center gap-4">
                <img src="/lautan-ternak-pantura/assets/images/logo.png" alt="Logo" class="h-14 w-auto object-contain">
                <div>
                    <h1 class="text-xl font-black text-gray-900 leading-tight">Lautan Ternak Pantura</h1>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">Qurban & Aqiqah Amanah</p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-[10px] font-black text-brand-primary uppercase tracking-[0.2em]">Kuitansi Setoran</p>
                <p class="text-lg font-black text-gray-900 mt-1">#KTRX-<?php echo str_pad($trx['id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p class="text-xs text-gray-400 font-bold mt-1"><?php echo date('d F Y, H:i', strtotime($trx['created_at'])); ?></p>
            </div>
        </div>

        <!-- Receipt Body / Details -->
        <div class="py-8 space-y-6">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Telah Diterima Dari</p>
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 grid sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Nama Nasabah</p>
                        <p class="text-sm font-black text-gray-900 mt-1"><?php echo htmlspecialchars($plan['customer_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Email / Kontak</p>
                        <p class="text-xs font-bold text-gray-600 mt-1"><?php echo htmlspecialchars($plan['customer_email']); ?> <?php if($plan['customer_phone']): ?>&middot; <?php echo htmlspecialchars($plan['customer_phone']); ?><?php endif; ?></p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Kode Rencana Tabungan</p>
                        <p class="text-sm font-bold text-brand-primary mt-1"><?php echo htmlspecialchars($plan['plan_code']); ?> - <span class="text-gray-900 font-black"><?php echo htmlspecialchars($plan['livestock_target']); ?></span></p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Detail Setoran</p>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="py-3 text-[10px] font-black text-gray-400 uppercase">Keterangan</th>
                            <th class="py-3 text-[10px] font-black text-gray-400 uppercase">Metode</th>
                            <th class="py-3 text-right text-[10px] font-black text-gray-400 uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr>
                            <td class="py-4">
                                <p class="text-sm font-black text-gray-900">Setoran Tabungan Qurban</p>
                                <p class="text-xs text-gray-400 mt-1">Status: <span class="font-bold text-green-600">Terverifikasi</span></p>
                            </td>
                            <td class="py-4 text-xs font-bold text-gray-600 capitalize">
                                <?php echo htmlspecialchars(str_replace('_', ' ', $trx['payment_method'])); ?>
                            </td>
                            <td class="py-4 text-right font-black text-brand-primary text-base">
                                Rp <?php echo number_format($trx['amount'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes Section -->
            <?php
                // Extract deposit date and remaining notes
                $notes = $trx['notes'] ?? '';
                $depositDateVal = '';
                if (preg_match('/Tanggal setor:\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $notes, $matches)) {
                    $depositDateVal = date('d M Y', strtotime($matches[1]));
                }
                
                // Clean notes to get additional notes
                $cleanNotes = preg_replace('/(Setoran manual admin\.\s*)?Tanggal setor:\s*[0-9]{4}-[0-9]{2}-[0-9]{2}/i', '', $notes);
                $additionalNotes = trim($cleanNotes);
            ?>
            <?php if (!empty($depositDateVal) || !empty($additionalNotes)): ?>
                <div class="bg-blue-50/30 border border-blue-100/50 rounded-2xl p-5">
                    <?php if (!empty($depositDateVal)): ?>
                        <p class="text-[9px] font-bold text-blue-500 uppercase tracking-wider mb-1">Tanggal Setor</p>
                        <p class="text-xs font-medium text-slate-700 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($depositDateVal); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($additionalNotes)): ?>
                        <p class="text-[9px] font-bold text-blue-500 uppercase tracking-wider mb-1 mt-2">Catatan Setoran</p>
                        <p class="text-xs font-medium text-slate-700 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($additionalNotes)); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Receipt Footer / Signatures -->
        <div class="border-t border-dashed border-gray-200 pt-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
            <div class="max-w-xs">
                <p class="text-xs font-bold text-slate-400 leading-relaxed">Kuitansi ini diterbitkan secara resmi oleh sistem Lautan Ternak Pantura sebagai bukti setoran yang sah.</p>
            </div>
            <div class="text-left sm:text-right min-w-[200px]">
                <p class="text-xs font-bold text-gray-400">Verifikator Penerima,</p>
                <div class="h-16 flex items-center justify-start sm:justify-end">
                    <!-- Placeholder Stamp / Checked element -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-green-50 border border-green-200 text-xs font-black text-green-600 uppercase tracking-wider">
                        <i class="fas fa-check-circle"></i> PAID / LUNAS
                    </span>
                </div>
                <p class="text-sm font-black text-gray-900">Sistem Kasir Utama</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Lautan Ternak Pantura</p>
            </div>
        </div>

    </div>

    <!-- Automatically open print dialog on page load if direct print request -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('autoprint')) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>
</html>
