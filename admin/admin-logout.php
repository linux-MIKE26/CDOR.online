<?php
require __DIR__ . '/../app/config/bootstrap.php';

session_destroy();

header("Location: /admin/admin-login.php");
exit;
?>
