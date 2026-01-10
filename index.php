<?php
require_once __DIR__ . '/app/config/bootstrap.php';
$title = "Mike Corredor // Full Stack Developer";
?>
<?php include __DIR__ . '/partials/head.php'; ?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<!-- Hero -->
<section class="hero container">
    <div class="animate-reveal">
        <h1 class="hero-title">
            MIKE CORREDOR<br>
            <span class="text-gradient">FULL STACK DEV</span>
        </h1>
        <p class="hero-subtitle">
            Arquitectura de alto rendimiento, seguridad ofensiva y sistemas escalables.
            <br>Transformando código en experiencias premium.
        </p>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="projects.php" class="btn">Explorar Proyectos</a>
            <a href="contact.php" class="btn btn-outline">Contactar</a>
        </div>
    </div>
</section>

<!-- Stack -->
<main class="container mb-4">
    <div class="glass-card animate-reveal delay-100">
        <h2 class="text-center" style="margin-bottom: 40px;">TECH <span class="text-gradient">ARSENAL</span></h2>

        <div class="grid-3">
            <div class="text-center">
                <div
                    style="font-size: 3rem; margin-bottom: 15px; color: var(--primary); text-shadow: 0 0 15px var(--primary-glow);">
                    <i class="fa-solid fa-bolt"></i></div>
                <h3>Backend Elite</h3>
                <p class="text-muted">
                    PHP 8.2+, Node.js y Python. Arquitecturas limpias y APIs RESTful optimizadas para velocidad.
                </p>
            </div>

            <div class="text-center">
                <div
                    style="font-size: 3rem; margin-bottom: 15px; color: var(--primary); text-shadow: 0 0 15px var(--primary-glow);">
                    <i class="fa-solid fa-shield-halved"></i></div>
                <h3>Seguridad</h3>
                <p class="text-muted">
                    Implementación de estándares OWASP, OAuth2 y auditoría de vulnerabilidades.
                </p>
            </div>

            <div class="text-center">
                <div
                    style="font-size: 3rem; margin-bottom: 15px; color: var(--primary); text-shadow: 0 0 15px var(--primary-glow);">
                    <i class="fa-solid fa-server"></i></div>
                <h3>Data Core</h3>
                <p class="text-muted">
                    MySQL optimizado, Redis para caché de alto rendimiento y soluciones NoSQL.
                </p>
            </div>
        </div>
    </div>
</main>

<!-- CTA -->
<section class="container" style="padding: 80px 0;">
    <div class="glass-card text-center animate-reveal delay-200" style="padding: 60px 20px;">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">¿LISTO PARA INNOVAR?</h2>
        <p class="text-muted" style="max-width: 600px; margin: 0 auto 30px;">
            Si tienes una visión técnica compleja o necesitas escalar tu infraestructura, hablemos.
        </p>
        <a href="contact.php" class="btn">INICIAR CONVERSACIÓN</a>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>