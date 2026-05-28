<?php
$id = isset($order['id']) ? $order['id'] : (isset($_GET['id']) ? $_GET['id'] : '');
if ($id) {
    header("Location: /lautan-ternak-pantura/sales/detail/" . $id);
} else {
    header("Location: /lautan-ternak-pantura/sales/index");
}
exit;