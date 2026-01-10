<?php
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin'];

$servers = json_decode(file_get_contents('servers.json'), true);
if (count($servers) == 0) die("No servers");
$targetId = $servers[0]['id'];
echo "Starting $targetId...\n";

$_GET['action'] = 'toggle';
$_GET['id'] = $targetId;
$_GET['command'] = 'start';

require 'api.php';
?>
