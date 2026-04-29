USE lautan_ternak_pantura;

-- Passwords are 'password123' (hashed using bcrypt)
INSERT INTO users (name, email, password, role) VALUES
('Admin Utama', 'admin@ltp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Ahmad Peternak', 'ahmad@breeder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'breeder'),
('Budi Peternak', 'budi@breeder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'breeder'),
('Siti Customer', 'siti@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Agus Customer', 'agus@customer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

INSERT INTO livestock (breeder_id, type, category, weight, age, health_condition, price, image_url, status) VALUES
(2, 'sapi', 'qurban', 350.50, 24, 'Sehat, sudah vaksin PMK', 21000000, 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&q=80', 'available'),
(2, 'sapi', 'qurban', 400.00, 30, 'Sehat, gemuk, lincah', 25000000, 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&q=80', 'available'),
(3, 'kambing', 'qurban', 35.00, 14, 'Sehat, tanduk utuh', 3500000, 'https://images.unsplash.com/photo-1524024973431-2ad916746881?auto=format&fit=crop&q=80', 'available'),
(3, 'kambing', 'aqiqah', 25.00, 10, 'Sehat, cocok untuk aqiqah', 2500000, 'https://images.unsplash.com/photo-1484557985045-edf25e08da73?auto=format&fit=crop&q=80', 'available'),
(2, 'kambing', 'qurban', 40.00, 16, 'Sehat, besar', 4000000, 'https://images.unsplash.com/photo-1511117833895-4b473c0b85d6?auto=format&fit=crop&q=80', 'booked');

INSERT INTO savings_plans (customer_id, target_amount, monthly_installment, status) VALUES
(4, 21000000, 1750000, 'active'),
(5, 3500000, 350000, 'active');

INSERT INTO savings_transactions (plan_id, amount, proof_of_payment, status) VALUES
(1, 1750000, 'dummy_proof1.jpg', 'verified'),
(1, 1750000, 'dummy_proof2.jpg', 'pending'),
(2, 350000, 'dummy_proof3.jpg', 'verified');
