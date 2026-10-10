/**
 * Pixel Swarm Button Controller
 * Initializes 3D pixel swarm scattering animation for buttons.
 */
function initPixelSwarm(button) {
  if (!button || button.dataset.swarmInitialized) return;
  button.dataset.swarmInitialized = 'true';

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
}

// Auto-initialize any pixel swarm buttons on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-pixel-swarm], .pixel-swarm-button, .btn-pixel-swarm').forEach(initPixelSwarm);
});
