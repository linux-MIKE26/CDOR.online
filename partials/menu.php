<?php
// LOGO IMAGE - Premium Logo for Web View
$logoSvg = '<img src="/assets/css/images/logo_new.png" alt="CDOR Logo" width="50" height="50" class="logo-glow" style="object-fit: contain;">';
?>
<!-- Floating Hamburger Button -->
<button id="burgerBtn" class="burger-btn" aria-label="Menu" aria-expanded="false" aria-controls="menuOverlay">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Enhanced Fullscreen Menu Overlay -->
<div id="menuOverlay" class="menu-overlay" aria-hidden="true" role="navigation" aria-label="Main Menu">
    <!-- Menu Header -->
    <div class="menu-header">
        <!-- Text Only as requested -->
        <div class="menu-brand">
            <span style="font-size: 1.5rem; letter-spacing: 2px; color: #fff; font-weight: 800;">CDOR <span
                    style="color:var(--primary)">ONLINE</span></span>
        </div>

        <button id="closeMenuBtn" class="close-btn" aria-label="Cerrar Menú">
            <i class="fa-solid fa-xmark" style="font-size: 2rem;"></i>
        </button>
    </div>

    <!-- Menu Links -->
    <nav class="mobile-nav">
        <a href="/" class="mobile-nav-link" data-text="INICIO">INICIO</a>
        <a href="/projects.php" class="mobile-nav-link" data-text="PROYECTOS">PROYECTOS</a>
        <a href="/about.php" class="mobile-nav-link" data-text="SOBRE MÍ">SOBRE MÍ</a>
        <a href="/contact.php" class="mobile-nav-link" data-text="CONTACTO">CONTACTO</a>
        <?php if (isset($_SESSION['user'])): ?>
            <?php $dashboardLink = ($_SESSION['user']['role'] === 'admin') ? '/admin/admin-dashboard.php' : '/dashboard.php'; ?>
            <a href="<?= $dashboardLink ?>" class="mobile-nav-link highlight" data-text="MI PERFIL">MI PERFIL</a>
        <?php else: ?>
            <a href="/login.php" class="mobile-nav-link highlight" data-text="ACCESO">ACCESO</a>
        <?php endif; ?>
    </nav>

    <!-- Accessibility Footer -->
    <div class="menu-footer">
        <div class="accessibility-panel">
            <a href="/accessibility.php" class="btn btn-outline btn-block"
                style="display:flex; justify-content:center; align-items:center; gap:10px;">
                <i class="fa-solid fa-universal-access"></i> OPCIONES DE ACCESIBILIDAD
            </a>
        </div>
    </div>
</div>

<script src="/assets/js/nav.js?v=2.6" defer></script>