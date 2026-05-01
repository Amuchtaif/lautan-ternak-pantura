# Project Summary
- **Tujuan aplikasi**: Marketplace "Lautan Ternak Pantura", platform penyedia hewan qurban dan aqiqah yang menghubungkan langsung pembeli dengan peternak (breeder), dilengkapi dengan sistem tabungan/cicilan.
- **Tech stack utama**: PHP (Native), MySQL (PDO), TailwindCSS (CDN), FontAwesome (CDN).
- **Pola arsitektur singkat**: MVC (Model-View-Controller) Custom Sederhana dengan Front Controller. Seluruh *request* masuk ke `index.php` dan didistribusikan ke masing-masing *Controller* lalu dirender ke dalam *Views*.

# Core Logic Flow (Function-Level Flowchart)
- **General Page Render**: `Request URL` -> `.htaccess` (Hapus `.php` & redirect ke index) -> `index.php` (Router) -> `Controller[index()]` -> `views/[halaman].php`
- **Marketplace Flow**: `index.php?url=marketplace` -> `MarketplaceController[index()]` -> Eksekusi filter query PDO `SELECT * FROM livestock` -> `views/marketplace.php` (Merender grid katalog hewan)
- **Auth Login Flow**: `views/auth/login.php` (Submit Form) -> `POST api/auth/login.php` -> Set Session ID & Role (Admin/Customer/Breeder) -> Redirect ke `views/[role]/dashboard.php`

# Clean Tree
```text
.
├── .htaccess
├── index.php
├── api
│   └── auth
│       ├── login.php
│       └── logout.php
├── config
│   └── database.php
├── controllers
│   ├── HomeController.php
│   ├── MarketplaceController.php
│   └── TabunganController.php
├── database
│   ├── schema.sql
│   └── seeder.sql
├── includes
│   ├── footer.php
│   └── header.php
└── views
    ├── admin
    │   └── dashboard.php
    ├── auth
    │   ├── login.php
    │   └── register.php
    ├── breeder
    │   └── dashboard.php
    ├── customer
    │   └── dashboard.php
    ├── home.php
    ├── marketplace.php
    └── tabungan.php
```

# Module Map (The Chapters)
- `index.php`
  - **Fungsi utama**: Front Controller / Router.
  - **Peran**: Menerima URI request, membersihkan string URL, memanggil class controller yang sesuai, dan mengeksekusi method `index()`.
- `api/auth/login.php` & `logout.php`
  - **Fungsi utama**: Action Handlers.
  - **Peran**: Menangani proses verifikasi login (saat ini tahap simulasi session) dan *destroy session* untuk logout berdasarkan alur *role-based access*.
- **Controllers**:
  - `HomeController.php`: Render halaman beranda (`/`).
  - `MarketplaceController.php`: Render halaman marketplace (`/marketplace`) & memanggil filter model `Livestock`.
  - `TabunganController.php`: Render halaman tabungan qurban (`/tabungan`).
  - `LivestockController.php`: Menangani halaman detail produk tunggal (`/livestock/detail/{id}`).

- **Models**:
  - `User.php`: Menangani query pengguna (auth, lookup email, insert pengguna baru).
  - `Livestock.php`: Menangani query data hewan (ambil semua produk yg *available* dengan filter, dan `getById` untuk detail).
- `config/database.php`
  - **Fungsi utama**: Inisialisasi PDO instance.
  - **Peran**: Membuka jalur koneksi antara layer aplikasi PHP ke basis data MySQL.

# Data & Config
- **Lokasi .env / Config Utama**: Tidak ditemukan `.env`. Konfigurasi basis data di-*hardcode* di dalam file `config/database.php`.
- **Skema Data Singkat**:
  - `users`: Entitas inti pengguna dan pengaturan role (admin, customer, breeder).
  - `livestock`: Katalog hewan ternak ternak (relasi ke `users.id` sebagai peternak penjual).
  - `orders` & `payments`: Pengelolaan pembelian langsung (checkout).
  - `savings_plans` & `savings_transactions`: Pengelolaan skema target tabungan, jumlah cicilan per bulan, serta riwayat bukti setor tiap pelanggan.
- **Lokasi Migration/Seed**: `database/schema.sql` (Pembuatan struktur DDL tabel) dan `database/seeder.sql` (Injeksi dummy data opsional).
- **Runtime Output**: Not found.

# External Integrations
- **TailwindCSS**: UI styling dipanggil via script tag CDN (`https://cdn.tailwindcss.com`) di `includes/header.php`.
- **FontAwesome**: Ikonography dipanggil via CDN (`cdnjs`) di `includes/header.php`.

# Risks / Blind Spots
- **Sudah Diperbaiki (Resolved)**: Sebelumnya terdapat *Authentication Gap* (simulasi login), tidak adanya pemisahan class *Model*, dan *Hardcoded Configuration* untuk kredensial DB. Saat ini semuanya telah diperbaiki:
  - Validasi login sekarang menggunakan `password_verify()` dengan pengecekan aktual ke tabel `users`.
  - Logika database telah dipisahkan ke layer *Model* (contoh: `Livestock` dan `User` class di folder `models/`).
  - Konfigurasi database sekarang menggunakan file `.env` dengan *environment variables*, tidak lagi *hardcoded* di source code.
