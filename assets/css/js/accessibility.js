(() => {
    const body = document.body;

    const savedFont = localStorage.getItem('font');
    if (savedFont) body.dataset.font = savedFont;

    if (localStorage.getItem('contrast')) {
        body.classList.add('contrast');
    }

    if (localStorage.getItem('reducedMotion')) {
        body.classList.add('reduced-motion');
    }

    window.setFont = size => {
        body.dataset.font = size;
        localStorage.setItem('font', size);
    };

    window.toggleContrast = () => {
        body.classList.toggle('contrast');
        localStorage.setItem('contrast', body.classList.contains('contrast'));
    };

    window.toggleMotion = () => {
        body.classList.toggle('reduced-motion');
        localStorage.setItem('reducedMotion', body.classList.contains('reduced-motion'));
    };
})();
