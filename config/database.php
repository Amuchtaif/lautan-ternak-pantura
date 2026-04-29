<?php
$host = 'localhost';
$db_name = 'lautan_ternak_pantura';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    // Silently continue if database is not set up yet
    // echo "Connection error: " . $exception->getMessage();
}
?>
