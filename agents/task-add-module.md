# TASK: Implementasi Modul Keuangan Tahap 1

## Tujuan

Membangun sistem keuangan dasar untuk aplikasi marketplace dan manajemen hewan qurban yang terintegrasi dengan pembelian hewan, penjualan hewan, serta pengelolaan modal investor dan biaya operasional.

Modul ini menjadi pondasi seluruh transaksi keuangan sehingga seluruh pergerakan uang dapat tercatat dalam satu arus kas terpusat.

---

# Modul yang Harus Dibuat

## 1. Kas & Bank

Buat menu baru:

```text
Keuangan
└── Kas & Bank
```

### Fitur

- Tambah rekening kas
- Edit rekening kas
- Hapus rekening kas
- Lihat saldo rekening
- Riwayat transaksi rekening

### Field

```text
Nama Rekening
Jenis Rekening (Kas Tunai / Bank)
Nomor Rekening (Opsional)
Nama Bank (Opsional)
Saldo Awal
Status Aktif
Keterangan
```

### Contoh Rekening

```text
Kas Operasional
BCA Operasional
BSI Qurban
```

### Ketentuan

Saldo rekening harus ter-update otomatis dari seluruh transaksi keuangan.

---

## 2. Modal Investor

Buat menu:

```text
Keuangan
└── Modal Investor
```

### Fitur

- Input modal investor
- Edit data modal investor
- Detail histori modal investor
- Daftar seluruh investasi aktif
- Upload bukti transfer

### Field

```text
Nama Investor
Tanggal Setor
Nominal
Rekening Tujuan
Keterangan
Status (Aktif / Selesai)
Lampiran Bukti Transfer
```

### Proses Otomatis

Ketika modal investor ditambahkan:

```text
Kas Bertambah
Arus Kas Bertambah
```

Transaksi arus kas otomatis dibuat dengan tipe:

```text
MODAL_INVESTOR
```

---

## 3. Dana Operasional

Buat menu:

```text
Keuangan
└── Dana Operasional
```

### Fitur

- Tambah pengeluaran
- Edit pengeluaran
- Hapus pengeluaran
- Upload bukti transaksi
- Filter berdasarkan tanggal
- Filter berdasarkan kategori

### Kategori Default

```text
Pakan Ternak
Vitamin dan Obat
Transportasi
Survey Hewan
Makan Bersama Buyer
Perawatan Kandang
Gaji Pekerja
Administrasi
Lain-lain
```

### Field

```text
Tanggal
Kategori
Nominal
Rekening Sumber
Keterangan
Lampiran
```

### Proses Otomatis

Ketika pengeluaran dibuat:

```text
Saldo rekening berkurang
Arus kas otomatis tercatat
```

Jenis transaksi:

```text
OPERASIONAL
```

---

## 4. Arus Kas Otomatis

Buat menu:

```text
Keuangan
└── Arus Kas
```

### Ketentuan

Arus kas tidak boleh diinput manual.

Data berasal dari:

```text
Modal Investor
Dana Operasional
Pembelian Hewan
Penjualan Hewan
```

### Tabel Arus Kas

| Field           | Keterangan                        |
| --------------- | --------------------------------- |
| Tanggal         | Tanggal transaksi                 |
| Kode Transaksi  | Nomor transaksi                   |
| Jenis Transaksi | Tipe transaksi                    |
| Deskripsi       | Detail transaksi                  |
| Kas Masuk       | Nominal masuk                     |
| Kas Keluar      | Nominal keluar                    |
| Saldo Berjalan  | Saldo setelah transaksi           |
| Rekening        | Rekening terkait                  |
| User Input      | Pengguna yang melakukan transaksi |

### Filter

```text
Periode
Jenis Transaksi
Rekening
```

### Jenis Transaksi

```text
MODAL_INVESTOR
OPERASIONAL
PEMBELIAN_HEWAN
PENJUALAN_HEWAN
```

---

## 5. Integrasi dengan Modul Pembelian Hewan

Saat transaksi pembelian hewan dibuat:

### Proses Otomatis

```text
Saldo rekening berkurang
Arus kas tercatat
```

### Jenis Transaksi

```text
PEMBELIAN_HEWAN
```

### Format Deskripsi

```text
Pembelian [Jenis Hewan] - [Nama/Kode Hewan]
```

Contoh:

```text
Pembelian Sapi - SP001
```

---

## 6. Integrasi dengan Modul Penjualan Hewan

Saat transaksi penjualan hewan dibuat:

### Jika Status = LUNAS

```text
Saldo rekening bertambah
Arus kas tercatat
```

Jenis transaksi:

```text
PENJUALAN_HEWAN
```

### Jika Status = DP

Hanya nominal DP yang masuk ke kas.

### Saat Pelunasan

```text
Sisa pembayaran masuk ke kas
Arus kas diperbarui
```

Sistem harus dapat menampilkan total:

```text
Total Tagihan
Total DP
Sisa Pelunasan
Status Pembayaran
```

---

## 7. Dashboard Ringkasan Keuangan

Tambahkan widget pada dashboard admin.

### Card Statistik

```text
Total Saldo Kas
Total Modal Investor Aktif
Pengeluaran Operasional Bulan Ini
Penjualan Bulan Ini
```

### Grafik

```text
Arus Kas Masuk vs Keluar
```

### Filter Periode

```text
7 Hari
30 Hari
90 Hari
1 Tahun
```

---

# Struktur Database

## cash_accounts

```sql
id
name
type
account_number
bank_name
opening_balance
current_balance
status
description
created_at
updated_at
```

## investors

```sql
id
name
phone
address
created_at
updated_at
```

## investor_funds

```sql
id
investor_id
cash_account_id
date
amount
proof
description
status
created_at
updated_at
```

## operational_categories

```sql
id
name
created_at
updated_at
```

## operational_expenses

```sql
id
category_id
cash_account_id
date
amount
description
attachment
created_at
updated_at
```

## cash_transactions

```sql
id
cash_account_id
transaction_type
reference_type
reference_id
transaction_date
description
cash_in
cash_out
balance_after
created_by
created_at
updated_at
```

---

# Ketentuan Penting

- Semua transaksi keuangan wajib tercatat pada tabel `cash_transactions`.
- Tidak boleh ada transaksi kas manual selain melalui modul yang tersedia.
- Saldo rekening harus dihitung otomatis berdasarkan transaksi.
- Seluruh nominal menggunakan format Rupiah Indonesia.
- Seluruh upload bukti transaksi menggunakan preview modal popup.
- Gunakan PHP Native dan Tailwind CSS sesuai arsitektur aplikasi yang sudah ada.
- Gunakan database transaction untuk seluruh proses penyimpanan data keuangan.
- Pastikan tidak terjadi selisih saldo apabila terjadi kegagalan proses penyimpanan.

---

# Hasil Akhir yang Diharapkan

Admin dapat:

- Melihat saldo seluruh rekening secara real-time.
- Melihat total modal investor yang masih aktif.
- Mencatat seluruh biaya operasional usaha.
- Mengetahui seluruh arus kas masuk dan keluar.
- Melacak sumber dan tujuan setiap transaksi keuangan.
- Mendapatkan dasar laporan keuangan untuk pengembangan modul profit dan bagi hasil investor pada tahap berikutnya.
