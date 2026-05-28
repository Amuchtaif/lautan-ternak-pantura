USE lautan_ternak_pantura;

-- TRUNCATE existing tables for clean seeding
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE purchase_payments;
TRUNCATE TABLE sale_payments;
TRUNCATE TABLE sales;
TRUNCATE TABLE purchases;
TRUNCATE TABLE savings_transactions;
TRUNCATE TABLE savings_plans;
TRUNCATE TABLE sohibul_qurban;
TRUNCATE TABLE livestock;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Passwords are 'password123' (hashed using bcrypt)
INSERT INTO users (full_name, name, username, email, phone, password, gender, role, status, address, city, province) VALUES
('Admin Utama', 'Admin Utama', 'admin', 'admin@ltp.com', '081111111111', '$2y$10$.PE6hkb3FsXvx29ckYUJeO/0Poub5Zqczg/vFnn/7bICDICWkQ3h6', 'male', 'admin', 'active', 'Kantor Lautan Ternak Pantura', 'Cirebon', 'Jawa Barat'),
('Ahmad Peternak', 'Ahmad Peternak', 'ahmad', 'ahmad@breeder.com', '082222222222', '$2y$10$.PE6hkb3FsXvx29ckYUJeO/0Poub5Zqczg/vFnn/7bICDICWkQ3h6', 'male', 'breeder', 'active', 'Kandang Mitra Pantura', 'Brebes', 'Jawa Tengah'),
('Budi Peternak', 'Budi Peternak', 'budi', 'budi@breeder.com', '083333333333', '$2y$10$.PE6hkb3FsXvx29ckYUJeO/0Poub5Zqczg/vFnn/7bICDICWkQ3h6', 'male', 'breeder', 'active', 'Kandang Mitra Pantura', 'Tegal', 'Jawa Tengah'),
('Siti Customer', 'Siti Customer', 'siti', 'siti@customer.com', '084444444444', '$2y$10$.PE6hkb3FsXvx29ckYUJeO/0Poub5Zqczg/vFnn/7bICDICWkQ3h6', 'female', 'customer', 'active', 'Jl. Merdeka No. 10', 'Cirebon', 'Jawa Barat'),
('Agus Customer', 'Agus Customer', 'agus', 'agus@customer.com', '085555555555', '$2y$10$.PE6hkb3FsXvx29ckYUJeO/0Poub5Zqczg/vFnn/7bICDICWkQ3h6', 'male', 'customer', 'active', 'Jl. Sudirman No. 5', 'Cirebon', 'Jawa Barat');

-- Seed livestock
INSERT INTO livestock (livestock_code, peternak_name, breed, gender, age, weight, purchase_price, selling_price, stock, status, image, description) VALUES
('LTP-LIV-001', 'Ahmad Peternak', 'Sapi Limousin', 'male', 24, 350.50, 18000000, 21000000, 5, 'available', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80', 'Sehat, sudah vaksin PMK, gemuk, nafsu makan baik.'),
('LTP-LIV-002', 'Ahmad Peternak', 'Sapi Brahman', 'male', 30, 400.00, 21000000, 25000000, 3, 'available', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&q=80', 'Sapi Brahman pilihan. Berotot tinggi, jinak dan lincah.'),
('LTP-LIV-003', 'Budi Peternak', 'Kambing Etawa', 'male', 14, 35.00, 2800000, 3500000, 10, 'available', 'https://images.unsplash.com/photo-1524024973431-2ad916746881?auto=format&fit=crop&q=80', 'Kambing Etawa jantan dengan tanduk utuh dan simetris.'),
('LTP-LIV-004', 'Budi Peternak', 'Kambing Gibas', 'male', 10, 25.00, 1900000, 2500000, 12, 'available', 'https://images.unsplash.com/photo-1484557985045-edf25e08da73?auto=format&fit=crop&q=80', 'Cocok untuk aqiqah, sehat tanpa cacat, bulu lebat bersih.'),
('LTP-LIV-005', 'Ahmad Peternak', 'Kambing Jawa', 'male', 16, 40.00, 3200000, 4000000, 0, 'sold', 'https://images.unsplash.com/photo-1511117833895-4b473c0b85d6?auto=format&fit=crop&q=80', 'Kambing Jawa jantan besar, tangguh.');

-- Seed savings_plans
INSERT INTO savings_plans (plan_code, customer_id, livestock_target, target_amount, current_amount, monthly_target, duration_month, start_date, target_date, status, notes) VALUES
('TQ-SEED-0001', 4, 'Sapi Limousin', 21000000, 1750000, 1750000, 12, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 12 MONTH), 'active', 'Sohibul qurban: Siti Customer'),
('TQ-SEED-0002', 5, 'Kambing Etawa', 3500000, 350000, 350000, 10, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 MONTH), 'active', 'Sohibul qurban: Agus Customer');

-- Seed savings_transactions
INSERT INTO savings_transactions (savings_plan_id, amount, payment_method, payment_proof, transaction_status, verified_by, verified_at) VALUES
(1, 1750000, 'transfer_bank', '/lautan-ternak-pantura/storage/uploads/savings/dummy_proof1.jpg', 'verified', 1, NOW()),
(1, 1750000, 'transfer_bank', '/lautan-ternak-pantura/storage/uploads/savings/dummy_proof2.jpg', 'pending', NULL, NULL),
(2, 350000, 'transfer_bank', '/lautan-ternak-pantura/storage/uploads/savings/dummy_proof3.jpg', 'verified', 1, NOW());

-- Seed purchases (Pembelian Hewan)
INSERT INTO purchases (purchase_code, livestock_id, peternak_name, qty, purchase_price, total_purchase, amount_paid, notes, purchased_at, created_by, payment_type) VALUES
('LTP-PUR-001', 1, 'Ahmad Peternak', 5, 18000000, 90000000, 90000000, 'Pembelian stok awal Sapi Limousin', DATE_SUB(NOW(), INTERVAL 5 DAY), 1, 'lunas'),
('LTP-PUR-002', 3, 'Budi Peternak', 10, 2800000, 28000000, 28000000, 'Pembelian stok awal Kambing Etawa', DATE_SUB(NOW(), INTERVAL 3 DAY), 1, 'lunas');

-- Seed sales (Penjualan Hewan)
INSERT INTO sales (invoice_code, customer_name, customer_phone, livestock_id, livestock_name, peternak_name, qty, selling_price_snapshot, total_price, payment_type, payment_status, sale_status, notes, created_by, payment_method, payment_proof) VALUES
-- Lunas transaction
('LTP-INV-001', 'Siti Customer', '084444444444', 1, 'Sapi Limousin', 'Ahmad Peternak', 1, 21000000, 21000000, 'lunas', 'paid', 'completed', 'Pesanan kurban Siti', 4, 'Transfer Bank Manual', '/lautan-ternak-pantura/storage/uploads/receipts/proof_lunas.jpg'),
-- DP / Partial transaction
('LTP-INV-002', 'Agus Customer', '085555555555', 3, 'Kambing Etawa', 'Budi Peternak', 2, 3500000, 7000000, 'dp', 'partial', 'processing', 'Cicilan kurban Agus', 5, 'Transfer Bank Manual', '/lautan-ternak-pantura/storage/uploads/receipts/proof_dp.jpg');

-- Seed sale_payments
INSERT INTO sale_payments (sale_id, payment_code, payment_method, payment_amount, payment_note, payment_proof, payment_status, created_by) VALUES
-- Payment for INV-001 (Fully Paid)
(1, 'LTP-PAY-001', 'Transfer Bank Manual', 21000000, 'Pelunasan invoice LTP-INV-001', '/lautan-ternak-pantura/storage/uploads/receipts/proof_lunas.jpg', 'verified', 4),
-- Payments for INV-002 (DP + Installment)
(2, 'LTP-PAY-002', 'Transfer Bank Manual', 3000000, 'Uang muka (DP) 3jt', '/lautan-ternak-pantura/storage/uploads/receipts/proof_dp.jpg', 'verified', 5),
(2, 'LTP-PAY-003', 'Transfer Bank Manual', 2000000, 'Cicilan ke-2', '/lautan-ternak-pantura/storage/uploads/receipts/proof_cicilan2.jpg', 'verified', 5);

-- Seed purchase_payments
INSERT INTO purchase_payments (purchase_id, payment_code, payment_amount, payment_date, notes) VALUES
(1, 'LTP-PUR-PAY-001', 90000000, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Pembayaran Lunas Awal'),
(2, 'LTP-PUR-PAY-002', 28000000, DATE_SUB(NOW(), INTERVAL 3 DAY), 'Pembayaran Lunas Awal');
