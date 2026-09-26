(() => {
  const toggle = document.querySelector('[data-app-nav-toggle]');
  const navigation = document.querySelector('[data-app-nav]');
  if (toggle && navigation) {
    toggle.addEventListener('click', () => {
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!expanded));
      navigation.classList.toggle('is-open', !expanded);
    });
  }
})();
