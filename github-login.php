<?php
require __DIR__ . '/app/config/bootstrap.php';

// Configura aquí tus credenciales reales
$clientId = "Ov23liCePlT5V7Pf9bfY"; 
$redirectUri = "https://cdor.online/github-callback.php";

// Generar estado aleatorio para seguridad CSRF
$_SESSION['oauth2state'] = bin2hex(random_bytes(16));

$url = "https://github.com/login/oauth/authorize?client_id={$clientId}&redirect_uri={$redirectUri}&scope=user:email&state={$_SESSION['oauth2state']}";

header("Location: $url");
exit;