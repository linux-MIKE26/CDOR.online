<?php
$host = "db5019199106.hosting-data.io";
$dbname = "dbs15072153";
$user = "dbu263604";
$pass = "Alejo8397";
$port = 3306;

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            // ESTA LÍNEA ES LA MAGIA: Timeout aumentado a 10s para conexiones lentas
            PDO::ATTR_TIMEOUT => 10 
        ]
    );
} catch (PDOException $e) {
    // MODO FALLBACK: No matamos la app, permitimos modo offline
    // die("Error de Conexión (DB): " . $e->getMessage());
    $pdo = null;
    $db_error = $e->getMessage();
}