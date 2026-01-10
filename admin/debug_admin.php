<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>CDOR Admin Diagnostics</h1>";

// 1. Check Path
echo "Current Directory: " . __DIR__ . "\n";

// 2. Check Bootstrap
$bootstrap = __DIR__ . '/../app/config/bootstrap.php';
echo "Checking Bootstrap at: $bootstrap ... ";
if (file_exists($bootstrap)) {
    echo "FOUND\n";
    require $bootstrap;
    echo "Bootstrap loaded.\n";
} else {
    echo "NOT FOUND\n";
    die("Cannot continue without bootstrap.");
}

// 3. Check Session (CLI mock)
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session Status: ACTIVE\n";
} else {
    echo "Session Status: INACTIVE (Expected in CLI)\n";
}

// 4. Check DB
echo "Checking Database... ";
if (isset($pdo)) {
    echo "CONNECTED\n";
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "Users count: $count\n";
    } catch (Exception $e) {
        echo "QUERY FAILED: " . $e->getMessage() . "\n";
    }
} else {
    echo "PDO OBJECT MISSING\n";
}

// 5. Check CSS File
$cssPath = __DIR__ . '/../assets/css/style.css';
echo "Checking Style.css at $cssPath ... ";
if (file_exists($cssPath)) {
    echo "FOUND (" . filesize($cssPath) . " bytes)\n";
    $content = file_get_contents($cssPath);
    if (strpos($content, 'ADMIN DASHBOARD PREMIUM') !== false) {
        echo "Admin styles detected.\n";
    } else {
        echo "Admin styles NOT detected.\n";
    }
} else {
    echo "NOT FOUND\n";
}
?>
