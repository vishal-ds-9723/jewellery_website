const menuButton = document.querySelector('.menu-button');
const nav = document.querySelector('.main-nav');
const toast = document.querySelector('.toast');
const header = document.querySelector('.site-header');

const updateHeader = () => header.classList.toggle('scrolled', window.scrollY > 16);
updateHeader();
window.addEventListener('scroll', updateHeader, { passive: true });

menuButton.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(isOpen));
  menuButton.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
});

document.querySelectorAll('.main-nav a').forEach((link) => {
  link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuButton.setAttribute('aria-expanded', 'false');
    menuButton.setAttribute('aria-label', 'Open menu');
  });
});

document.querySelectorAll('[data-toast]').forEach((button) => {
  button.addEventListener('click', () => {
    toast.textContent = button.dataset.toast;
    toast.classList.add('show');
    window.setTimeout(() => toast.classList.remove('show'), 2400);
  });
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));

document.querySelectorAll('.section-heading .text-link').forEach((button) => {
  const swarm = document.createElement('span');
  swarm.className = 'pixel-swarm';
  swarm.setAttribute('aria-hidden', 'true');

  const rows = 5;
  const columns = 18;

  for (let row = 0; row < rows; row += 1) {
    for (let column = 0; column < columns; column += 1) {
      const pixel = document.createElement('span');
      const direction = column < columns / 2 ? -1 : 1;
      const verticalDirection = row < rows / 2 ? -1 : 1;
      pixel.className = 'swarm-pixel';
      pixel.style.setProperty('--column', column);
      pixel.style.setProperty('--row', row);
      pixel.style.setProperty('--scatter-x', `${direction * (35 + Math.random() * 95)}px`);
      pixel.style.setProperty('--scatter-y', `${verticalDirection * (20 + Math.random() * 70)}px`);
      pixel.style.setProperty('--scatter-z', `${-180 + Math.random() * 360}px`);
      pixel.style.setProperty('--rotate-x', `${-150 + Math.random() * 300}deg`);
      pixel.style.setProperty('--rotate-y', `${-150 + Math.random() * 300}deg`);
      pixel.style.setProperty('--delay', `${Math.random() * 160}ms`);
      swarm.append(pixel);
    }
  }

  button.prepend(swarm);
  let disperseTimer;

  const startSwarm = () => {
    window.clearTimeout(disperseTimer);
    button.classList.remove('is-dispersing');
    button.classList.remove('is-swarming');
    window.requestAnimationFrame(() => window.requestAnimationFrame(() => button.classList.add('is-swarming')));
  };

  const reverseSwarm = () => {
    if (!button.classList.contains('is-swarming')) return;
    button.classList.remove('is-swarming');
    button.classList.add('is-dispersing');
    disperseTimer = window.setTimeout(() => button.classList.remove('is-dispersing'), 850);
  };

  button.addEventListener('mouseenter', startSwarm);
  button.addEventListener('mouseleave', reverseSwarm);
  button.addEventListener('focus', startSwarm);
  button.addEventListener('blur', reverseSwarm);
});

const collectionScroller = document.querySelector('[data-horizontal-scroll]');
const previousCollectionButton = document.querySelector('[data-scroll-previous]');
const nextCollectionButton = document.querySelector('[data-scroll-next]');
let collectionScrollTarget = collectionScroller.scrollLeft;
let collectionScrollFrame = null;

const updateCollectionControls = () => {
  const maximum = collectionScroller.scrollWidth - collectionScroller.clientWidth;
  previousCollectionButton.disabled = collectionScroller.scrollLeft <= 1;
  nextCollectionButton.disabled = collectionScroller.scrollLeft >= maximum - 1;
};

const scrollCollectionByCard = (direction) => {
  const card = collectionScroller.querySelector('.product-card');
  const gap = parseFloat(getComputedStyle(collectionScroller).gap) || 0;
  const distance = card.getBoundingClientRect().width + gap;

  collectionScrollTarget = Math.max(
    0,
    Math.min(collectionScroller.scrollWidth - collectionScroller.clientWidth, collectionScroller.scrollLeft + (distance * direction)),
  );

  if (collectionScrollFrame === null) {
    collectionScrollFrame = window.requestAnimationFrame(animateCollectionScroll);
  }
};

const animateCollectionScroll = () => {
  const distance = collectionScrollTarget - collectionScroller.scrollLeft;

  if (Math.abs(distance) < 0.5) {
    collectionScroller.scrollLeft = collectionScrollTarget;
    collectionScrollFrame = null;
    return;
  }

  collectionScroller.scrollLeft += distance * 0.12;
  collectionScrollFrame = window.requestAnimationFrame(animateCollectionScroll);
};

collectionScroller.addEventListener('wheel', (event) => {
  const delta = Math.abs(event.deltaY) >= Math.abs(event.deltaX) ? event.deltaY : event.deltaX;
  const multiplier = event.deltaMode === WheelEvent.DOM_DELTA_LINE ? 24 : event.deltaMode === WheelEvent.DOM_DELTA_PAGE ? collectionScroller.clientWidth : 1;
  const amount = delta * multiplier;
  const maximum = collectionScroller.scrollWidth - collectionScroller.clientWidth;

  collectionScrollTarget = Math.max(0, Math.min(maximum, collectionScrollTarget + amount));

  if (collectionScrollFrame === null) {
    collectionScrollFrame = window.requestAnimationFrame(animateCollectionScroll);
  }
}, { passive: true });

collectionScroller.addEventListener('scroll', () => {
  if (collectionScrollFrame === null) collectionScrollTarget = collectionScroller.scrollLeft;
  updateCollectionControls();
}, { passive: true });

previousCollectionButton.addEventListener('click', () => scrollCollectionByCard(-1));
nextCollectionButton.addEventListener('click', () => scrollCollectionByCard(1));
window.addEventListener('resize', updateCollectionControls);
updateCollectionControls();

const toggleJewelleryFocus = (card) => {
  const isFocused = card.classList.toggle('is-jewellery-focus');
  const productName = card.querySelector('h3').textContent;
  card.setAttribute('aria-pressed', String(isFocused));
  card.setAttribute('aria-label', `${productName}: ${isFocused ? 'show normal portrait' : 'show jewellery spotlight'}`);
};

document.querySelectorAll('.collection-grid .product-card').forEach((card) => {
  card.setAttribute('role', 'button');
  card.setAttribute('tabindex', '0');
  card.setAttribute('aria-pressed', 'false');
  card.setAttribute('aria-label', `${card.querySelector('h3').textContent}: show jewellery spotlight`);

  card.addEventListener('click', () => toggleJewelleryFocus(card));
  card.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      toggleJewelleryFocus(card);
    }
  });
});

document.querySelector('#newsletter-form').addEventListener('submit', (event) => {
  event.preventDefault();
  const status = document.querySelector('.form-status');
  status.textContent = 'You are on the list. Welcome to the house.';
  event.target.reset();
});
