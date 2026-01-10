<?php
// CDOR v5.0 HIGH VOLTAGE - COOKIE CONSENT GATEWAY
require_once __DIR__ . '/app/config/bootstrap.php';

$title = "Verificación de Seguridad // CDOR";
$hide_menu = true; // For a cleaner look
include __DIR__ . '/partials/head.php';
?>

<style>
    body {
        background: #020202;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
    }

    .portal-box {
        max-width: 500px;
        width: 90%;
        padding: 60px 40px;
        text-align: center;
        animation: portalIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes portalIn {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .icon-lock {
        font-size: 4rem;
        color: var(--primary);
        margin-bottom: 30px;
        filter: drop-shadow(0 0 20px var(--primary-glow));
    }

    .portal-title {
        font-size: 2rem;
        letter-spacing: 4px;
        margin-bottom: 20px;
    }

    .portal-text {
        color: var(--text-dim);
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .verify-btn {
        width: 100%;
        padding: 18px;
        font-size: 1.1rem;
        background: var(--primary);
        color: #000;
        border: none;
        border-radius: 12px;
        font-family: var(--font-display);
        font-weight: 900;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .verify-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 0 30px var(--primary-glow);
    }
</style>

<div class="portal-box glass-card">
    <div class="icon-lock"><i class="fa-solid fa-shield-halved"></i></div>
    <h1 class="portal-title">CYBER <span style="color:var(--primary)">SHIELD</span></h1>
    <p class="portal-text">
        Este sistema utiliza protocolos de seguridad y cookies técnicas para garantizar la integridad de tu sesión.
        Al continuar, aceptas el uso de cookies obligatorias.
    </p>

    <form action="/verify-human.php" method="GET">
        <?php if (isset($_GET['return'])): ?>
            <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return']) ?>">
        <?php endif; ?>
        <button type="submit" class="verify-btn">
            ACEPTAR Y VERIFICAR IDENTIDAD
        </button>
    </form>

    <div style="margin-top: 30px;">
        <a href="/privacy.php"
            style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
            Leer Política de Privacidad
        </a>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>