<?php
require_once __DIR__ . '/app/config/bootstrap.php';

// AUTH CHECK
if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit;
}

$title = "Proyectos // Mike Corredor";
include __DIR__ . '/partials/head.php';
?>

<?php include __DIR__ . '/partials/menu.php'; ?>

<!-- Hero -->
<section class="hero container" style="min-height: 40vh;">
    <div class="animate-reveal">
        <h1 class="hero-title">
            LABORATORIO <span class="text-gradient">I+D</span>
        </h1>
        <p class="hero-subtitle">
            Innovación constante. Sistemas reales construidos con tecnologías de vanguardia.
        </p>
    </div>
</section>

<!-- Projects Grid -->
<main class="container mb-4">
    <div class="projects-grid animate-reveal delay-100">
        
        <!-- Project 1: AI -->
        <article class="project-card">
            <a href="/chatbot/" class="project-link">
                <div class="project-visual">
                    <div class="project-bg" style="background: radial-gradient(circle at center, #2e1065, #000);"></div>
                    <div class="project-icon"><i class="fa-solid fa-brain"></i></div>
                </div>
                
                <div class="project-content">
                    <h2 class="project-title">CDOR Neural AI</h2>
                    <p class="project-description">
                        Asistente conversacional ultra-rápido potenciado por <strong>Llama 3.3 70B</strong>. 
                        Respuestas en tiempo real.
                    </p>
                    <div class="tech-stack">
                        <span class="tech-tag">Groq API</span>
                        <span class="tech-tag">PHP 8</span>
                        <span class="tech-tag">Real-time</span>
                    </div>
                </div>
            </a>
        </article>

        <!-- Project 2: Auth -->
        <article class="project-card">
            <a href="#" class="project-link" style="cursor: default;">
                <div class="project-visual">
                    <div class="project-bg" style="background: linear-gradient(135deg, #0a0a0a, #1a1a1a);"></div>
                    <div class="project-icon"><i class="fa-solid fa-shield-halved"></i></div>
                </div>
                
                <div class="project-content">
                    <h2 class="project-title">Auth & Security Module</h2>
                    <p class="project-description">
                        Sistema de autenticación empresarial con <strong>OAuth2, 2FA</strong> y gestión de sesiones seguras.
                    </p>
                    <div class="tech-stack">
                        <span class="tech-tag">OAuth2</span>
                        <span class="tech-tag">JWT</span>
                        <span class="tech-tag">BCrypt</span>
                    </div>
                </div>
            </a>
        </article>

        <!-- Project 3: Data -->
        <article class="project-card">
            <a href="#" class="project-link" style="cursor: default;">
                <div class="project-visual">
                    <div class="project-bg" style="background: linear-gradient(135deg, #0a0a0a, #1a1a1a);"></div>
                    <div class="project-icon"><i class="fa-solid fa-chart-line"></i></div>
                </div>
                
                <div class="project-content">
                    <h2 class="project-title">Analytics Engine</h2>
                    <p class="project-description">
                        Motor de procesamiento de datos en tiempo real con visualización interactiva.
                    </p>
                    <div class="tech-stack">
                        <span class="tech-tag">MySQL</span>
                        <span class="tech-tag">Chart.js</span>
                        <span class="tech-tag">API REST</span>
                    </div>
                </div>
            </a>
        </article>



    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>