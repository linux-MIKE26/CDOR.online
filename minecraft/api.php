<?php
// CDOR Minecraft Hosting - Standard API
session_start();
header('Content-Type: application/json');

// 1. Config & Auth
require_once __DIR__ . '/../app/config/bootstrap.php';
$dataFile = __DIR__ . '/servers.json';
$queueFile = __DIR__ . '/commands.json';

function getServers() {
    global $dataFile;
    if (!file_exists($dataFile)) return [];
    $data = json_decode(file_get_contents($dataFile), true);
    return is_array($data) ? $data : [];
}

function saveServers($servers) {
    global $dataFile;
    file_put_contents($dataFile, json_encode(array_values($servers), JSON_PRETTY_PRINT));
}

function checkAuth() {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $_SESSION['user']['id'];
}

// 2. Process Monitoring (Improved)
function isProcessRunning($pid) {
    if (empty($pid) || !is_numeric($pid)) return false;
    // Check if process exists using signal 0 (requires permissions, might be restricted)
    if (function_exists('posix_kill')) return @posix_kill((int)$pid, 0);
    // Fallback to ps
    $out = [];
    @exec("ps -p " . (int)$pid, $out);
    return count($out) > 1;
}

// 3. Command Queue Helper
function addCommand($serverId, $action, $params = []) {
    global $queueFile;
    $queue = file_exists($queueFile) ? json_decode(file_get_contents($queueFile), true) : [];
    if (!is_array($queue)) $queue = [];
    
    // Cleanup old commands
    $now = time();
    $queue = array_filter($queue, function($c) use ($now) {
        return ($now - ($c['timestamp'] ?? 0)) < 3600;
    });

    $queue[] = [
        'id' => uniqid('cmd_'),
        'server_id' => $serverId,
        'action' => strtoupper($action),
        'params' => $params,
        'status' => 'pending',
        'timestamp' => $now
    ];
    file_put_contents($queueFile, json_encode(array_values($queue), JSON_PRETTY_PRINT));
}

// 4. API Actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $userId = checkAuth();
        $servers = getServers();
        $filtered = array_filter($servers, fn($s) => $s['user_id'] == $userId);
        echo json_encode(array_values($filtered));
        break;

    case 'create':
        $userId = checkAuth();
        $servers = getServers();
        
        // Limit: 1 server for MVP
        $userServers = array_filter($servers, fn($s) => $s['user_id'] == $userId);
        if (count($userServers) >= 1) {
            echo json_encode(['error' => 'Límite alcanzado (Max 1 servidor en demo)']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? 'Mi Servidor';
        
        $newServer = [
            'id' => uniqid('srv_'),
            'user_id' => $userId,
            'name' => $name,
            'port' => 25565,
            'version' => '1.21.4',
            'status' => 'offline',
            'public_ip' => '',
            'device_token' => bin2hex(random_bytes(16)),
            'last_seen' => 0,
            'created_at' => time()
        ];
        
        $servers[] = $newServer;
        saveServers($servers);
        echo json_encode(['success' => true, 'server' => $newServer]);
        break;

    case 'delete':
        $userId = checkAuth();
        $serverId = $_GET['id'] ?? '';
        $servers = getServers();
        $servers = array_filter($servers, function($s) use ($serverId, $userId) {
            return !($s['id'] === $serverId && $s['user_id'] == $userId);
        });
        saveServers($servers);
        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $userId = checkAuth();
        $serverId = $_GET['id'] ?? '';
        $command = $_GET['command'] ?? '';
        
        $servers = getServers();
        foreach ($servers as &$s) {
            if ($s['id'] === $serverId && $s['user_id'] == $userId) {
                if ($command === 'start') {
                    // Start order
                    addCommand($serverId, 'START');
                    $s['status'] = 'starting';
                } elseif ($command === 'stop') {
                    addCommand($serverId, 'STOP');
                    $s['status'] = 'stopping';
                } elseif ($command === 'install') {
                    addCommand($serverId, 'INSTALL', ['version' => $_GET['version'] ?? '1.21.4']);
                }
                saveServers($servers);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['error' => 'Server not found']);
        break;

    case 'status':
        $serverId = $_GET['id'] ?? '';
        $servers = getServers();
        foreach ($servers as $s) {
            if ($s['id'] === $serverId) {
                $now = time();
                $isLocalActive = ($now - ($s['last_seen'] ?? 0)) < 20;
                $s['node_active'] = $isLocalActive;
                echo json_encode($s);
                exit;
            }
        }
        echo json_encode(['error' => 'Not found']);
        break;

    case 'client_poll':
        $token = $_GET['token'] ?? '';
        $servers = getServers();
        $target = null;
        foreach ($servers as $s) {
            if (($s['device_token'] ?? '') === $token) {
                $target = $s; break;
            }
        }
        if (!$target) { echo json_encode(['error' => 'Invalid token']); exit; }

        $queue = file_exists($queueFile) ? json_decode(file_get_contents($queueFile), true) : [];
        $pending = [];
        $newQueue = [];
        foreach ($queue as $cmd) {
            if ($cmd['server_id'] === $target['id'] && $cmd['status'] === 'pending') {
                $pending[] = $cmd;
                $cmd['status'] = 'processing';
            }
            $newQueue[] = $cmd;
        }
        file_put_contents($queueFile, json_encode(array_values($newQueue), JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'commands' => $pending]);
        break;

    case 'client_update':
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $servers = getServers();
        foreach ($servers as &$s) {
            if (($s['device_token'] ?? '') === $token) {
                $s['status'] = $input['status'] ?? $s['status'];
                $s['last_seen'] = time();
                if (isset($input['public_ip'])) $s['public_ip'] = $input['public_ip'];
                saveServers($servers);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        break;

    case 'client_log':
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $logs = $input['logs'] ?? '';
        $servers = getServers();
        foreach ($servers as $s) {
            if (($s['device_token'] ?? '') === $token) {
                $logFile = __DIR__ . '/server_data/' . $s['id'] . '/server.log';
                if (!file_exists(dirname($logFile))) @mkdir(dirname($logFile), 0755, true);
                file_put_contents($logFile, $logs); 
                echo json_encode(['success' => true]);
                exit;
            }
        }
        break;

    case 'fetch_logs':
        $serverId = $_GET['id'] ?? '';
        $logFile = __DIR__ . '/server_data/' . $serverId . '/server.log';
        if (file_exists($logFile)) {
            echo json_encode(['success' => true, 'logs' => file_get_contents($logFile)]);
        } else {
            echo json_encode(['success' => false, 'logs' => 'Esperando servidor...']);
        }
        exit;

    case 'get_token':
        $userId = checkAuth();
        $serverId = $_GET['id'] ?? '';
        $servers = getServers();
        foreach ($servers as $s) {
            if ($s['id'] === $serverId && $s['user_id'] == $userId) {
                echo json_encode(['success' => true, 'token' => $s['device_token']]);
                exit;
            }
        }
        break;

    case 'download_client':
        $userId = checkAuth();
        $serverId = $_GET['id'] ?? '';
        $servers = getServers();
        foreach ($servers as $s) {
            if ($s['id'] === $serverId && $s['user_id'] == $userId) {
                header('Content-Type: application/javascript');
                header('Content-Disposition: attachment; filename="cdor_client.js"');
                $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $baseUrl = $proto . '://' . $_SERVER['HTTP_HOST'] . '/minecraft/api.php';
                echo "const DEVICE_TOKEN = '{$s['device_token']}';\nconst API_URL = '{$baseUrl}';\n\n";
                echo file_get_contents(__DIR__ . '/dummy_client.js');
                exit;
            }
        }
        break;

    case 'check_sys':
        echo json_encode(['java' => shell_exec("java -version 2>&1") ?: 'No', 'os' => PHP_OS, 'user' => get_current_user()]);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
