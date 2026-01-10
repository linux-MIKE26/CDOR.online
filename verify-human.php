<?php
// CDOR v5.0 HIGH VOLTAGE - HUMAN VERIFICATION (TURNSTILE)
require_once __DIR__ . '/app/config/bootstrap.php';

// If already verified, move on
if (isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] === 'yes' && !isset($_GET['force'])) {
    header("Location: " . ($_GET['return'] ?? '/'));
    exit;
}

$title = "Verificación Humana // CDOR";
include __DIR__ . '/partials/head.php';
?>

<style>
    body {
        background: #020202;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .verify-box {
        max-width: 450px;
        width: 90%;
        padding: 50px;
        text-align: center;
        border: 1px solid var(--primary);
    }

    .cf-turnstile-container {
        display: flex;
        justify-content: center;
        margin: 30px 0;
    }
</style>

<div class="verify-box glass-card">
    <h2 style="color:var(--primary); margin-bottom: 10px;">VERIFICACIÓN</h2>
    <p style="color:var(--text-dim); margin-bottom: 30px;">
        Confirma que no eres un sistema automatizado para acceder al núcleo de CDOR.
    </p>

    <form action="/app/controllers/verify-consent.php" method="POST">
        <?= csrf_field() ?>
        <?php if (isset($_GET['return'])): ?>
            <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return']) ?>">
        <?php endif; ?>

        <div class="cf-turnstile-container">
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACLr_hGrMjaG9HkI" data-theme="dark"
                data-callback="onVerify"></div>
        </div>

        <div id="status-msg" style="margin-top: 20px; font-weight: 700; color: var(--primary); display:none;">
            [ COMPROBANDO CREDENCIALES... ]
        </div>

        <button type="submit" id="submit-btn" class="btn btn-block" style="display:none; margin-top: 20px;">
            CONTINUAR AL SISTEMA
        </button>
    </form>
</div>

<script>
    function onVerify(token) {
        document.getElementById('status-msg').style.display = 'block';
        // Auto-submit after briefly showing the status
        setTimeout(() => {
            document.querySelector('form').submit();
        }, 1000);
    }
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>