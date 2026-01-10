<?php
// Mock Session
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin'];

// Mock $_GET
$_GET['action'] = 'toggle';
$_GET['id'] = 'server1';
$_GET['command'] = 'start';

// Include api.php
// We need to prevent api.php from exiting, but it likely will exit after echo.
// We can capture output buffer.
ob_start();
require 'api.php';
$output = ob_get_clean();

echo "API Output: " . $output . "\n";

// Check servers.json content after
$servers = json_decode(file_get_contents('servers.json'), true);
foreach ($servers as $s) {
    if ($s['id'] == 'server1') {
        echo "Server1 Status: " . $s['status'] . "\n";
        echo "Server1 PID: " . $s['pid'] . "\n";
        // Check if process is running
        if ($s['pid']) {
            $running = file_exists("/proc/" . $s['pid']);
            echo "Process Running: " . ($running ? 'YES' : 'NO') . "\n";
        }
    }
}
?>
