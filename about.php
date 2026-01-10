<?php
require __DIR__ . '/app/config/bootstrap.php';

// SEO
$title = "Sobre Mí // Mike Corredor - Full Stack Developer";
$metaDescription = "Desarrollador web enfocado en crear sistemas robustos y eficientes. Especialización en PHP, seguridad web y arquitectura de alto rendimiento.";
$canonicalUrl = "https://cdor.online/about.php";
require __DIR__ . '/partials/head.php';
?>
<?php include __DIR__ . '/partials/menu.php'; ?>

<main class="container mb-50" style="max-width: 900px;">
    <article class="glass-card" style="margin-top: 60px; padding: 50px;">
        <header>
            <h1 style="font-size: clamp(2.5rem, 5vw, 3.5rem); margin-bottom: 30px;">
                SOBRE <span style="color:var(--primary)">MÍ</span>
            </h1>
        </header>

        <div style="color: #fff; margin-bottom: 25px; font-size: 1.2rem; line-height: 1.8;">
            <p style="margin-bottom: 20px;">
                Hola, soy <strong style="color:var(--primary)">Mike</strong>. Este sitio web no es una agencia,
                es mi <strong>laboratorio personal</strong> donde desarrollo, pruebo y exhibo sistemas reales.
            </p>
        </div>

        <hr style="border: 0; border-bottom: 1px solid var(--border); margin: 35px 0;">

        <div style="color: var(--text-dim); font-size: 1.1rem; line-height: 1.8;">
            <p style="margin-bottom: 20px;">
                Mi enfoque está en construir <strong style="color:#fff">arquitecturas robustas</strong> que
                resuelvan problemas reales. Me especializo en:
            </p>

            <ul style="list-style: none; padding: 0; margin: 25px 0;">
                <li style="margin-bottom: 12px; padding-left: 30px; position: relative;">
                    <span style="position: absolute; left: 0; color: var(--primary);">▸</span>
                    <strong style="color:#fff">Backend Development</strong> (PHP 8+, Node.js, Python)
                </li>
                <li style="margin-bottom: 12px; padding-left: 30px; position: relative;">
                    <span style="position: absolute; left: 0; color: var(--primary);">▸</span>
                    <strong style="color:#fff">Seguridad Web</strong> (OAuth2, 2FA, encriptación)
                </li>
                <li style="margin-bottom: 12px; padding-left: 30px; position: relative;">
                    <span style="position: absolute; left: 0; color: var(--primary)">▸</span>
                    <strong style="color:#fff">Optimización de Rendimiento</strong> (Core Web Vitals, caching)
                </li>
            </ul>

            <p style="margin-top: 25px;">
                Cada proyecto en la sección de <a href="/projects.php"
                    style="color:var(--primary); font-weight:600;">Proyectos</a>
                es funcional y está en producción. Si algo te resulta interesante o tienes una propuesta técnica,
                puedes contactarme a través del <a href="/contact.php"
                    style="color:var(--primary); font-weight:600;">formulario</a>.
            </p>
        </div>

        <div style="margin-top: 40px;">
            <a href="/projects.php" class="btn">EXPLORAR PROYECTOS</a>
        </div>
    </article>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>