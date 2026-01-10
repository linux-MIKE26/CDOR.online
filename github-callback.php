<?php
require __DIR__ . '/app/config/bootstrap.php';

// CONFIGURACIÓN
$clientId = "Ov23liCePlT5V7Pf9bfY";
$clientSecret = "fb6a8031b56557f3255703127109d13a20a7feb8";

// 1. Verificar Estado CSRF
if (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    if (isset($_SESSION['oauth2state'])) unset($_SESSION['oauth2state']);
    die("Error de seguridad (CSRF). Por favor, vuelve a intentar iniciar sesión.");
}

$code = $_GET['code'] ?? null;
if (!$code) die("Error: GitHub no devolvió el código de autorización.");

// FUNCIONES HELPER CURL (Para evitar errores SSL en hosting compartido)
function curlRequest($url, $postParams = null, $token = null) {
    $ch = curl_init($url);
    $headers = ["Accept: application/json", "User-Agent: CDOR-App"];
    
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // Fix para Ionos/Hosting compartido
        CURLOPT_HTTPHEADER => $headers
    ]);

    if ($postParams) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
    }

    $response = curl_exec($ch);
    if (curl_errno($ch)) die('Error cURL: ' . curl_error($ch));
    curl_close($ch);
    
    return json_decode($response, true);
}

// 2. Obtener Access Token
$tokenData = curlRequest("https://github.com/login/oauth/access_token", [
    "client_id" => $clientId,
    "client_secret" => $clientSecret,
    "code" => $code
]);

$accessToken = $tokenData['access_token'] ?? null;
if (!$accessToken) die("Error obteniendo Token de GitHub.");

// 3. Obtener Datos de Usuario
$ghUser = curlRequest("https://api.github.com/user", null, $accessToken);

// 4. Obtener Email
$email = $ghUser['email'] ?? null;
if (!$email) {
    $emails = curlRequest("https://api.github.com/user/emails", null, $accessToken);
    if (is_array($emails)) {
        foreach ($emails as $e) {
            if (($e['primary'] ?? false) && ($e['verified'] ?? false)) {
                $email = $e['email'];
                break;
            }
        }
    }
}
// Fallback si no hay email público
if (!$email) $email = ($ghUser['login'] ?? 'user') . "@github.noreply";

// 5. Lógica de Base de Datos (FIXED)
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $localUser = $stmt->fetch();

    if ($localUser) {
        // EL USUARIO EXISTE: Usamos sus datos
        // TRUST UPGRADE: Si entra con GitHub, verificamos su cuenta automÃ¡ticamente
        if (($localUser['email_verified'] ?? 0) == 0) {
            $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?")->execute([$localUser['id']]);
        }

        $userId = $localUser['id'];
        $name = $localUser['name'];
        $role = $localUser['role'] ?? 'user'; 
    } else {
        // EL USUARIO NO EXISTE: Lo creamos
        $name = $ghUser['name'] ?? $ghUser['login'] ?? 'GitHub User';
        $passHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $role = 'user';

        // Aseguramos insertar el rol explícitamente
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, email_verified, role) VALUES (?, ?, ?, 1, ?)");
        $insert->execute([$name, $email, $passHash, $role]);
        $userId = $pdo->lastInsertId();
    }

    // 6. Crear Sesión
    $_SESSION['user'] = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'email_verified' => 1,
        'auth_type' => 'github'
    ];

    // Redirección limpia
    header("Location: /dashboard.php");
    exit;

} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}
?>