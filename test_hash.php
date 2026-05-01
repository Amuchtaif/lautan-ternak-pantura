<?php
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
if (password_verify('password123', $hash)) {
    echo "It matches password123";
} elseif (password_verify('password', $hash)) {
    echo "It matches password";
} else {
    echo "It does not match password or password123";
}
?>
