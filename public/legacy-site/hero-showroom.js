(() => {
  const hero = document.querySelector('.showroom-hero');
  if (!hero) return;

  const images = [...hero.querySelectorAll('.showroom-images img')];
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let inView = true;

  const updatePlayback = () => {
    hero.classList.toggle('is-paused', !inView || document.hidden || reducedMotion.matches);
  };

  document.addEventListener('visibilitychange', updatePlayback);
  reducedMotion.addEventListener('change', updatePlayback);

  if ('IntersectionObserver' in window) {
    const visibilityObserver = new IntersectionObserver(([entry]) => {
      inView = entry.isIntersecting;
      updatePlayback();
    });
    visibilityObserver.observe(hero);
  }

  // Decode both stationary layers before starting the light-to-dark fade.
  Promise.all(images.map((image) => image.decode())).then(() => {
    hero.classList.add('is-ready');
    updatePlayback();
  }).catch(() => {
    hero.classList.add('is-fallback');
    // Retain whichever photograph loaded if the other layer is unavailable.
    images.forEach((image) => {
      if (!image.naturalWidth) image.style.display = 'none';
      else image.style.opacity = '1';
    });
  });
})();
