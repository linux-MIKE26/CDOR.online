<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../app/config/bootstrap.php';
global $pdo;

if (!$pdo) {
    echo "PDO is null\n";
} else {
    echo "PDO is OK\n";
    try {
        $stmt = $pdo->query("SELECT count(*) FROM users");
        echo "Users: " . $stmt->fetchColumn() . "\n";
    } catch (Exception $e) {
        echo "Query failed: " . $e->getMessage() . "\n";
    }
}
