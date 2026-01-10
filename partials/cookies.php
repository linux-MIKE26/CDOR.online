<?php
// Verificar si la cookie existe
$cookieAccepted = isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] === 'yes';

// Detectar si estamos en la página de privacidad
$isPrivacyPage = basename($_SERVER['PHP_SELF']) === 'privacy.php';

if (!$cookieAccepted): ?>

    <?php
    // Solo aplicamos el efecto BORROSO si NO estamos en la página de privacidad.
    // Así el usuario puede leer la política antes de aceptar.
    if (!$isPrivacyPage): ?>
        <script>
            document.body.classList.add('cookies-pending');
        </script>
    <?php endif; ?>

    <div class="cookie-modal">
        <div class="cookie-content">
            <h3>🍪 Privacidad y Seguridad</h3>
            <p>
                Este sistema requiere la aceptación de cookies técnicas para garantizar la seguridad y el funcionamiento del
                núcleo de CDOR.
            </p>
        </div>
        <div class="cookie-actions">
            <a href="/cookie-consent.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-submit">CONFIGURAR
                ACCESO</a>
        </div>
    </div>
<?php endif; ?>