<?php
require __DIR__ . '/app/config/bootstrap.php';
$title = "Política de Privacidad · CDOR";
include __DIR__ . '/partials/head.php';
?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="container" style="padding-top: 60px; padding-bottom: 60px; min-height: 80vh;">
    <div class="glass-card animate-reveal" style="max-width: 800px; margin: 0 auto;">
        <h1 class="section-title">Política de Privacidad</h1>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Última actualización: <?= date('d/m/Y') ?></p>

        <section style="margin-bottom: 30px;">
            <h2 style="color: #fff; margin-bottom: 15px;">1. Uso de Cookies Técnicas</h2>
            <p style="color: var(--text-muted); line-height: 1.6;">
                CDOR utiliza <strong>únicamente una cookie técnica</strong> ("cookies_accepted" y "PHPSESSID"). 
                Estas son estrictamente necesarias para el funcionamiento del sistema de Login, GitHub Auth y el Panel de Administración.
                No utilizamos cookies de terceros, rastreadores ni publicidad.
            </p>
        </section>

        <section style="margin-bottom: 30px;">
            <h2 style="color: #fff; margin-bottom: 15px;">2. Almacenamiento de Datos</h2>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Si te registras o usas GitHub Login, almacenamos tu <strong>email y nombre público</strong> únicamente para crear tu perfil.
                Las contraseñas se almacenan cifradas (Bcrypt). Nunca compartimos tus datos con terceros.
            </p>
        </section>
        
        <section style="margin-bottom: 30px;">
            <h2 style="color: #fff; margin-bottom: 15px;">3. Tus Derechos</h2>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Puedes solicitar la eliminación completa de tu cuenta y datos en cualquier momento enviando un correo a través de nuestra sección de contacto.
            </p>
        </section>

        <!-- Botón solo para volver, ya que se acepta en el banner flotante -->
        <div style="text-align: center; margin-top: 40px; border-top: 1px solid var(--card-border); padding-top: 30px;">
            <a href="/" class="btn-nav" style="display: inline-block; text-decoration: none;">← Volver al Inicio</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>