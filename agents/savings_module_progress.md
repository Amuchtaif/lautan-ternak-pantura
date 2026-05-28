# Progress Implementasi Modul Tabungan Qurban

Tanggal: 2026-05-20

## Status

Selesai diimplementasikan dan sudah diverifikasi dengan test runner bawaan.

Hasil test:

```text
Berhasil: 10
Gagal: 0
Status: GREEN
```

## Ringkasan Implementasi

- Modul Tabungan Qurban dipisahkan dari `orders`, `payments`, dan checkout marketplace.
- `current_amount` hanya bertambah saat transaksi setoran berubah menjadi `verified`.
- Riwayat setoran bersifat append-only melalui tabel `savings_transactions`.
- Verifikasi admin memakai database transaction PDO.
- Upload bukti setoran divalidasi berdasarkan MIME type `jpg/png/webp`, maksimal 2MB, dan disimpan ke `storage/uploads/savings`.
- UI customer dan admin menggunakan TailwindCSS, progress bar, statistic cards, status badge, riwayat transaksi, dan upload modal.
- Laporan harian dan bulanan memakai agregasi `SUM`, `COUNT`, `GROUP BY`, dan `DATE_FORMAT`.

## File Baru

- `models/SavingsPlan.php`
- `models/SavingsTransaction.php`
- `models/SavingsReport.php`
- `controllers/SavingsController.php`
- `controllers/SavingsReportController.php`
- `views/customer/savings.php`
- `views/customer/savings_create.php`
- `views/customer/savings_detail.php`
- `views/admin/savings_management.php`
- `views/admin/savings_detail.php`
- `views/admin/savings_reports_daily.php`
- `views/admin/savings_reports_monthly.php`
- `api/savings/create.php`
- `database/migration_savings_module.sql`
- `agents/savings_module_progress.md`

## File Diubah

- `api/savings/deposit.php`
- `api/savings/plan.php`
- `api/admin/verify_transfer.php`
- `controllers/TabunganController.php`
- `models/Savings.php`
- `views/tabungan.php`
- `views/customer/dashboard.php`
- `views/admin/dashboard.php`
- `views/admin/transfers.php`
- `views/admin/includes/sidebar.php`
- `database/schema.sql`
- `database/seeder.sql`
- `tests/run_tests.php`
- `tests/SavingsTest.php`

## Route Baru

- Customer dashboard tabungan: `/lautan-ternak-pantura/savings`
- Buat tabungan: `/lautan-ternak-pantura/savings/create`
- Detail tabungan customer: `/lautan-ternak-pantura/savings/detail/{id}`
- Admin manajemen tabungan: `/lautan-ternak-pantura/savings/management`
- Admin detail tabungan: `/lautan-ternak-pantura/savings/adminDetail/{id}`
- Laporan harian tabungan: `/lautan-ternak-pantura/savingsReport/daily`
- Laporan bulanan tabungan: `/lautan-ternak-pantura/savingsReport/monthly`

## API Baru / Diubah

- `POST /lautan-ternak-pantura/api/savings/create`
- `POST /lautan-ternak-pantura/api/savings/deposit`
- `POST /lautan-ternak-pantura/api/admin/verify_transfer`

## Database

Skema utama sudah diperbarui di `database/schema.sql`.

Untuk database lama, jalankan:

```sql
SOURCE database/migration_savings_module.sql;
```

Catatan:

- Migration akan memindahkan data lama ke tabel baru dengan suffix backup `_old`.
- Jika database produksi punya variasi struktur lama, cek dulu backup tabel sebelum menjalankan migration.

## Verifikasi

Perintah yang dijalankan:

```powershell
php -l models\SavingsPlan.php
php -l models\SavingsTransaction.php
php -l models\SavingsReport.php
php -l controllers\SavingsController.php
php -l controllers\SavingsReportController.php
php -l api\savings\create.php
php -l api\savings\deposit.php
php -l api\admin\verify_transfer.php
php -l views\customer\savings.php
php -l views\customer\savings_create.php
php -l views\customer\savings_detail.php
php -l views\admin\savings_management.php
php -l views\admin\savings_detail.php
php -l views\admin\savings_reports_daily.php
php -l views\admin\savings_reports_monthly.php
php tests\run_tests.php
```

Semua lint file yang dicek valid, dan test runner selesai GREEN.

## Patch Kompatibilitas Skema Lama

Tambahan setelah ditemukan error runtime pada database yang belum menjalankan `migration_savings_module.sql`:

- Model tabungan sekarang mendeteksi otomatis kolom lama vs baru.
- Kolom lama yang didukung:
  - `savings_transactions.plan_id`
  - `savings_transactions.status`
  - `savings_transactions.proof_of_payment`
  - `savings_plans.monthly_installment`
- Kolom baru yang tetap menjadi target migration:
  - `savings_transactions.savings_plan_id`
  - `savings_transactions.transaction_status`
  - `savings_transactions.payment_proof`
  - `savings_plans.monthly_target`
  - `savings_plans.current_amount`
- Dashboard admin, verifikasi transfer, dashboard customer, report harian, dan report bulanan sekarang tidak fatal saat database masih memakai struktur lama.

Rekomendasi tetap menjalankan migration saat siap, karena struktur baru lebih sesuai dengan modul finansial append-only.

## Progress Implementasi Auth Sohibul Qurban

Tanggal: 2026-05-20

Status: selesai diimplementasikan.

File baru:

- `controllers/AuthController.php`
- `controllers/DashboardController.php`
- `helpers/AuthHelper.php`
- `helpers/ValidationHelper.php`
- `services/AuthService.php`
- `database/migration_users_sohibul_qurban.sql`

File utama yang diubah:

- `models/User.php`
- `api/auth/register.php`
- `api/auth/login.php`
- `api/auth/logout.php`
- `views/auth/register.php`
- `views/auth/login.php`
- `views/customer/dashboard.php`
- `includes/header.php`
- `views/home.php`
- `database/schema.sql`
- `database/seeder.sql`

Route auth baru:

- `/lautan-ternak-pantura/auth/register`
- `/lautan-ternak-pantura/auth/login`
- `/lautan-ternak-pantura/auth/logout`
- `/lautan-ternak-pantura/customer/dashboard`

Fitur selesai:

- Registrasi Sohibul Qurban khusus role `customer`.
- Validasi nama, email unik, WhatsApp angka minimal 10 digit, password minimal 8 karakter, konfirmasi password, gender, alamat, kota, provinsi.
- CSRF protection pada login dan register.
- Password disimpan dengan `password_hash()`.
- Login memakai `password_verify()`.
- Session auto-login setelah register:
  - `user_id`
  - `full_name`
  - `name`
  - `email`
  - `role`
  - `is_login`
- Session regeneration saat login/register.
- Update `last_login` jika kolom tersedia.
- Register UI split screen modern dan mobile responsive.
- Login UI clean dengan remember me, forgot password placeholder, Google login placeholder, show/hide password, dan loading state.
- Dashboard customer menampilkan onboarding card setelah register.

Catatan kompatibilitas:

- `User.php` otomatis memakai `full_name` jika tersedia.
- Jika database masih memakai kolom lama `name`, registrasi tetap berjalan memakai kolom tersebut.
- Jalankan `database/migration_users_sohibul_qurban.sql` saat siap memakai struktur user lengkap.

Verifikasi:

```powershell
php -l helpers\ValidationHelper.php
php -l helpers\AuthHelper.php
php -l services\AuthService.php
php -l controllers\AuthController.php
php -l controllers\DashboardController.php
php -l models\User.php
php -l api\auth\register.php
php -l api\auth\login.php
php -l views\auth\register.php
php -l views\auth\login.php
php tests\run_tests.php
```

Hasil test terakhir: `10/10 GREEN`.
