<?php
// Mock Session
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin'];

$targetId = 'server_debug_mike';

// Start it
$_GET['action'] = 'toggle';
$_GET['id'] = $targetId;
$_GET['command'] = 'start';

ob_start();
require 'api.php';
$output = ob_get_clean();

echo "API Output: " . $output . "\n";

// Check resulting PID and Process
$servers = json_decode(file_get_contents('servers.json'), true);
foreach ($servers as $s) {
    if ($s['id'] == $targetId) {
        echo "Server PID: " . $s['pid'] . "\n";
        if ($s['pid']) {
             // Check /proc directly
             $exists = file_exists("/proc/" . $s['pid']);
             echo "Proc Exists: " . ($exists ? 'Yes' : 'No') . "\n";
             if ($exists) {
                 // Check if it handles signal
                 sleep(1);
                 $exists_after = file_exists("/proc/" . $s['pid']);
                 echo "Proc Exists After 1s: " . ($exists_after ? 'Yes' : 'No') . "\n";
             }
        }
    }
}
?>
