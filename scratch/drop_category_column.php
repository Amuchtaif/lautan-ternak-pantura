<?php
require_once 'config/database.php';
global $conn;

try {
    // 1. Check if category column exists
    $stmt = $conn->query("SHOW COLUMNS FROM livestock LIKE 'category'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        $conn->exec("ALTER TABLE livestock DROP COLUMN category");
        echo "Successfully dropped 'category' column from 'livestock' table!\n";
    } else {
        echo "Column 'category' does not exist in 'livestock' table, skipping.\n";
    }
} catch (Exception $e) {
    echo "Error dropping category column: " . $e->getMessage() . "\n";
}
