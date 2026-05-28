USE lautan_ternak_pantura;

CREATE TABLE IF NOT EXISTS savings_plans_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_code VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    livestock_target VARCHAR(150) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    monthly_target DECIMAL(15,2) NOT NULL,
    duration_month INT NOT NULL,
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    status ENUM('active', 'completed', 'overdue', 'cancelled') DEFAULT 'active',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS savings_transactions_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    savings_plan_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_proof VARCHAR(255) NOT NULL,
    transaction_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (savings_plan_id) REFERENCES savings_plans_new(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO savings_plans_new (
    id, plan_code, customer_id, livestock_target, target_amount, current_amount,
    monthly_target, duration_month, start_date, target_date, status, notes, created_at
)
SELECT
    sp.id,
    CONCAT('TQ-MIG-', LPAD(sp.id, 6, '0')),
    sp.customer_id,
    'Hewan Qurban',
    sp.target_amount,
    COALESCE((SELECT SUM(st.amount) FROM savings_transactions st WHERE st.plan_id = sp.id AND st.status = 'verified'), 0),
    sp.monthly_installment,
    GREATEST(1, CEIL(sp.target_amount / NULLIF(sp.monthly_installment, 0))),
    DATE(sp.created_at),
    DATE_ADD(DATE(sp.created_at), INTERVAL GREATEST(1, CEIL(sp.target_amount / NULLIF(sp.monthly_installment, 0))) MONTH),
    CASE WHEN sp.status IN ('active', 'completed', 'cancelled') THEN sp.status ELSE 'active' END,
    NULL,
    sp.created_at
FROM savings_plans sp
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO savings_transactions_new (
    id, savings_plan_id, amount, payment_method, payment_proof,
    transaction_status, verified_by, verified_at, created_at
)
SELECT
    id, plan_id, amount, 'transfer_bank', proof_of_payment,
    status, verified_by,
    CASE WHEN status IN ('verified', 'rejected') THEN created_at ELSE NULL END,
    created_at
FROM savings_transactions
ON DUPLICATE KEY UPDATE id = id;

SET FOREIGN_KEY_CHECKS = 0;
RENAME TABLE savings_transactions TO savings_transactions_old,
             savings_plans TO savings_plans_old,
             savings_plans_new TO savings_plans,
             savings_transactions_new TO savings_transactions;
SET FOREIGN_KEY_CHECKS = 1;
