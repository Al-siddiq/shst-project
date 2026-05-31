/**
 * Progressive mobile navigation enhancement.
 *
 * Public content remains usable without JavaScript. This tiny script only
 * reveals the tenant menu on narrow screens and maintains accessible state.
 */
(() => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        menu.classList.toggle('is-open', !expanded);
    });
})();
