const toast = document.querySelector('.toast');

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

document.querySelectorAll('[data-pixel-swarm], .pixel-swarm-button, .btn-pixel-swarm').forEach((button) => {
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

let isDraggingCollection = false;
let hasDraggedCollection = false;

if (collectionScroller) {
  const getCardPositions = () => {
    const cards = Array.from(collectionScroller.querySelectorAll('.product-card'));
    if (!cards.length) return [];
    const baseOffset = cards[0].offsetLeft;
    return cards.map(c => Math.max(0, c.offsetLeft - baseOffset));
  };

  const updateCollectionControls = () => {
    if (!previousCollectionButton || !nextCollectionButton) return;
    const maximum = collectionScroller.scrollWidth - collectionScroller.clientWidth;
    previousCollectionButton.disabled = collectionScroller.scrollLeft <= 3;
    nextCollectionButton.disabled = collectionScroller.scrollLeft >= maximum - 3;
  };

  const scrollCollectionByCard = (direction) => {
    const positions = getCardPositions();
    const current = collectionScroller.scrollLeft;
    const maxScroll = collectionScroller.scrollWidth - collectionScroller.clientWidth;

    let target;
    if (positions.length > 0) {
      if (direction > 0) {
        target = positions.find(pos => pos > current + 8);
        if (target === undefined) target = maxScroll;
      } else {
        const prevPositions = positions.filter(pos => pos < current - 8);
        target = prevPositions.length ? prevPositions[prevPositions.length - 1] : 0;
      }
    } else {
      const card = collectionScroller.querySelector('.product-card');
      const gap = parseFloat(getComputedStyle(collectionScroller).gap) || 25;
      const distance = card ? card.getBoundingClientRect().width + gap : 360;
      target = current + (distance * direction);
    }

    target = Math.max(0, Math.min(maxScroll, target));
    collectionScroller.scrollTo({
      left: target,
      behavior: 'smooth'
    });
  };

  if (previousCollectionButton) {
    previousCollectionButton.addEventListener('click', () => scrollCollectionByCard(-1));
  }
  if (nextCollectionButton) {
    nextCollectionButton.addEventListener('click', () => scrollCollectionByCard(1));
  }

  collectionScroller.addEventListener('scroll', updateCollectionControls, { passive: true });
  window.addEventListener('resize', updateCollectionControls);
  updateCollectionControls();

  // Keyboard navigation on scroller
  collectionScroller.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      scrollCollectionByCard(-1);
    } else if (event.key === 'ArrowRight') {
      event.preventDefault();
      scrollCollectionByCard(1);
    }
  });

  // Desktop drag-to-scroll interaction
  let dragStartX = 0;
  let dragScrollStart = 0;
  let dragLastX = 0;
  let dragLastTime = 0;
  let dragVelocityX = 0;

  collectionScroller.addEventListener('mousedown', (event) => {
    if (event.button !== 0) return;
    isDraggingCollection = true;
    hasDraggedCollection = false;
    dragStartX = event.pageX;
    dragScrollStart = collectionScroller.scrollLeft;
    dragLastX = event.pageX;
    dragLastTime = performance.now();
    dragVelocityX = 0;
    collectionScroller.classList.add('is-dragging');
  });

  window.addEventListener('mousemove', (event) => {
    if (!isDraggingCollection) return;
    const now = performance.now();
    const dt = now - dragLastTime;
    const dx = event.pageX - dragLastX;
    if (dt > 0) {
      dragVelocityX = dx / dt;
    }
    dragLastX = event.pageX;
    dragLastTime = now;

    const totalWalk = event.pageX - dragStartX;
    if (Math.abs(totalWalk) > 6) {
      hasDraggedCollection = true;
    }
    collectionScroller.scrollLeft = dragScrollStart - totalWalk;
  });

  const stopCollectionDrag = () => {
    if (!isDraggingCollection) return;
    isDraggingCollection = false;
    collectionScroller.classList.remove('is-dragging');

    if (hasDraggedCollection) {
      const positions = getCardPositions();
      const current = collectionScroller.scrollLeft;
      const maxScroll = collectionScroller.scrollWidth - collectionScroller.clientWidth;
      let target = current;

      if (positions.length > 0) {
        if (Math.abs(dragVelocityX) > 0.35) {
          if (dragVelocityX < 0) {
            const nextPos = positions.find(pos => pos > current + 12);
            target = nextPos !== undefined ? nextPos : maxScroll;
          } else {
            const prevPositions = positions.filter(pos => pos < current - 12);
            target = prevPositions.length ? prevPositions[prevPositions.length - 1] : 0;
          }
        } else {
          let closest = positions[0];
          let minDiff = Infinity;
          for (const pos of positions) {
            const diff = Math.abs(pos - current);
            if (diff < minDiff) {
              minDiff = diff;
              closest = pos;
            }
          }
          target = closest;
        }
      }

      target = Math.max(0, Math.min(maxScroll, target));
      collectionScroller.scrollTo({
        left: target,
        behavior: 'smooth'
      });

      setTimeout(() => {
        hasDraggedCollection = false;
      }, 60);
    }
  };

  window.addEventListener('mouseup', stopCollectionDrag);
  document.addEventListener('mouseleave', stopCollectionDrag);
}

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

  card.addEventListener('click', () => {
    if (hasDraggedCollection) return;
    toggleJewelleryFocus(card);
  });
  card.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      toggleJewelleryFocus(card);
    }
  });
});
