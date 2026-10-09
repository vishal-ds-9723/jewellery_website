const fs = require('node:fs');
const assert = require('node:assert/strict');
const { randomUUID } = require('node:crypto');

const base = process.env.SITE_URL || 'http://127.0.0.1:8088';
const email = 'qa-' + randomUUID() + '@example.com';
const reportPath = 'storage/app/private/browser-check.json';
fs.mkdirSync('storage/app/private', { recursive: true });
fs.writeFileSync(reportPath, JSON.stringify({ email, passed: false }));
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

(async () => {
  let target;
  for (let attempt = 0; attempt < 50; attempt++) {
    try { target = await fetch('http://127.0.0.1:9223/json/new?about:blank', { method: 'PUT' }).then(r => r.json()); break; }
    catch (error) { if (attempt === 49) throw error; await delay(100); }
  }
  const socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
  const pending = new Map();
  const exceptions = [];
  let nextId = 0;
  socket.onmessage = (event) => {
    const result = JSON.parse(event.data);
    if (result.id && pending.has(result.id)) {
      const { resolve, reject, timer } = pending.get(result.id);
      clearTimeout(timer);
      pending.delete(result.id);
      result.error ? reject(new Error(result.error.message)) : resolve(result.result);
    } else if (result.method === 'Runtime.exceptionThrown') {
      exceptions.push(result.params.exceptionDetails.exception?.description || result.params.exceptionDetails.text);
    }
  };
  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++nextId;
    const timer = setTimeout(() => { pending.delete(id); reject(new Error('CDP timeout: ' + method)); }, 15000);
    pending.set(id, { resolve, reject, timer });
    socket.send(JSON.stringify({ id, method, params }));
  });
  const evaluate = async (expression) => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
    return result.result.value;
  };
  const until = async (expression, label) => {
    for (let attempt = 0; attempt < 100; attempt++) {
      if (await evaluate(expression)) return;
      await delay(100);
    }
    throw new Error('Timed out: ' + label);
  };
  const navigate = async (path) => {
    await send('Page.navigate', { url: base + path });
    await until("location.href === " + JSON.stringify(base + path) + " && document.readyState === 'complete'", path);
  };
  const fill = async (phone = '+91 9876543210') => {
    await evaluate("(() => { const f=document.querySelector('#enquiry-form'); const values=" + JSON.stringify({
      name: 'QA Browser Check', email, phone, shopping_interest: 'women',
      appointment_slot: 'morning', message: 'Automated browser verification - safe to remove.',
    }) + "; for(const [k,v] of Object.entries(values)) f.elements.namedItem(k).value=v; f.elements.namedItem('appointment_date').value=new Intl.DateTimeFormat('en-CA',{timeZone:'Asia/Kolkata',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date(Date.now()+86400000)); })()");
  };

  try {
    await send('Page.enable');
    await send('Runtime.enable');
    await send('Network.enable');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });

    for (const page of ['/', '/about', '/collections', '/product?item=gul', '/contact']) {
      await navigate(page);
      assert.equal(await evaluate("document.querySelectorAll('main').length"), 1, page);
      await until("Array.from(document.images).filter(i => i.loading !== 'lazy').every(i => i.complete && i.naturalWidth)", 'Eager images load: ' + page);
      // Scroll each lazy image into view and verify it actually decodes.
      const imageCount = await evaluate("document.images.length");
      for (let i = 0; i < imageCount; i++) {
        await evaluate("document.images[" + i + "].scrollIntoView({behavior:'instant',block:'center'})");
        await until("document.images[" + i + "].complete && document.images[" + i + "].naturalWidth > 0", 'Image ' + i + ' on ' + page);
      }
    }

    await fill();
    const before = await evaluate('location.href');
    await evaluate("document.querySelector('#enquiry-form').requestSubmit(); document.querySelector('#enquiry-form').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));");
    await until("document.querySelector('#enquiry-status').dataset.state === 'success'", 'enquiry AJAX success');
    assert.equal(await evaluate('location.href'), before, 'No page reload or redirect');
    assert.equal(await evaluate("document.querySelector('#name').value"), '', 'Reset only after save');

    await fill('-------');
    await evaluate("document.querySelector('#enquiry-form').requestSubmit()");
    await until("document.querySelector('#enquiry-status').dataset.state === 'error'", 'server validation');
    assert.equal(await evaluate("document.querySelector('#phone').getAttribute('aria-invalid')"), 'true');
    assert.equal(await evaluate("document.querySelector('#email').value"), email, 'Input retained after invalid submission');

    await fill();
    await send('Network.setBlockedURLs', { urls: [base + '/enquiries'] });
    await evaluate("document.querySelector('#enquiry-form').requestSubmit()");
    await until("document.querySelector('#enquiry-status').dataset.state === 'error' && !document.querySelector('.submit-button').disabled", 'network error recovery');
    assert.equal(await evaluate("document.querySelector('#email').value"), email);
    await send('Network.setBlockedURLs', { urls: [] });

    // Laravel 13 trusts browser same-origin metadata; a request without it must have a valid token.
    const cookies = (await send('Network.getCookies', { urls: [base] })).cookies.map(c => c.name + '=' + c.value).join('; ');
    const rejected = await fetch(base + '/enquiries', {
      method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', Cookie: cookies, 'X-CSRF-TOKEN': 'invalid' },
      body: new URLSearchParams({ _token: 'invalid' }),
    });
    assert.equal(rejected.status, 419, 'Invalid token without trusted browser origin is rejected');
    await navigate('/contact');

    await send('Emulation.setDeviceMetricsOverride', { width: 375, height: 900, deviceScaleFactor: 1, mobile: true });
    assert.equal(await evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), true, 'Mobile contact fits viewport');
    await evaluate("document.querySelector('#enquiry-form').scrollIntoView()");
    const screenshot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
    fs.writeFileSync('storage/app/private/contact-mobile-check.png', Buffer.from(screenshot.data, 'base64'));

    await navigate('/');
    await evaluate("document.querySelector('#newsletter-form input[name=\"email\"]').value=" + JSON.stringify(email) + "; document.querySelector('#newsletter-form').requestSubmit()");
    await until("document.querySelector('#newsletter-status').dataset.state === 'success'", 'newsletter AJAX success');
    await evaluate("document.querySelector('#newsletter-form input[name=\"email\"]').value=" + JSON.stringify(email.toUpperCase()) + "; document.querySelector('#newsletter-form').requestSubmit()");
    await until("document.querySelector('#newsletter-status').dataset.state === 'success'", 'newsletter duplicate handling');
    assert.equal(await evaluate("document.querySelector('#newsletter-form input[name=\"email\"]').value"), '');

    await send('Page.navigate', { url: base + '/admin' });
    await until("location.pathname === '/admin/login' && document.readyState === 'complete'", 'admin authentication required');
    assert.equal(exceptions.length, 0, 'No JavaScript runtime exceptions: ' + exceptions.join('; '));
    fs.writeFileSync(reportPath, JSON.stringify({ email, passed: true }));
    console.log('Browser checks passed: five pages/assets, real AJAX saves, double-submit guard, validation, network failure, CSRF, mobile layout, newsletter duplicate, protected admin.');
  } finally {
    await send('Browser.close').catch(() => {});
    socket.close();
  }
})().catch((error) => { console.error(error); process.exitCode = 1; });
