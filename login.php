<?php
require __DIR__ . '/app/config/bootstrap.php';

if (isset($_SESSION['user'])) {
    $redirect = ($_SESSION['user']['role'] === 'admin') ? '/admin/admin-dashboard.php' : '/dashboard.php';
    header("Location: $redirect");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        // Fetch user with is_banned column
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 1. BAN CHECK
            if (isset($user['is_banned']) && $user['is_banned'] == 1) {
                $error = 'ACCESO DENEGADO: Tu cuenta ha sido suspendida permanentemente.';
            }
            // 2. PASSWORD CHECK
            elseif (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'user',
                    'email_verified' => $user['email_verified']
                ];

                if ($user['email_verified'] == 0) {
                    header("Location: /verify.php?email=" . urlencode($user['email']));
                    exit;
                }

                $redirect = ($_SESSION['user']['role'] === 'admin') ? '/admin/admin-dashboard.php' : '/dashboard.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<?php $title = "Acceso · CDOR";
include __DIR__ . '/partials/head.php'; ?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="auth-wrapper">
    <!-- FIX: High z-index to stay above potential overlays -->
    <div class="glass-card auth-card animate-reveal" style="position: relative; z-index: 100;">
        <div class="auth-branding">
            <div class="auth-logo-icon">⚡</div>
        </div>

        <h1 class="auth-title">Bienvenido</h1>
        <p class="auth-subtitle">Accede a tu panel de control profesional</p>

        <?php if ($error): ?>
            <div class="error-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email" class="form-label">CORREO ELECTRÓNICO</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" class="form-input with-icon"
                        placeholder="nombre@ejemplo.com" required>
                    <i class="fa-solid fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">CONTRASEÑA</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input with-icon"
                        placeholder="••••••••" required>
                    <i class="fa-solid fa-lock input-icon"></i>
                </div>
            </div>

            <?php csrf_field(); ?>

            <button type="submit" class="btn btn-block btn-primary-glow">
                <span>INICIAR SESIÓN</span> <i class="fa-solid fa-arrow-right" style="margin-left: 10px;"></i>
            </button>
        </form>

        <div class="divider">
            <span>o continúa con</span>
        </div>

        <a href="/github-login.php" class="btn btn-github btn-block">
            <i class="fa-brands fa-github" style="font-size: 1.2rem; margin-right: 10px;"></i>
            GitHub
        </a>

        <div class="text-center mt-4">
            <a href="/register.php" class="btn-link">Crear cuenta nueva</a>
            <span style="margin: 0 10px; color: var(--text-dim);">·</span>
            <a href="/resend-verification.php" style="color: var(--text-muted); font-size: 0.9rem;">Reenviar código</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>