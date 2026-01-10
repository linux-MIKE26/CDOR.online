<?php
// Cookie técnica segura (IONOS + HTTPS + móviles)
setcookie(
    'cookies_accepted',
    'yes',
    [
        'expires'  => time() + 365 * 24 * 60 * 60,
        'path'     => '/',
        'domain'   => 'cdor.online',
        'secure'   => true,
        'httponly' => false,
        'samesite' => 'Lax'
    ]
);

// Redirigir de vuelta
header("Location: /");
exit;
