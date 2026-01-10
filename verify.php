<?php
require __DIR__ . '/app/config/bootstrap.php';

$msg = "";
$msgType = "";
$email = $_GET['email'] ?? '';
$urlCode = $_GET['code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $emailPost = trim($_POST['email'] ?? '');

    if ($code && $emailPost) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_verified = 0");
        $stmt->execute([$emailPost]);
        $user = $stmt->fetch();

        if (!$user) {
            $msg = "Usuario no encontrado o ya verificado."; $msgType = "error";
        } else {
            if (strtotime($user['verification_expires']) < time()) {
                $msg = "El código ha expirado. Solicita uno nuevo."; $msgType = "error";
            } elseif ($user['verification_code'] == $code) {
                // ACTIVAR CUENTA
                $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?")->execute([$user['id']]);
                
                // Iniciar sesión automáticamente CON TODOS LOS DATOS
                $_SESSION['user'] = [
                    'id' => $user['id'], 
                    'name' => $user['name'],
                    'email' => $user['email'], 
                    'role' => $user['role'],
                    'email_verified' => 1
                ];
                
                header("Location: /dashboard.php");
                exit;
            } else {
                $msg = "Código incorrecto."; $msgType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php $title = "Verificar · CDOR"; include __DIR__ . '/partials/head.php'; ?>
    <style>
        .code-input {
            text-align: center; 
            font-size: 2.5rem; 
            letter-spacing: 0.5em; 
            font-weight: 700; 
            height: 80px;
        }
        @media (max-width: 480px) {
            .code-input { 
                font-size: 1.8rem; 
                letter-spacing: 0.2em; 
                height: 60px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="auth-wrapper">
    <div class="glass-card auth-card animate-reveal text-center">
        <h1 class="auth-title">Verificación</h1>
        <p class="auth-subtitle">
            Hemos enviado un código al correo:<br>
            <strong style="color: var(--text-main);"><?= htmlspecialchars($email) ?></strong>
        </p>
        <p style="color: #f59e0b; font-size: 0.9rem; margin-bottom: 20px; background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.2);">
            <i class="fa-solid fa-triangle-exclamation"></i> Revisa tu <strong>Bandeja de SPAM</strong> si no recibes el código.
        </p>
        
        <?php if ($msg): ?>
            <div class="error-alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="form-group">
                <input type="text" name="code" class="form-input code-input" 
                       placeholder="000000" maxlength="6" 
                       value="<?= htmlspecialchars($urlCode) ?>" 
                       required autocomplete="off">
            </div>
            
            <button type="submit" class="btn btn-block">Verificar Ahora</button>
        </form>
        
        <div class="mt-4">
            <a href="/resend-verification.php" class="btn-link">¿No recibiste el código?</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>