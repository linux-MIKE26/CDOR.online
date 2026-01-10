<?php
require_once __DIR__ . '/app/config/bootstrap.php';
$title = "Contacto // Mike Corredor";
require_once __DIR__ . '/partials/head.php';
?>
<?php include __DIR__ . '/partials/menu.php'; ?>

<section class="hero container" style="min-height: 40vh; padding-bottom: 0;">
    <div class="animate-reveal">
        <h1 class="hero-title">CONEXIÓN <span class="text-gradient">SEGURA</span></h1>
        <p class="hero-subtitle">
            Canal directo para propuestas de arquitectura, consultoría y desarrollo.
        </p>
    </div>
</section>

<main class="container" style="padding-bottom: 80px;">
    <div class="glass-card animate-reveal delay-100" style="max-width: 800px; margin: 0 auto;">

        <?php if (isset($_GET['sent'])): ?>
            <div class="success-alert"
                style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; padding: 20px; border-radius: var(--radius-sm); margin-bottom: 30px; text-align: center;">
                <h3 style="margin-bottom: 5px;">✅ Mensaje Enviado</h3>
                <p style="font-size: 0.9rem;">Analizaré tu propuesta y responderé en breve.</p>
            </div>
        <?php endif; ?>

        <form action="app/controllers/contact.php" method="POST">
            <?php csrf_field(); ?>
            <div class="grid-3" style="margin: 0 0 24px; gap: 24px; grid-template-columns: 1fr 1fr;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">IDENTIDAD / EMPRESA</label>
                    <input type="text" name="name" class="form-input" placeholder="Nombre completo" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">EMAIL CORPORATIVO</label>
                    <input type="email" name="email" class="form-input" placeholder="nombre@empresa.com" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ASUNTO</label>
                <input type="text" name="subject" class="form-input" placeholder="Consultoría de Arquitectura..."
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">PROTOCOLO DE MENSAJE</label>

                <!-- CYBER-SHIELD: TRAP FIELDS FOR BOTS -->
                <div style="display:none !important;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    <input type="checkbox" name="contact_me_by_fax_only" tabindex="-1" autocomplete="off">
                    <input type="hidden" name="form_load_time" value="<?= time() ?>">
                </div>

                <textarea name="message" class="form-input" rows="6" placeholder="Detalles técnicos de la solicitud..."
                    required></textarea>
            </div>

            <div class="form-group" style="display: flex; justify-content: center; margin-bottom: 24px;">
                <!-- Cloudflare Turnstile Widget -->
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <div class="cf-turnstile" data-sitekey="0x4AAAAAACLr_hGrMjaG9HkI" data-theme="dark"></div>
            </div>

            <button type="submit" class="btn btn-block" style="min-height:56px; font-size:1.1rem;">
                ENVIAR MENSAJE
            </button>
        </form>

        <div class="text-center mt-4 text-muted">
            <small>Encripción SSL activa. Tus datos permanecen confidenciales.</small>
        </div>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>