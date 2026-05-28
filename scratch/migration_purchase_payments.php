<?php
require_once 'd:\xampp\htdocs\lautan-ternak-pantura\config\database.php';

if (!isset($conn)) {
    die("Database connection failed. Please check config/database.php and your .env file.\n");
}

try {
    // Create purchase_payments table if not exists
    $conn->exec("CREATE TABLE IF NOT EXISTS purchase_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        purchase_id INT NOT NULL,
        payment_code VARCHAR(50) UNIQUE NOT NULL,
        payment_amount DECIMAL(15,2) NOT NULL,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Successfully created 'purchase_payments' table if not existed.\n";

    // Populate initial payments from purchases table
    $stmt = $conn->query("SELECT id, purchase_code, amount_paid, purchased_at FROM purchases WHERE amount_paid > 0");
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($purchases as $p) {
        // Check if there is already a payment for this purchase
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM purchase_payments WHERE purchase_id = ?");
        $checkStmt->execute([$p['id']]);
        if ($checkStmt->fetchColumn() == 0) {
            $payCode = 'LTP-PUR-PAY-' . time() . '-' . rand(100, 999) . '-' . $p['id'];
            $insStmt = $conn->prepare("INSERT INTO purchase_payments (purchase_id, payment_code, payment_amount, payment_date, notes) VALUES (?, ?, ?, ?, ?)");
            $insStmt->execute([
                $p['id'],
                $payCode,
                $p['amount_paid'],
                $p['purchased_at'],
                'Pembayaran Awal / DP'
            ]);
            $count++;
        }
    }
    echo "Populated {$count} initial payment records into 'purchase_payments'.\n";

} catch (PDOException $e) {
    die("Error running migration: " . $e->getMessage() . "\n");
}
?>
