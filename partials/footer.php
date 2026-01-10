<footer role="contentinfo" class="main-footer-section"
    style="text-align: center; border-top: 1px solid var(--border); background: #000; padding: 100px 0 60px;">
    <div class="container footer-content"
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin: 0 auto; width: 100%;">

        <!-- Brand -->
        <div class="footer-brand"
            style="font-family: var(--font-display); font-size: 2.2rem; color: #fff; margin-bottom: 25px; font-weight: 900; letter-spacing: 5px; text-transform: uppercase;">
            CDOR <span class="highlight"
                style="color: var(--primary); text-shadow: 0 0 20px var(--primary-glow);">//</span> PORTAFOLIO
        </div>
        <p class="footer-tagline"
            style="color: var(--text-muted); margin-bottom: 50px; font-size: 1.1rem; max-width: 650px; font-weight: 500; line-height: 1.6;">
            Desarrollo Web de Alto Rendimiento & Sistemas Robustos
        </p>

        <!-- Navigation Links -->
        <nav aria-label="Footer Navigation" class="footer-nav" style="margin-bottom: 50px; width: 100%;">
            <ul class="footer-nav-list"
                style="display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; list-style: none; padding: 0; margin: 0 auto;">
                <li><a href="/projects.php" class="footer-link">Proyectos</a></li>
                <li><a href="/about.php" class="footer-link">Sobre Mí</a></li>
                <li><a href="/contact.php" class="footer-link">Contacto</a></li>
                <li><a href="/privacy.php" class="footer-link">Privacidad</a></li>
                <li><a href="/admin/" class="footer-link">Acceso Admin</a></li>
            </ul>
        </nav>

        <!-- Social Links -->
        <div class="footer-social" style="margin-bottom: 50px; display: flex; justify-content: center; gap: 20px;">
            <a href="https://github.com/mikecorredor" target="_blank" rel="noopener noreferrer" aria-label="GitHub"
                class="social-icon"
                style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50%; color: #fff; transition: all 0.4s var(--ease-out);">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path
                        d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0016 8c0-4.42-3.58-8-8-8z" />
                </svg>
            </a>
        </div>

        <!-- Copyright & Status -->
        <div class="footer-bottom"
            style="border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 50px; width: 100%;">
            <div class="footer-legal"
                style="display: flex; justify-content: center; gap: 30px; margin-bottom: 30px; flex-wrap: wrap;">
                <a href="/privacy.php">Privacidad</a>
                <a href="/accessibility.php">Accesibilidad</a>
                <a href="/report-bug.php" class="bug-report" style="color: var(--primary); font-weight: 800;">¿Tienes
                    algún bug?</a>
            </div>
            <div class="copy text-muted mt-4">
                &copy; <?= date('Y') ?> Mike Corredor. <span class="rights">Todos los derechos reservados.</span>
            </div>
            <p class="footer-status"
                style="color: var(--primary); font-weight: 900; font-family: var(--font-display); font-size: 0.7rem; letter-spacing: 4px; margin-top: 40px; opacity: 0.5; text-transform: uppercase;">
                [ SISTEMA OPERATIVO ]
            </p>
        </div>
    </div>
</footer>

<?php include __DIR__ . '/cookies.php'; ?>

<!-- Structured Data for Organization (Footer context) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "CDOR Portfolio",
  "url": "https://cdor.online",
  "author": {
    "@type": "Person",
    "name": "Mike Corredor"
  },
  "inLanguage": "es"
}
</script>

</body>

</html>