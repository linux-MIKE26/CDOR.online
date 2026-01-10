<?php
require __DIR__ . '/app/config/bootstrap.php';

echo "<h1>Reparando Base de Datos...</h1>";

try {
    // 1. INTENTAR AGREGAR LA COLUMNA 'role'
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
        echo "<p>✅ Columna 'role' creada.</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ La columna 'role' ya existía o no se pudo crear (Posiblemente ya esté).</p>";
    }

    // 2. INTENTAR AGREGAR LA COLUMNA 'verification_code' (Por si acaso)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_expires DATETIME DEFAULT NULL");
        echo "<p>✅ Columnas de verificación creadas.</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ Columnas de verificación ya existían.</p>";
    }

    // 3. CREAR O ACTUALIZAR EL ADMIN
    $email = 'ceo@cdor.online';
    $pass = 'Alejo8397';
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Verificamos si existe el usuario
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Actualizamos
        $sql = "UPDATE users SET role = 'admin', password = '$hash' WHERE email = '$email'";
        $pdo->exec($sql);
        echo "<p>✅ Usuario $email actualizado a ADMIN correctamente.</p>";
    } else {
        // Creamos
        $sql = "INSERT INTO users (name, email, password, role, email_verified) VALUES ('CEO Admin', '$email', '$hash', 'admin', 1)";
        $pdo->exec($sql);
        echo "<p>✅ Usuario Admin creado desde cero.</p>";
    }

    echo "<hr><h3>¡Listo! Ahora borra este archivo y prueba entrar.</h3>";
    echo '<a href="/login.php">Ir al Login</a>';

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error Fatal: " . $e->getMessage() . "</h3>";
}
?>