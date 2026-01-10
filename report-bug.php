<?php
require __DIR__ . '/app/config/bootstrap.php';
$title = "Reportar Bug · CDOR";
include __DIR__ . '/partials/head.php';
?>
<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="container" style="padding: 80px 20px; min-height: 80vh; display:flex; justify-content:center; align-items:center;">
    <div class="glass-card animate-reveal" style="max-width: 600px; width:100%;">
        <div class="text-center mb-4">
            <h1 class="section-title" style="font-size:2rem;">¿Encontraste un Bug?</h1>
            <p class="text-muted">Ayúdanos a mejorar. Describe el error y lo solucionaremos.</p>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="success-alert">
                <i class="fa-solid fa-check-circle"></i> ¡Gracias! Reporte enviado al CEO.
            </div>
        <?php endif; ?>

        <form action="/send-bug.php" method="POST">
            <div class="form-group">
                <label class="form-label">TU CORREO (OPCIONAL)</label>
                <div class="input-wrapper">
                    <input type="email" name="email" class="form-input with-icon" placeholder="para contactarte...">
                    <i class="fa-solid fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">DESCRIPCIÓN DEL ERROR</label>
                <textarea name="message" class="form-input" style="min-height:150px;" required placeholder="¿Qué pasó y dónde?"></textarea>
            </div>

            <button type="submit" class="btn btn-block btn-primary-glow">
                <i class="fa-solid fa-bug"></i> REPORTAR ERROR
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="/" class="btn-link">Volver al Inicio</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
