const menuToggle = document.querySelector('.menu-button');
const nav = document.querySelector('.main-nav');
const header = document.querySelector('.site-header');

const updateHeader = () => header.classList.toggle('scrolled', window.scrollY > 16);
updateHeader();
window.addEventListener('scroll', updateHeader, { passive: true });

menuToggle.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('open');
  menuToggle.setAttribute('aria-expanded', String(isOpen));
  menuToggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
});

document.querySelectorAll('.main-nav a').forEach((link) => {
  link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', 'Open menu');
  });
});

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.14 });

document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));

document.querySelectorAll('.person-card').forEach((card) => {
  card.addEventListener('pointermove', (event) => {
    const bounds = card.getBoundingClientRect();
    const x = (event.clientX - bounds.left) / bounds.width - 0.5;
    const y = (event.clientY - bounds.top) / bounds.height - 0.5;
    card.style.transform = `perspective(700px) rotateX(${y * -3}deg) rotateY(${x * 3}deg)`;
  });
  card.addEventListener('pointerleave', () => {
    card.style.transform = '';
  });
});

// Defer the film until it is near the viewport. Native controls remain available.
(() => {
  const video = document.querySelector('#showroom-film');
  if (!video) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const saveData = navigator.connection?.saveData === true;
  let inView = false;
  let wantsPlayback = !reducedMotion.matches && !saveData;
  let automaticPause = false;

  const pauseOffscreen = () => {
    if (!video.paused) {
      automaticPause = true;
      video.pause();
    }
  };
  const syncPlayback = () => {
    if (!inView || document.hidden) {
      pauseOffscreen();
    } else if (wantsPlayback && video.paused) {
      video.play().catch(() => { /* Native play control handles autoplay restrictions. */ });
    }
  };

  video.addEventListener('pause', () => {
    if (automaticPause) automaticPause = false;
    else wantsPlayback = false;
  });
  video.addEventListener('play', () => {
    wantsPlayback = true;
    if (!inView || document.hidden) pauseOffscreen();
  });
  video.addEventListener('error', () => {
    document.querySelector('#showroom-film-error').hidden = false;
  });
  video.querySelector('source').addEventListener('error', () => {
    document.querySelector('#showroom-film-error').hidden = false;
  });

  if ('IntersectionObserver' in window) {
    if (!saveData) {
      const preloadObserver = new IntersectionObserver((entries, observer) => {
        if (entries.some(entry => entry.isIntersecting)) {
          video.preload = 'metadata';
          video.load();
          observer.disconnect();
        }
      }, { rootMargin: '300px' });
      preloadObserver.observe(video);
    }
    const playbackObserver = new IntersectionObserver(entries => {
      inView = entries[0].isIntersecting && entries[0].intersectionRatio >= 0.35;
      syncPlayback();
    }, { threshold: [0, 0.35] });
    playbackObserver.observe(video);
  } else {
    // Manual playback still works on browsers without IntersectionObserver.
    inView = true;
  }

  document.addEventListener('visibilitychange', syncPlayback);
  reducedMotion.addEventListener('change', () => {
    if (reducedMotion.matches) {
      wantsPlayback = false;
      pauseOffscreen();
    }
  });
})();
