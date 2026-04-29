<?php
session_start();
session_destroy();
header("Location: /lautan-ternak-pantura/index.php");
exit;
?>
