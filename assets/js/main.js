(function () {
    let currentId = null;

    window.loadMessage = function (id, el) {
        // UI Updates
        document.querySelectorAll('.msg-item').forEach(i => i.classList.remove('active'));
        if (el) {
            el.classList.add('active');
            el.classList.remove('unread');
        }

        const emptyState = document.getElementById('emptyState');
        const container = document.getElementById('msgContent');

        if (emptyState) emptyState.style.display = 'none';
        if (container) {
            container.classList.remove('active');

            // Fetch
            fetch(`/admin/api-admin.php?action=get_message&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }

                    currentId = data.id;

                    const fields = {
                        'vSubject': "Mensaje de " + data.name,
                        'vName': data.name,
                        'vEmail': data.email,
                        'vDate': data.formatted_date,
                        'vBody': data.message,
                        'vAvatar': data.name.charAt(0).toUpperCase()
                    };

                    for (const [key, val] of Object.entries(fields)) {
                        const elem = document.getElementById(key);
                        if (elem) elem.textContent = val;
                    }

                    const btnReply = document.getElementById('btnReply');
                    if (btnReply) btnReply.href = `mailto:${data.email}?subject=RE: Consulta CDOR&body=Hola ${data.name},%0A%0A...`;

                    container.style.display = 'block';
                    // Force reflow
                    void container.offsetWidth;
                    container.classList.add('active');
                })
                .catch(e => console.error("Error loading message", e));
        }
    };

    window.deleteCurrentMsg = function () {
        if (!currentId) return;
        if (!confirm('¿Borrar mensaje permanentemente?')) return;

        fetch(`/admin/api-admin.php?action=delete_message&id=${currentId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`.msg-item[onclick*="${currentId}"]`);
                    if (item) item.remove();

                    const container = document.getElementById('msgContent');
                    const emptyState = document.getElementById('emptyState');

                    if (container) container.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'flex';
                    currentId = null;
                }
            });
    };

    // SCROLL REVEAL OBSERVER
    document.addEventListener('DOMContentLoaded', () => {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1 // 10% visible para disparar
        };

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target); // Solo animar una vez
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
    // PARTICLE NETWORK BACKGROUND
    const canvas = document.getElementById('bg-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        let mouse = { x: null, y: null };

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            initParticles();
        }

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 0.5; // Velocidad lenta
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 1;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                // Rebotar en bordes
                if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
                if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
            }
            draw() {
                // Opacidad mínima: casi subliminal
                ctx.fillStyle = 'rgba(255, 255, 255, 0.1)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            const count = Math.min(60, Math.floor(window.innerWidth / 25));
            for (let i = 0; i < count; i++) {
                particles.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(p => {
                p.update();
                p.draw();

                // Conexiones Mouse - MUY SUTILES
                if (mouse.x != null) {
                    const dx = mouse.x - p.x;
                    const dy = mouse.y - p.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    const maxDist = 100; // Rango reducido

                    if (distance < maxDist) {
                        ctx.beginPath();
                        // Línea casi invisible
                        ctx.strokeStyle = `rgba(255, 255, 255, ${0.08 * (1 - distance / maxDist)})`; 
                        ctx.lineWidth = 0.4;
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(mouse.x, mouse.y);
                        ctx.stroke();
                        
                        // Efecto magnético muy suave (casi nulo)
                        const force = (maxDist - distance) / maxDist;
                        p.vx += (dx / distance) * force * 0.005;
                        p.vy += (dy / distance) * force * 0.005;
                    }
                }
            });
            requestAnimationFrame(animateParticles);
        }

        window.addEventListener('resize', resize);
        window.addEventListener('mousemove', e => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });
        window.addEventListener('mouseout', () => {
            mouse.x = null;
            mouse.y = null;
        });

        resize();
        animateParticles();
    }
})();