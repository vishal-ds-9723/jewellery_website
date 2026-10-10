const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));

document.querySelectorAll('[data-filter]').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelector('.filters .active').classList.remove('active');
    button.classList.add('active');
    const filter = button.dataset.filter;
    document.querySelectorAll('.product-card').forEach((card) => {
      card.hidden = filter !== 'all' && card.dataset.category !== filter;
    });
  });
});

// Native buttons support mouse, touch, Enter, and Space without navigation.
document.querySelectorAll('.product-image[aria-pressed]:not([data-background-toggle])').forEach((button) => {
  const card = button.closest('.product-card');
  const name = card.querySelector('h3').textContent;
  const focusImage = button.querySelector('.product-image-focus');
  const updateLabel = (focused) => button.setAttribute('aria-label',
    name + ': ' + (focused ? 'show normal portrait' : 'show jewellery spotlight'));

  button.addEventListener('click', async () => {
    const focused = button.getAttribute('aria-pressed') !== 'true';
    button.setAttribute('aria-pressed', String(focused));
    updateLabel(focused);
    if (!focused) {
      card.classList.remove('is-jewellery-focus');
      return;
    }

    // Keep the colour portrait visible until its alternate image is decoded.
    try {
      focusImage.loading = 'eager';
      await focusImage.decode();
      if (button.getAttribute('aria-pressed') === 'true') {
        card.classList.add('is-jewellery-focus');
      }
    } catch {
      button.setAttribute('aria-pressed', 'false');
      updateLabel(false);
    }
  });
});

// Transparent jewellery photos keep their colour as the background changes.
document.querySelectorAll('[data-background-toggle]').forEach((button) => {
  const name = button.querySelector('img').alt;
  button.addEventListener('click', () => {
    const dark = button.getAttribute('aria-pressed') !== 'true';
    button.setAttribute('aria-pressed', String(dark));
    button.setAttribute('aria-label', name + ': show ' + (dark ? 'light' : 'dark') + ' background');
  });
});
