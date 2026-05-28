<?php
require_once '../../helpers/AuthHelper.php';

AuthHelper::logout();
header('Location: /lautan-ternak-pantura/auth/login');
exit;
