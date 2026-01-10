document.documentElement.classList.add('js');

const toggle = document.querySelector('.menu-toggle');
const mobileMenu = document.querySelector('.nav-links');
const closeBtn = document.querySelector('.nav-links .close');

if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
        mobileMenu.classList.add('active');
    });
}

if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
    });
}

document.addEventListener('click', (e) => {
    if (!mobileMenu.contains(e.target) && !toggle.contains(e.target)) {
        mobileMenu.classList.remove('active');
    }
});
