const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initReveal(scope = document) {
    scope.querySelectorAll('[data-reveal-group]').forEach((group) => {
        Array.from(group.children).forEach((child, i) => child.style.setProperty('--i', Math.min(i, 8)));
    });
    const els = Array.from(scope.querySelectorAll('[data-reveal], [data-reveal-group] > *')).filter((el) => !el.classList.contains('is-visible'));
    if (reduceMotion || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                const el = e.target;
                el.classList.add('is-visible');
                el.addEventListener('transitionend', (ev) => { if (ev.propertyName === 'opacity') el.classList.add('reveal-done'); }, { once: true });
                io.unobserve(el);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    els.forEach((el) => io.observe(el));
}

function initImages(scope = document) {
    scope.querySelectorAll('img.img-fade:not(.is-loaded)').forEach((img) => {
        const done = () => img.classList.add('is-loaded');
        if (img.complete) done(); else { img.addEventListener('load', done, { once: true }); img.addEventListener('error', done, { once: true }); }
    });
}

function countUp(el, to, { decimals = 0, suffix = '', duration = 700 } = {}) {
    const final = Number(to).toFixed(decimals) + suffix;
    if (reduceMotion) { el.textContent = final; return; }
    const start = performance.now();
    (function tick(now) {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = p < 1 ? (Number(to) * eased).toFixed(decimals) + suffix : final;
        if (p < 1) requestAnimationFrame(tick);
    })(start);
}

function bump(el) {
    if (reduceMotion || !el) return;
    el.classList.remove('anim-bump'); void el.offsetWidth; el.classList.add('anim-bump');
}

function initHeader() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    const update = () => header.setAttribute('data-scrolled', window.scrollY > 8 ? 'true' : 'false');
    update();
    window.addEventListener('scroll', update, { passive: true });
}

window.JJ = { initReveal, initImages, countUp, bump, reduceMotion };
document.addEventListener('DOMContentLoaded', () => { initHeader(); initReveal(); initImages(); });
