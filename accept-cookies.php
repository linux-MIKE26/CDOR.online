<?php
require __DIR__ . '/app/config/bootstrap.php'; // Incluimos esto para poder chequear sesión si queremos

// Detectar si estamos en HTTPS o no
$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

setcookie(
    'cookies_accepted', // Nombre
    'yes',              // Valor
    [
        'expires'  => time() + 31536000, // 1 año
        'path'     => '/',               // Disponible en toda la web
        'domain'   => '',                
        'secure'   => $isSecure,         
        'httponly' => true,              
        'samesite' => 'Lax'
    ]
);

// --- LÓGICA DE REDIRECCIÓN MEJORADA ---
$referer = $_SERVER['HTTP_REFERER'] ?? '/';

// Si el usuario viene de la página de privacidad, NO lo mandes de vuelta a privacidad
// porque parecerá que el botón no hizo nada. Mándalo al Dashboard o al Home.
if (strpos($referer, 'privacy.php') !== false) {
    if (isset($_SESSION['user'])) {
        header("Location: /dashboard.php");
    } else {
        header("Location: /");
    }
} else {
    // Si viene del popup (footer), recarga la página donde estaba
    header("Location: $referer");
}
exit;