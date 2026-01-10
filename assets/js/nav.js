console.log('Nav.js loaded - Event Delegation V2');

// Wait for DOM
document.addEventListener('DOMContentLoaded', () => {

    // --- Helper: Cookie Management (10 Year Persistence) ---
    function setCookie(name, value, days = 3650) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
    }

    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // --- GLOBAL ACCESSIBILITY INIT ---
    function initAccessibility() {
        // Motion
        const savedMotion = getCookie('reduceMotion') || localStorage.getItem('reduceMotion');
        if (savedMotion === 'true') {
            document.body.classList.add('reduce-motion');
            // Ensure cookie is set if it was only in localStorage
            if (!getCookie('reduceMotion')) setCookie('reduceMotion', 'true');
        }

        // Contrast
        const savedContrast = getCookie('highContrast') || localStorage.getItem('highContrast');
        if (savedContrast === 'true') {
            document.body.classList.add('high-contrast');
            if (!getCookie('highContrast')) setCookie('highContrast', 'true');
        }

        // Font Size
        const savedFont = getCookie('fontSize') || localStorage.getItem('fontSize');
        if (savedFont) {
            document.documentElement.style.fontSize = savedFont;
            if (!getCookie('fontSize')) setCookie('fontSize', savedFont);
        }
    }
    initAccessibility();

    // Export helpers for other scripts
    window.AccessHelpers = {
        setCookie,
        getCookie
    };

    const getEl = (id) => document.getElementById(id);

    // --- Core Action: Toggle Menu ---
    function executeToggle() {
        const menuOverlay = getEl('menuOverlay');
        const burgerBtn = getEl('burgerBtn');

        if (!menuOverlay) return;

        const isOpen = menuOverlay.classList.contains('active');
        console.log('Toggling. Was Open:', isOpen);

        if (isOpen) {
            // Close
            menuOverlay.classList.remove('active');
            menuOverlay.setAttribute('aria-hidden', 'true');
            if (burgerBtn) {
                burgerBtn.classList.remove('active');
                burgerBtn.setAttribute('aria-expanded', 'false');
            }
            document.body.style.overflow = ''; // Unlock Scroll
        } else {
            // Open
            menuOverlay.classList.add('active');
            menuOverlay.setAttribute('aria-hidden', 'false');
            if (burgerBtn) {
                burgerBtn.classList.add('active');
                burgerBtn.setAttribute('aria-expanded', 'true');
            }
            document.body.style.overflow = 'hidden';
        }
    }

    // --- Core Action: Font Size ---
    function changeFont(delta) {
        let current = parseFloat(document.documentElement.style.fontSize) || 100;
        // handle raw %
        if (document.documentElement.style.fontSize && document.documentElement.style.fontSize.includes('%')) {
            current = parseFloat(document.documentElement.style.fontSize);
        } else {
            current = 100; // default
        }

        let newVal = current + (delta * 10); // step by 10%
        if (newVal > 150) newVal = 150;
        if (newVal < 80) newVal = 80;

        document.documentElement.style.fontSize = `${newVal}%`;
        console.log('Font size:', newVal);
    }

    // --- EVENT DELEGATION (The Fix) ---
    document.body.addEventListener('click', (e) => {
        const target = e.target;

        // 1. Burger Button or Close Button
        if (target.closest('#burgerBtn') || target.closest('#closeMenuBtn')) {
            e.preventDefault();
            e.stopPropagation();
            executeToggle();
            return;
        }

        // 2. Mobile Links (Close on click)
        if (target.closest('.mobile-nav-link')) {
            // Allow default navigation, but close menu
            const menuOverlay = getEl('menuOverlay');
            if (menuOverlay && menuOverlay.classList.contains('active')) {
                executeToggle();
            }
            return;
        }

        // 3. Accessibility Controls - MOVED TO accessibility.php to isolate logic
        // nav.js only handles INIT (top of file)
    });

    // --- On Load: Sync Buttons if present ---
    function syncAccessButtons() {
        if (document.body.classList.contains('reduce-motion')) {
            const btn = document.getElementById('motionToggle');
            if (btn) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Movimiento Reducido ACTIVADO';
            }
        }
        if (document.body.classList.contains('high-contrast')) {
            const btn = document.getElementById('contrastToggle');
            if (btn) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Alto Contraste ACTIVADO';
            }
        }
    }
    syncAccessButtons();

    // --- Feature: Instant Page Loader ---
    function showLoader() {
        let loader = getEl('pageLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'pageLoader';
            loader.className = 'page-loader';
            loader.innerHTML = '<div class="loader-spinner"></div>';
            document.body.appendChild(loader);
        }
        // Force reflow
        void loader.offsetWidth;
        loader.classList.add('active');
    }

    // Intercept clicks on links for instant feedback
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && link.href && !link.target && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            const url = new URL(link.href);
            // Only internal links, not same page anchors
            if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
                // Determine destination similarity to avoid loading on hash changes if needed
                if (link.getAttribute('href').startsWith('#')) return;

                showLoader();
                // Allow default navigation to proceed
            }
        }
    });

});