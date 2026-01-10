<?php
require __DIR__ . '/app/config/bootstrap.php';
$title = "Accesibilidad · CDOR";
include __DIR__ . '/partials/head.php';
?>

<!-- Minimal Header Override for this page only -->
<style>
    /* Force header transparency and removal of any borders/backgrounds */
    .main-header {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        pointer-events: none;
        /* Let clicks pass through except for burger */
    }

    /* Center Layout */
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        z-index: 10;
        min-height: 100vh;
        /* Full viewport height */
    }

    /* Glass Panel */
    .access-panel {
        background: rgba(10, 10, 12, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 215, 0, 0.1);
        border-radius: 24px;
        padding: 50px 40px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        position: relative;
        overflow: hidden;
    }

    /* Glow Effect behind panel */
    .access-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.05), transparent 60%);
        pointer-events: none;
        z-index: -1;
    }

    .panel-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .panel-title {
        font-size: 2rem;
        color: #fff;
        margin-bottom: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 800;
        text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
    }

    .panel-desc {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    /* Modern Rows */
    .control-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding: 5px 0;
    }

    .control-label h3 {
        font-size: 1.1rem;
        margin: 0;
        color: #fff;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .control-label p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 4px 0 0 0;
    }

    /* Cyberpunk Toggle */
    .cyber-toggle {
        position: relative;
        width: 56px;
        height: 30px;
        flex-shrink: 0;
    }

    .cyber-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .cyber-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #1a1a1a;
        transition: .4s;
        border-radius: 30px;
        border: 1px solid #333;
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.5);
    }

    .cyber-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background-color: #888;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    input:checked+.cyber-slider {
        background-color: rgba(255, 215, 0, 0.1);
        /* Gold tint */
        border-color: var(--primary);
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
    }

    input:checked+.cyber-slider:before {
        transform: translateX(26px);
        background-color: var(--primary);
        box-shadow: 0 0 10px var(--primary);
    }

    /* Font Size Controls */
    .font-controls {
        display: flex;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        padding: 5px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
        justify-content: space-between;
        margin-top: 15px;
    }

    .font-btn {
        flex: 1;
        background: transparent;
        border: none;
        color: var(--text-muted);
        padding: 10px;
        cursor: pointer;
        font-weight: 700;
        transition: 0.3s;
        border-radius: 8px;
    }

    .font-btn:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .font-btn.active {
        background: var(--primary);
        color: #000;
        box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
    }

    /* Back Button */
    .btn-back {
        margin-top: 30px;
        width: 100%;
        padding: 15px;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-muted);
        border-radius: 12px;
        cursor: pointer;
        transition: 0.3s;
        font-family: var(--font-display);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.8rem;
    }

    .btn-back:hover {
        border-color: #fff;
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
    }
</style>

<?php include __DIR__ . '/partials/menu.php'; ?>

<main>
    <div class="access-panel animate-reveal">
        <div class="panel-header">
            <h1 class="panel-title">ACCESIBILIDAD</h1>
            <p class="panel-desc">AJUSTES DE INTERFAZ DEL NÚCLEO</p>
        </div>

        <div
            style="background: rgba(255,215,0,0.02); border: 1px solid rgba(255,215,0,0.1); border-radius: 16px; padding: 25px; margin-bottom: 30px;">
            <!-- High Contrast -->
            <div class="control-row">
                <div class="control-label">
                    <h3>Alto Contraste</h3>
                    <p>Maximizar legibilidad del sistema</p>
                </div>
                <label class="cyber-toggle">
                    <input type="checkbox" id="check-contrast" onchange="AccessManager.toggleContrast(this.checked)">
                    <span class="cyber-slider"></span>
                </label>
            </div>

            <!-- Reduced Motion -->
            <div class="control-row" style="margin-bottom:0">
                <div class="control-label">
                    <h3>Movilidad Reducida</h3>
                    <p>Optimizar rendimiento visual</p>
                </div>
                <label class="cyber-toggle">
                    <input type="checkbox" id="check-motion" onchange="AccessManager.toggleMotion(this.checked)">
                    <span class="cyber-slider"></span>
                </label>
            </div>
        </div>

        <!-- Font Size -->
        <div
            style="margin-top: 30px; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 16px; padding: 25px;">
            <div class="control-label" style="text-align:left; margin-bottom:15px;">
                <h3>ESCALADO DE TEXTO</h3>
            </div>
            <div class="font-controls">
                <button class="font-btn" onclick="AccessManager.changeFont(-1)"><i
                        class="fa-solid fa-minus"></i></button>
                <div style="display:flex; align-items:center; color:var(--primary); font-weight:900; font-family:var(--font-display); width:80px; justify-content:center; font-size:1.2rem; text-shadow: 0 0 10px var(--primary-glow);"
                    id="font-display">100%</div>
                <button class="font-btn" onclick="AccessManager.changeFont(1)"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div style="text-align:right; margin-top:15px;">
                <button onclick="AccessManager.resetFont()"
                    style="background:none; border:none; color:#666; font-size:0.75rem; cursor:pointer; text-transform:uppercase; letter-spacing:1px;">[
                    Resetear Parámetros ]</button>
            </div>
        </div>

        <button class="btn btn-block" style="margin-top: 40px;" onclick="window.location.href='/'">
            <i class="fa-solid fa-chevron-left"></i> VOLVER AL SISTEMA
        </button>
    </div>
</main>

<script>
    const AccessManager = {
        init: function () {
            this.syncUI();
        },

        setCookie: function (name, value, days = 3650) {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
        },

        removeCookie: function (name) {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        },

        syncUI: function () {
            // Sync Checkboxes
            document.getElementById('check-contrast').checked = document.body.classList.contains('high-contrast');
            document.getElementById('check-motion').checked = document.body.classList.contains('reduce-motion');

            // Sync Font
            const f = document.documentElement.style.fontSize || '100%';
            document.getElementById('font-display').textContent = f;
        },

        toggleContrast: function (checked) {
            document.body.classList.toggle('high-contrast', checked);
            if (checked) {
                this.setCookie('highContrast', 'true');
                localStorage.setItem('highContrast', 'true');
            } else {
                this.removeCookie('highContrast');
                localStorage.removeItem('highContrast');
            }
        },

        toggleMotion: function (checked) {
            document.body.classList.toggle('reduce-motion', checked);
            if (checked) {
                this.setCookie('reduceMotion', 'true');
                localStorage.setItem('reduceMotion', 'true');
            } else {
                this.removeCookie('reduceMotion');
                localStorage.removeItem('reduceMotion');
            }
        },

        changeFont: function (delta) {
            let current = parseFloat(document.documentElement.style.fontSize) || 100;
            if (isNaN(current)) current = 100;

            let val = current + (delta * 10);
            if (val > 150) val = 150;
            if (val < 80) val = 80;

            const newVal = val + '%';
            document.documentElement.style.fontSize = newVal;
            this.setCookie('fontSize', newVal);
            localStorage.setItem('fontSize', newVal);
            this.syncUI();
        },

        resetFont: function () {
            document.documentElement.style.fontSize = '';
            this.removeCookie('fontSize');
            localStorage.removeItem('fontSize');
            this.syncUI();
            document.getElementById('font-display').textContent = 'Auto';
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        AccessManager.init();
    });
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>