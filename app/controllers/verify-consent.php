<?php
// CDOR v5.0 HIGH VOLTAGE - CONSENT CONTROLLER
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Turnstile
    $token = $_POST['cf-turnstile-response'] ?? '';
    $secret = "0x4AAAAAACLr_od2sVhEV_RZyR_L5NB_dQg";

    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    $outcome = json_decode(curl_exec($ch), true);

    if ($outcome['success']) {
        // Set Cookie
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie(
            'cookies_accepted',
            'yes',
            [
                'expires' => time() + 31536000,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        $return = $_POST['return'] ?? '/';
        header("Location: $return");
        exit;
    }
}

header("Location: /verify-human.php?error=failed");
exit;
