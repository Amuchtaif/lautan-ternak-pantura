<?php

// Set default timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Simple .env parser function
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Load .env relative to config directory
loadEnv(__DIR__ . '/../.env');

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'lautan_ternak_pantura';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Synchronize MySQL connection timezone to Asia/Jakarta (WIB)
    $conn->exec("SET time_zone = '+07:00'");
} catch(PDOException $exception) {
    // Silently continue if database is not set up yet
    // echo "Connection error: " . $exception->getMessage();
}
?>
