(() => {
  const header = document.querySelector('.site-header');
  const button = header?.querySelector('.menu-button');
  const nav = header?.querySelector('.main-nav');
  if (!header || !button || !nav) return;

  const compact = window.matchMedia('(max-width: 1100px)');
  const setOpen = (open) => {
    nav.classList.toggle('open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  };
  const updateHeader = () => {
    header.classList.toggle('scrolled', document.body.classList.contains('contact-page') || window.scrollY > 16);
  };
  const measureHeader = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
  };

  button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });
  document.addEventListener('click', (event) => {
    if (!header.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && nav.classList.contains('open')) {
      setOpen(false);
      button.focus();
    }
  });
  header.addEventListener('focusout', (event) => {
    if (event.relatedTarget && !header.contains(event.relatedTarget)) setOpen(false);
  });
  compact.addEventListener('change', () => {
    if (!compact.matches && document.activeElement === button) nav.querySelector('a').focus();
    if (compact.matches && nav.contains(document.activeElement)) button.focus();
    setOpen(false);
    measureHeader();
  });
  window.addEventListener('scroll', updateHeader, { passive: true });
  window.addEventListener('resize', measureHeader, { passive: true });
  if ('ResizeObserver' in window) new ResizeObserver(measureHeader).observe(header);
  updateHeader();
  measureHeader();
})();
