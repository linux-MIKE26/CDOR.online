<?php
// CDOR v5.0 HIGH VOLTAGE - CONTACT CONTROLLER + CLOUDFLARE TURNSTILE
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 🛡️ CYBER-SHIELD: Layer 0 - CLOUDFLARE TURNSTILE VALIDATION
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';
    $secretKey = "0x4AAAAAACLr_od2sVhEV_RZyR_L5NB_dQg";

    if (empty($turnstileResponse)) {
        error_log("BLOCK: Missing Turnstile token.");
        die("<h1>❌ SECURITY ALERT</h1><p>Verificación de seguridad requerida.</p>");
    }

    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'secret' => $secretKey,
        'response' => $turnstileResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);

    $outcome = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!$outcome['success']) {
        error_log("BLOCK: Turnstile verification failed.");
        die("<h1>❌ SECURITY ALERT</h1><p>Fallo en la verificación de seguridad de Cloudflare.</p>");
    }

    // 🛡️ CYBER-SHIELD: Layer 1 - HONEYPOT
    if (!empty($_POST['website_url']) || !empty($_POST['contact_me_by_fax_only'])) {
        error_log("BOT DETECTED: Honeypot triggered.");
        header("Location: ../../contact.php?sent=1"); // Fake success
        exit();
    }

    // 🛡️ CYBER-SHIELD: Layer 2 - TIME CHECK
    $loadTime = (int) ($_POST['form_load_time'] ?? 0);
    $submitTime = time();
    if (($submitTime - $loadTime) < 2) { // Adjusted to 2s because Turnstile takes some time
        error_log("BOT DETECTED: Fast submission");
        header("Location: ../../contact.php?sent=1");
        exit();
    }

    // DATA CLEANING
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'System Inquiry');
    $message = trim($_POST['message'] ?? '');

    // 🛡️ CYBER-SHIELD: Layer 3 - KEYWORD FILTERING
    $forbidden = ['backlinks', 'skyrocket', 'ranking terms', 'semrush-backlinks', 'strictlydigital', 'searchregister.net'];
    $content = strtolower($message . ' ' . $subject);
    foreach ($forbidden as $word) {
        if (strpos($content, $word) !== false) {
            error_log("SPAM DETECTED: Keyword match '$word'");
            header("Location: ../../contact.php?sent=1");
            exit();
        }
    }

    // PROCESS VALID MESSAGE
    if (!empty($name) && !empty($email) && !empty($message)) {
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
        }

        $to = "ceo@cdor.online";
        $headers = "From: web@cdor.online\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $txt = "--- NUEVO CONTACTO DE CDOR.ONLINE ---\n\n";
        $txt .= "IDENTIDAD: $name\n";
        $txt .= "EMAIL: $email\n";
        $txt .= "ASUNTO: $subject\n\n";
        $txt .= "MENSAJE:\n$message\n\n";
        $txt .= "--- FIN DEL PROTOCOLO ---";

        @mail($to, "CDOR CONTACT: $subject", $txt, $headers);

        header("Location: ../../contact.php?sent=1");
        exit();
    }
}

header("Location: ../../contact.php?error=access_denied");
exit();
