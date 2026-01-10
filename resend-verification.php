<?php
require __DIR__ . '/app/config/bootstrap.php';

$msg = "";
$msgType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email) {
        $stmt = $pdo->prepare("SELECT id, name, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $msg = "No encontramos ninguna cuenta con ese correo.";
            $msgType = "error";
        } elseif ($user['email_verified']) {
            $msg = "Esta cuenta ya está verificada. <a href='/login.php' style='color:inherit;text-decoration:underline'>Inicia Sesión</a>.";
            $msgType = "success";
        } else {
            $code = random_int(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?")
                ->execute([$code, $expires, $user['id']]);

            // --- SEND EMAIL (PREMIUM) ---
            require_once __DIR__ . '/app/utils/EmailDesign.php';
            $subject = "CDOR // Nuevo Código de Acceso";
            $domain = "https://cdor.online";
            $verifyLink = "$domain/verify.php?email=" . urlencode($email) . "&code=$code";

            $msgBody = "
            <p>Has solicitado un nuevo código de acceso para tu cuenta en CDOR.</p>
            <div style='background:#111; padding:25px; font-size:40px; letter-spacing:8px; font-weight:bold; margin:30px 0; border:1px solid #333; color:#FFD700; text-align:center;'>
                $code
            </div>
            <p>Este código es válido por 60 minutos.</p>
            ";

            EmailDesign::send($email, $subject, "REGENERACIÓN DE CÓDIGO", $msgBody, "VERIFICAR AHORA", $verifyLink);

            header("Location: /verify.php?email=" . urlencode($email));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php $title = "Reenviar Código · CDOR";
    include __DIR__ . '/partials/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/partials/menu.php'; ?>

    <main class="auth-wrapper">
        <div class="glass-card auth-card animate-reveal text-center">
            <div class="auth-branding">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔄</div>
            </div>

            <h1 class="auth-title">Reenviar Código</h1>
            <p class="auth-subtitle">
                Te enviaremos un nuevo código de acceso.
            </p>

            <?php if ($msg): ?>
                <div class="<?= $msgType == 'error' ? 'error-alert' : 'success-alert' ?>"
                    style="<?= $msgType != 'error' ? 'background: rgba(16,185,129,0.15); color: #34d399; padding:12px; border-radius:8px; margin-bottom:20px;' : '' ?>">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" style="text-align: left;">Correo Electrónico</label>
                    <input type="email" name="email" class="form-input" placeholder="ejemplo@cdor.online" required>
                </div>
                <button type="submit" class="btn btn-block">
                    Enviar Nuevo Código
                </button>
            </form>

            <div class="mt-4">
                <a href="/login.php" class="btn-link">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>

</html>