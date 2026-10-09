const fs = require('node:fs');
const assert = require('node:assert/strict');
const base = 'http://127.0.0.1:8088';
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

(async () => {
  let target;
  for (let i = 0; i < 50; i++) {
    try { target = await fetch('http://127.0.0.1:9223/json/new?about:blank', { method: 'PUT' }).then(r => r.json()); break; }
    catch (error) { if (i === 49) throw error; await delay(100); }
  }
  const socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
  let id = 0;
  const pending = new Map();
  const errors = [];
  socket.onmessage = event => {
    const result = JSON.parse(event.data);
    if (result.id && pending.has(result.id)) {
      const task = pending.get(result.id);
      pending.delete(result.id);
      clearTimeout(task.timer);
      result.error ? task.reject(new Error(result.error.message)) : task.resolve(result.result);
    }
    if (result.method === 'Runtime.exceptionThrown') errors.push(result.params.exceptionDetails.text);
  };
  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const key = ++id;
    const timer = setTimeout(() => { pending.delete(key); reject(new Error('CDP timeout: ' + method)); }, 20000);
    pending.set(key, { resolve, reject, timer });
    socket.send(JSON.stringify({ id: key, method, params }));
  });
  const evaluate = async expression => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
    return result.result.value;
  };
  const until = async (expression, label) => {
    for (let i = 0; i < 150; i++) {
      if (await evaluate(expression)) return;
      await delay(100);
    }
    throw new Error('Timeout: ' + label);
  };
  const scrollToVideo = () => evaluate("document.querySelector('#showroom-film').scrollIntoView({behavior:'instant',block:'center'})");
  const scrollToTop = () => evaluate("window.scrollTo({top:0,behavior:'instant'})");
  const video = "document.querySelector('#showroom-film')";
  try {
    await send('Page.enable');
    await send('Runtime.enable');
    await send('Network.enable');
    await send('Network.setCacheDisabled', { cacheDisabled: true });
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
    await send('Page.navigate', { url: base + '/about' });
    await until("location.pathname === '/about' && document.readyState === 'complete'", 'about loaded');
    assert.equal(await evaluate(video + '.paused'), true);
    assert.equal(await evaluate("performance.getEntriesByType('resource').some(r => r.name.endsWith('.mp4'))"), false, 'Video not downloaded on initial page load');
    assert.equal(await evaluate("Boolean(document.querySelector('.showroom-film-section').compareDocumentPosition(document.querySelector('.closing-section')) & Node.DOCUMENT_POSITION_FOLLOWING)"), true, 'Video precedes closing section');

    const url = await evaluate(video + ".querySelector('source').src");
    const range = await fetch(url, { headers: { Range: 'bytes=0-1023' } });
    assert.equal(range.status, 206);
    assert.equal(range.headers.get('content-type'), 'video/mp4');
    assert.equal((await range.arrayBuffer()).byteLength, 1024);
    assert.ok(range.headers.get('content-range').startsWith('bytes 0-1023/'));

    // A 3 Mbps connection, with 80 ms latency; enough for this encoded source.
    await send('Network.emulateNetworkConditions', { offline: false, latency: 80, downloadThroughput: 375000, uploadThroughput: 125000, connectionType: 'cellular4g' });
    await scrollToVideo();
    await until(video + '.currentTime > 2 && !' + video + '.paused', 'muted playback under bandwidth limit');
    const startedAt = await evaluate(video + '.currentTime');
    await delay(3000);
    const advancedBy = await evaluate(video + '.currentTime') - startedAt;
    assert.ok(advancedBy > 2, 'Video continues playing under bandwidth limit');
    const quality = await evaluate(video + '.getVideoPlaybackQuality().toJSON?.() || ({totalVideoFrames:' + video + '.getVideoPlaybackQuality().totalVideoFrames,droppedVideoFrames:' + video + '.getVideoPlaybackQuality().droppedVideoFrames})');
    await send('Network.emulateNetworkConditions', { offline: false, latency: 0, downloadThroughput: -1, uploadThroughput: -1 });
    await evaluate(video + '.currentTime = 12');
    await until('!' + video + '.seeking && ' + video + '.currentTime >= 12', 'seeking');
    await scrollToTop();
    await until(video + '.paused', 'offscreen pause');
    await scrollToVideo();
    await until('!' + video + '.paused', 'resume when visible');
    await evaluate(video + '.pause()');
    await delay(150);
    await scrollToTop();
    await delay(150);
    await scrollToVideo();
    await delay(400);
    assert.equal(await evaluate(video + '.paused'), true, 'Manual pause is respected');

    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    await send('Emulation.setDeviceMetricsOverride', { width: 375, height: 900, deviceScaleFactor: 1, mobile: true });
    await send('Page.navigate', { url: base + '/about' });
    await until("document.readyState === 'complete' && !!document.querySelector('#showroom-film')", 'mobile about');
    await scrollToVideo();
    await delay(800);
    assert.equal(await evaluate(video + '.paused'), true, 'Reduced motion prevents autoplay');
    assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth + 1'), true);
    const screenshot = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('storage/app/private/about-video-mobile.png', Buffer.from(screenshot.data, 'base64'));
    await evaluate(video + '.play()');
    await until(video + '.currentTime > 0.5', 'manual playback with reduced motion');
    assert.equal(errors.length, 0, errors.join('; '));
    const report = { passed: true, range_requests: 206, throttled_mbps: 3, playback_advance_in_3_seconds: +advancedBy.toFixed(2), quality, mobile_width: 375, reduced_motion: true, manual_pause: true };
    fs.writeFileSync('storage/app/private/about-video-check.json', JSON.stringify(report, null, 2));
    console.log(JSON.stringify(report, null, 2));
  } finally {
    await send('Browser.close').catch(() => {});
    socket.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
