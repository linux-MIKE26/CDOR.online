<?php
require_once __DIR__ . '/app/config/bootstrap.php';

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Usuario';
$userEmail = $user['email'] ?? 'Sin email';
$userRole = $user['role'] ?? 'user';
$isVerified = isset($user['email_verified']) ? $user['email_verified'] : 0;
$nameInitial = !empty($userName) ? strtoupper(substr($userName, 0, 1)) : 'U';

$title = "Mi Perfil · CDOR";
?>
<?php include __DIR__ . '/partials/head.php'; ?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="container" style="padding-top: 40px; padding-bottom: 40px;">

    <!-- HEADER SECTION -->
    <div class="glass-card animate-reveal" style="margin-bottom: 30px;">
        <div class="flex-between" style="align-items: flex-start; flex-wrap: wrap; gap: 20px;">

            <div style="display: flex; gap: 24px; align-items: center;">
                <div style="
                    width: 80px; height: 80px; 
                    background: linear-gradient(135deg, var(--primary), var(--accent)); 
                    border-radius: 50%; 
                    display: flex; align-items: center; justify-content: center; 
                    font-size: 2.5rem; font-weight: 800; color: #000;
                    box-shadow: 0 0 20px var(--primary-glow);
                ">
                    <?= htmlspecialchars($nameInitial) ?>
                </div>

                <div>
                    <h1 style="font-size: 2rem; margin-bottom: 4px;"><?= htmlspecialchars($userName) ?></h1>
                    <p class="text-muted" style="margin-bottom: 12px;"><?= htmlspecialchars($userEmail) ?></p>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if ($isVerified): ?>
                            <span
                                style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                                VERIFICADO
                            </span>
                        <?php else: ?>
                            <span
                                style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3);">
                                NO VERIFICADO
                            </span>
                        <?php endif; ?>

                        <?php if ($userRole === 'admin'): ?>
                            <span
                                style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: rgba(245, 158, 11, 0.15); color: var(--primary); border: 1px solid var(--primary);">
                                ADMINISTRADOR
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <a href="/logout.php" class="btn btn-outline" style="font-size: 0.8rem; padding: 10px 20px;">
                Cerrar Sesión
            </a>
        </div>
    </div>

    <!-- ACTION NEEDED -->
    <?php if (!$isVerified): ?>
        <div class="glass-card animate-reveal delay-100"
            style="border-color: rgba(239, 68, 68, 0.5); background: rgba(220, 38, 38, 0.05);">
            <div class="flex-between" style="flex-wrap: wrap; gap: 20px;">
                <div>
                    <h3 style="color: #fca5a5; margin-bottom: 8px;">⚠️ Activación Requerida</h3>
                    <p style="color: #e4e4e7;">Tu cuenta está limitada. Verifica tu correo para acceder a todas las
                        funciones.</p>
                </div>
                <form action="/resend-verification.php" method="POST" style="margin:0;">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($userEmail) ?>">
                    <button type="submit" class="btn"
                        style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5;">
                        Reenviar Código
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-card animate-reveal delay-100">
            <h3 style="margin-bottom: 15px;">Estado de la Cuenta</h3>
            <p class="text-muted">
                Bienvenido a tu panel de control, <strong><?= htmlspecialchars($userName) ?></strong>. <br>
                Desde aquí podrás gestionar tus proyectos y configuraciones (Próximamente).
            </p>
        </div>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/partials/footer.php'; ?>