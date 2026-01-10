<?php
require __DIR__ . '/app/config/bootstrap.php';

// Si ya está logueado, fuera
if (isset($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $message = 'Por favor, rellena todos los campos.';
        $msgType = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = 'Este correo ya registrado. Inicia sesión.';
            $msgType = 'error';
        } else {
            $code = random_int(100000, 999999);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $sql = "INSERT INTO users (name, email, password, verification_code, verification_expires, email_verified, role) 
                    VALUES (?, ?, ?, ?, ?, 0, 'user')";

            try {
                $pdo->prepare($sql)->execute([$name, $email, $hash, $code, $expires]);

                // --- CONFIGURACIÓN DEL CORREO (PREMIUM) ---
                require_once __DIR__ . '/app/utils/EmailDesign.php';

                $to = $email;
                $subject = "CDOR // CÓDIGO DE VERIFICACIÓN";
                $domain = "https://cdor.online";
                $verifyLink = "$domain/verify.php?email=" . urlencode($email) . "&code=$code";

                $msgBody = "
                <p>Bienvenido al Núcleo de Operaciones de CDOR.</p>
                <p>Has iniciado el proceso de registro. Para activar tu identidad en el sistema, utiliza el siguiente código de autorización de un solo uso:</p>
                <div style='background:#111; padding:25px; font-size:40px; letter-spacing:8px; font-weight:bold; margin:30px 0; border:1px solid #333; color:#FFD700; text-align:center;'>
                    $code
                </div>
                <p>O utiliza el enlace directo a continuación para completar la verificación automáticamente.</p>
                ";

                EmailDesign::send($to, $subject, "VERIFICACIÓN REQUERIDA", $msgBody, "VERIFICAR CUENTA", $verifyLink);

                header("Location: /verify.php?email=" . urlencode($email));
                exit;

            } catch (PDOException $e) {
                // Log actual error for debug purposes if needed, show generic to user
                $message = "Error del sistema. Inténtalo más tarde.";
                $msgType = 'error';
            }
        }
    }
}
?>
<?php $title = "Crear Cuenta · CDOR";
include __DIR__ . '/partials/head.php'; ?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="auth-wrapper">
    <!-- FIX: High z-index to stay above potential overlays -->
    <div class="glass-card auth-card animate-reveal" style="position: relative; z-index: 100;">
        <div class="auth-branding">
            <div class="auth-logo-icon">⚡</div>
        </div>

        <h1 class="auth-title">Crear Cuenta</h1>
        <p class="auth-subtitle">Únete a mi laboratorio personal</p>

        <?php if ($message): ?>
            <div class="<?= $msgType == 'error' ? 'error-alert' : 'success-alert' ?>"
                style="<?= $msgType != 'error' ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px;' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Nombre Clave</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Ej. Alex Corredor" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="nombre@dominio.com"
                    required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <?php csrf_field(); ?>

            <button type="submit" class="btn btn-block">Iniciar Registro</button>
        </form>

        <div class="divider">
            <span>o regístrate con</span>
        </div>

        <a href="/github-login.php" class="btn btn-github btn-block">
            <i class="fa-brands fa-github" style="font-size: 1.2rem; margin-right: 10px;"></i>
            GitHub Access
        </a>

        <div class="text-center mt-4">
            <span style="color: var(--text-muted);">¿Ya tienes cuenta?</span>
            <a href="/login.php" class="btn-link" style="margin-left: 5px;">Acceder aquí</a>
        </div>

    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>