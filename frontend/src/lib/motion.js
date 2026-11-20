const TARGETS = [
    'main section .container > *',
    'main section .nt-reveal',
    'main .nt-card-grid > *',
    'main .row.nt-stagger > *',
    'main .nt-stagger > *',
    'main .d-grid > *',
    'main .nt-event-row',
    'main .nt-feature > *',
    'main .nt-image-list > *',
    'main .nt-image-list__panel li',
    '.nt-footer__links > *',
    '.nt-footer__contact > *',
].join(',');

let observer = null;

export function reducedMotion() {
    return typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function initReveal(root = document) {
    if (typeof window === 'undefined' || reducedMotion() || !('IntersectionObserver' in window)) return () => {};

    observer?.disconnect();
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('rv-in');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    root.querySelectorAll('.rv:not(.rv-in)').forEach((el) => observer.observe(el));

    const fold = window.innerHeight;
    root.querySelectorAll(TARGETS).forEach((el) => {
        if (el.classList.contains('rv') || el.closest('.nt-hero, .nt-page-hero, .nt-mega, .nt-dropdown')) return;
        if (el.getBoundingClientRect().top < fold * 0.95) return;
        const siblings = el.parentElement ? Array.from(el.parentElement.children) : [];
        el.style.setProperty('--rv-delay', `${Math.min(siblings.indexOf(el), 5) * 70}ms`);
        el.classList.add('rv');
        observer.observe(el);
    });

    return () => observer?.disconnect();
}

export function initCountUp(root = document, { immediate = false } = {}) {
    if (typeof window === 'undefined' || reducedMotion() || !('IntersectionObserver' in window)) return () => {};
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            io.unobserve(entry.target);
            const el = entry.target;
            const original = el.dataset.countup;
            const match = original.match(/^(\D*)(\d[\d,]*\.?\d*)(.*)$/);
            if (!match) return;
            const [, prefix, number, suffix] = match;
            const target = parseFloat(number.replace(/,/g, ''));
            const decimals = (number.split('.')[1] || '').length;
            const start = performance.now();
            const duration = 1800;
            const tick = (now) => {
                const p = Math.min(1, Math.max(0, (now - start) / duration));
                const eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
                el.textContent = `${prefix}${(target * eased).toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })}${suffix}`;
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = original;
            };
            requestAnimationFrame(tick);
        });
    }, { threshold: 0.6 });
    root.querySelectorAll('[data-countup]').forEach((el) => {
        if (immediate || el.getBoundingClientRect().top > window.innerHeight * 0.9) {
            if (immediate) el.textContent = el.dataset.countup.replace(/\d[\d,]*\.?\d*/, (n) => n.replace(/\d/g, '0').replace(/^0+(?=\d)/, ''));
            io.observe(el);
        }
    });
    return () => io.disconnect();
}

export function enterPage(main = document.getElementById('main')) {
    if (!main || reducedMotion()) return;
    main.classList.remove('nt-page-enter');
    void main.offsetWidth;
    main.classList.add('nt-page-enter');
}
