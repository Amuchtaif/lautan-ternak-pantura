# TASK

Bangun modul Tabungan Qurban terpisah dari sistem penjualan langsung pada aplikasi marketplace "Lautan Ternak Pantura".

Gunakan:

* PHP Native MVC Custom
* MySQL PDO
* TailwindCSS
* OOP clean architecture
* secure validation
* financial transaction approach

# IMPORTANT ARCHITECTURE RULE

Tabungan qurban HARUS dipisahkan dari:

* order penjualan
* checkout marketplace
* transaksi pembelian langsung

Karena:

* tabungan bersifat financial plan
* memiliki target nominal
* memiliki cicilan berkala
* memiliki histori setoran
* tidak langsung menghasilkan order pembelian

JANGAN gabungkan:

* tabel orders
* payments
* checkout marketplace

dengan:

* savings
* savings_transactions

# BUSINESS CONTEXT

Customer dapat:

* membuat tabungan qurban
* menentukan target hewan
* melihat progress tabungan
* melakukan setoran cicilan
* upload bukti transfer
* melihat histori transaksi tabungan

Admin dapat:

* memverifikasi pembayaran tabungan
* melihat progress seluruh nasabah tabungan
* melihat laporan tabungan

# MODULES TO BUILD

# 1. SAVINGS PLAN MODULE

## FEATURES

* buat tabungan qurban
* pilih target hewan
* tentukan target nominal
* simulasi cicilan bulanan
* progress percentage
* histori setoran

# REQUIRED FILES

/controllers

* SavingsController.php

/models

* SavingsPlan.php
* SavingsTransaction.php

/views/customer

* savings.php
* savings_create.php
* savings_detail.php

/views/admin

* savings_management.php
* savings_detail.php

/api/savings

* create.php
* deposit.php

# DATABASE TABLES

## savings_plans

Fields:

* id
* plan_code
* customer_id
* livestock_target
* target_amount
* current_amount
* monthly_target
* duration_month
* start_date
* target_date
* status
* notes
* created_at
* updated_at

# STATUS

Gunakan:

* active
* completed
* overdue
* cancelled

## savings_transactions

Fields:

* id
* savings_plan_id
* amount
* payment_method
* payment_proof
* transaction_status
* verified_by
* verified_at
* notes
* created_at

# STATUS TRANSACTION

Gunakan:

* pending
* verified
* rejected

# BUSINESS LOGIC

## Create Savings Plan

* customer memilih target hewan
* sistem menghitung target nominal
* sistem menghitung cicilan bulanan
* generate unique plan code
* insert savings plan

## Deposit Flow

Customer:

* input nominal setoran
* upload bukti transfer
* submit pembayaran

System:

* simpan transaksi sebagai pending
* menunggu verifikasi admin

Admin:

* verifikasi pembayaran
* update current_amount
* hitung progress percentage
* update status jika target tercapai

# IMPORTANT RULES

* current_amount tidak boleh update sebelum transaksi verified
* gunakan append-only transaction history
* jangan overwrite histori transaksi
* semua transaksi harus memiliki audit trail
* gunakan database transaction PDO
* gunakan prepared statement

# PROGRESS CALCULATION

Formula:
progress = (current_amount / target_amount) * 100

Jika:
current_amount >= target_amount

maka:
status = completed

# MONTHLY INSTALLMENT SIMULATION

Formula:
monthly_target = target_amount / duration_month

# PAYMENT UPLOAD REQUIREMENTS

* jpg/png/webp only
* max 2MB
* secure filename
* validate mime type
* simpan ke:
  storage/uploads/savings

# UI REQUIREMENTS

Gunakan TailwindCSS:

* progress bar
* statistic cards
* riwayat transaksi
* status badge
* upload modal
* responsive mobile UI

# CUSTOMER DASHBOARD FEATURES

Tampilkan:

* total tabungan aktif
* total saldo tabungan
* progress target
* transaksi terakhir
* target completion date

# ADMIN DASHBOARD FEATURES

Tampilkan:

* total nasabah tabungan
* total dana terkumpul
* transaksi pending verifikasi
* tabungan hampir jatuh tempo
* tabungan selesai

# 2. SAVINGS REPORT MODULE

# REQUIRED REPORTS

## Daily Savings Report

* total setoran harian
* jumlah transaksi
* transaksi pending
* transaksi verified

## Monthly Savings Report

* total dana terkumpul
* pertumbuhan tabungan
* jumlah nasabah aktif
* completion rate
* statistik pembayaran

# REQUIRED FILES

/controllers

* SavingsReportController.php

/models

* SavingsReport.php

/views/admin

* savings_reports_daily.php
* savings_reports_monthly.php

# REPORT QUERY REQUIREMENTS

Gunakan:

* SUM
* COUNT
* GROUP BY
* DATE_FORMAT

# FILTERS

* filter tanggal
* filter bulan
* filter status
* filter customer

# SECURITY REQUIREMENTS

* auth middleware
* role validation
* CSRF protection
* SQL injection protection
* secure upload validation
* escape output HTML

# CODE STYLE

* gunakan OOP PHP
* reusable method
* service-oriented logic
* hindari duplicate query
* business logic jangan di controller

# OUTPUT EXPECTATION

AI harus menghasilkan:

* migration SQL
* model class
* controller class
* savings business logic
* progress calculation
* upload handler
* Tailwind UI
* report query
* dashboard widget
* dummy seeder
* route integration

# IMPORTANT

Jangan gunakan Laravel.
Gunakan pure native PHP MVC custom.

Pastikan:
MODUL TABUNGAN TERPISAH TOTAL DARI MODUL PENJUALAN.
