<?php
// Mock Session
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Admin'];

// Mock input for creating a temp server to match user's port, if we can.
// Actually, let's just use server2 (port 28949) for testing if the issue is generic.
// Or we can manually append a server with port 25814 to servers.json to match user context exactly.

$servers = json_decode(file_get_contents('servers.json'), true);
$targetId = 'debug_server_25814';
$found = false;
foreach ($servers as $s) {
    if ($s['id'] === $targetId) {
        $found = true; 
        break;
    }
}

if (!$found) {
    $servers[] = [
        'id' => $targetId,
        'user_id' => 1,
        'name' => 'Debug Server',
        'type' => 'java',
        'ip' => '127.0.0.1',
        'port' => 25814,
        'status' => 'offline',
        'pid' => null,
        'version' => '1.21.4',
        'device_token' => 'debug25814',
        'public_ip' => null,
        'created_at' => date('Y-m-d H:i:s')
    ];
    file_put_contents('servers.json', json_encode($servers, JSON_PRETTY_PRINT));
    echo "Created debug server 25814\n";
}

// Now try to start it
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
                 $comm = file_get_contents("/proc/" . $s['pid'] . "/comm");
                 echo "Comm: " . trim($comm) . "\n";
                 
                 // Also check cmdline for the arguments
                 $cmdline = file_get_contents("/proc/" . $s['pid'] . "/cmdline");
                 echo "Cmdline: " . str_replace("\0", " ", $cmdline) . "\n";
             }
        }
    }
}
?>
