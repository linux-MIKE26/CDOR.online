<?php
// Mock Session
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin'];

$targetId = 'server_1'; 
// Ensure server_1 exists in servers.json first or mock it? 
// Let's read servers.json and pick the first one
$servers = json_decode(file_get_contents('servers.json'), true);
if (count($servers) > 0) {
    $targetId = $servers[0]['id'];
} else {
    die("No servers found");
}

echo "Targeting Server ID: $targetId\n";

// 1. INSTALL
$_GET['action'] = 'toggle';
$_GET['id'] = $targetId;
$_GET['command'] = 'install';
$_GET['version'] = '1.21.4';

ob_start();
require 'api.php';
$output = ob_get_clean();
echo "Install Output: " . $output . "\n";

// 2. START
$_GET['command'] = 'start';
ob_start();
require 'api.php'; // Require again? Might fail due to function redeclare. 
// Actually we can't require 'api.php' twice in same script due to function definitions.
// We should use shell_exec to call the API script to avoid redeclaration issues.
?>
