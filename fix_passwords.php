<?php
require_once 'config/database.php';
if (isset($conn)) {
    $newHash = password_hash('password123', PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = :password";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':password' => $newHash])) {
        echo "Successfully updated all user passwords to 'password123'";
    } else {
        echo "Failed to update passwords.";
    }
} else {
    echo "Database connection failed.";
}
?>
