<?php
$target = '/lautan-ternak-pantura/auth/login?action=register';
if (isset($_GET['redirect']) && $_GET['redirect'] !== '') {
    $target .= '&redirect=' . urlencode($_GET['redirect']);
}
header("Location: " . $target);
exit();
?>
