const products = {
  noor: { title: 'The Noor Set', category: 'Bridal edit', image: 'assets/ra-parekh-person-1.jpeg', description: 'An ornate bridal composition created for ceremonies, celebrations, and the stories that follow.' },
  gul: { title: 'Gul Jhumka', category: 'Signature', image: 'assets/ra-parekh-person-2.jpeg', description: 'A richly detailed pair of jhumkas shaped to bring warmth and movement to every occasion.' },
  meher: { title: 'Meher Necklace', category: 'Everyday heirloom', image: 'assets/ra-parekh-person-3.jpeg', description: 'A refined necklace designed for effortless wear and a lifetime of meaningful moments.' },
  sitara: { title: 'Sitara Jhumka', category: 'Signature', image: 'assets/ra-parekh-person-4.jpeg', description: 'A luminous statement inspired by starlight, finished with intricate traditional detail.' },
  rani: { title: 'The Rani Set', category: 'Bridal edit', image: 'assets/ra-parekh-person-5.jpeg', description: 'A regal bridal set with a strong silhouette and carefully considered craftsmanship.' },
  nira: { title: 'Nira Pendant', category: 'Everyday heirloom', image: 'assets/ra-parekh-person-6.jpeg', description: 'A delicate pendant made to become part of the everyday and stay for generations.' },
  zoya: { title: 'Zoya Drops', category: 'Signature', image: 'assets/ra-parekh-person-7.jpeg', description: 'Elegant drops that balance contemporary ease with the richness of Indian jewellery craft.' },
  'mens-rope-chain': { title: 'Royal Rope Chain', category: "Men's collection", image: 'assets/ra-parekh-man-1.jpeg', description: 'A substantial gold rope chain designed with a confident profile and enduring finish.' },
  'mens-signature-chain': { title: 'Signature Link Chain', category: "Men's collection", image: 'assets/ra-parekh-man-2.jpeg', description: 'A bold linked chain that balances traditional gold craftsmanship with contemporary character.' },
  'mens-classic-chain': { title: 'Classic Gold Chain', category: "Men's collection", image: 'assets/ra-parekh-man-3.jpeg', description: 'A timeless gold chain made for effortless everyday wear and lasting presence.' },
  'mens-heritage-chain': { title: 'Heritage Link Chain', category: "Men's collection", image: 'assets/ra-parekh-man-4.jpeg', description: 'A heritage-inspired gold chain defined by strong links and carefully finished detail.' },
};

const key = new URLSearchParams(window.location.search).get('item');
const product = products[key] || products.noor;
const image = document.querySelector('#product-image');
const zoomButton = document.querySelector('.zoom-button');

document.title = `${product.title} | R.A. Parikh Jewellers`;
document.querySelector('#product-category').textContent = product.category;
document.querySelector('#product-title').textContent = product.title;
document.querySelector('#product-description').textContent = product.description;
image.src = new URL(product.image, document.querySelector('meta[name="asset-base"]').content).href;
image.alt = product.title;

const setZoomOrigin = (clientX, clientY) => {
  const bounds = zoomButton.getBoundingClientRect();
  const x = Math.max(0, Math.min(100, ((clientX - bounds.left) / bounds.width) * 100));
  const y = Math.max(0, Math.min(100, ((clientY - bounds.top) / bounds.height) * 100));
  image.style.transformOrigin = `${x}% ${y}%`;
};

zoomButton.addEventListener('click', (event) => {
  const isPointerClick = event.clientX !== 0 || event.clientY !== 0;

  if (!zoomButton.classList.contains('is-zoomed') && isPointerClick) {
    setZoomOrigin(event.clientX, event.clientY);
  }

  const isZoomed = zoomButton.classList.toggle('is-zoomed');
  zoomButton.setAttribute('aria-pressed', String(isZoomed));
  zoomButton.setAttribute('aria-label', isZoomed ? 'Return jewellery image to normal size' : 'Zoom jewellery image to 3 times');
});

zoomButton.addEventListener('pointermove', (event) => {
  if (!zoomButton.classList.contains('is-zoomed')) return;
  if (event.pointerType === 'touch' && event.buttons === 0) return;

  setZoomOrigin(event.clientX, event.clientY);
});
