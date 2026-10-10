const fs = require('node:fs');
const assert = require('node:assert/strict');
const base = 'http://127.0.0.1:8000';
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
    await send('Network.setCacheDisabled', {cacheDisabled:true});
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    await send('Page.navigate', { url: base + '/' });
    await until("document.readyState === 'complete' && document.querySelector('.showroom-hero.is-ready, .showroom-hero.is-fallback')", 'home loaded');
    const results = [];
    for (const [width, height] of [[320,568],[360,640],[390,844],[600,320],[768,1024],[820,1180],[1024,768],[1100,800],[1280,800],[1440,1000]]) {
      await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width <= 1100 });
      await evaluate("window.scrollTo({top:0,behavior:'instant'})");
      await delay(150);
      const state = await evaluate(`(() => {
        const header = document.querySelector('.site-header').getBoundingClientRect();
        const scene = document.querySelector('.showroom-scene').getBoundingClientRect();
        const copy = document.querySelector('.showroom-copy').getBoundingClientRect();
        return {width:innerWidth,scrollWidth:document.documentElement.scrollWidth,headerBottom:header.bottom,sceneTop:scene.top,sceneBottom:scene.bottom,copyTop:copy.top,imageFit:getComputedStyle(document.querySelector('.showroom-day')).objectFit,menu:getComputedStyle(document.querySelector('.main-nav')).display,button:getComputedStyle(document.querySelector('.menu-button')).display};
      })()`);
      assert.ok(state.scrollWidth <= width + 1, `Page overflow at ${width}: ${JSON.stringify(state)}`);
      assert.equal(state.imageFit, width <= 600 ? 'cover' : 'contain');
      if (width <= 600 && height > 500) assert.ok(state.sceneBottom - state.sceneTop >= 240, 'Mobile showroom should have a substantial image area');
      if (width <= 1100) {
        assert.equal(state.menu, 'none');
        assert.ok(state.sceneTop >= state.headerBottom - 1, `Header covers hero at ${width}`);
        assert.ok(state.copyTop >= state.sceneBottom - 1, `Text covers image at ${width}`);
        await evaluate("document.querySelector('.menu-button').click()");
        const menu = await evaluate(`(() => {
          const nav=document.querySelector('.main-nav'); const cta=nav.querySelector('.appointment-cta');
          return {expanded:document.querySelector('.menu-button').getAttribute('aria-expanded'),display:getComputedStyle(nav).display,ctaCount:document.querySelectorAll('.site-header .appointment-cta').length,ctaWidth:cta.getBoundingClientRect().width,href:cta.getAttribute('href'),bottom:nav.getBoundingClientRect().bottom};
        })()`);
        assert.equal(menu.expanded, 'true');
        assert.equal(menu.display, 'flex');
        assert.equal(menu.ctaCount, 1);
        assert.ok(menu.ctaWidth > 0 && menu.ctaWidth <= width);
        assert.ok(menu.href.endsWith('/contact?appointment=video-call#enquiry-form'));
        assert.ok(menu.bottom <= height + 1, `Menu extends past viewport at ${width}x${height}`);
        if (width === 320) {
          const capture=await send('Page.captureScreenshot',{format:'png'});
          fs.writeFileSync('storage/app/private/home-mobile-menu.png',Buffer.from(capture.data,'base64'));
        }
        await send('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});
        assert.equal(await evaluate("document.querySelector('.menu-button').getAttribute('aria-expanded')"),'false');
        assert.equal(await evaluate("document.activeElement === document.querySelector('.menu-button')"),true);
        await evaluate("document.querySelector('.menu-button').click(); document.querySelector('.showroom-copy').click()");
        assert.equal(await evaluate("document.querySelector('.menu-button').getAttribute('aria-expanded')"),'false');
      } else {
        assert.equal(state.menu,'flex');
        assert.equal(state.button,'none');
      }
      if ([320,390,768,1440].includes(width)) {
        const capture=await send('Page.captureScreenshot',{format:'png'});
        fs.writeFileSync(`storage/app/private/home-responsive-${width}.png`,Buffer.from(capture.data,'base64'));
      }
      results.push({width,height,passed:true});
    }
    // Normal motion keeps desktop animation, while the compact header stays readable.
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
    assert.equal(await evaluate("getComputedStyle(document.querySelector('.site-header')).animationName"), 'showroom-nav-reveal');
    await send('Emulation.setDeviceMetricsOverride',{width:375,height:812,deviceScaleFactor:1,mobile:true});
    await delay(150);
    assert.equal(await evaluate("getComputedStyle(document.querySelector('.site-header')).animationName"), 'none');
    assert.equal(await evaluate("getComputedStyle(document.querySelector('.showroom-copy')).opacity"), '1');
    await evaluate("document.querySelector('.menu-button').click()");
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:800,deviceScaleFactor:1,mobile:false});
    await delay(150);
    assert.equal(await evaluate("document.querySelector('.menu-button').getAttribute('aria-expanded')"),'false');
    // Shared navigation must remain usable on the other public pages.
    await send('Emulation.setDeviceMetricsOverride',{width:375,height:812,deviceScaleFactor:1,mobile:true});
    for (const path of ['/about','/collections','/contact']) {
      await send('Page.navigate',{url:base+path});
      await until(`location.pathname === ${JSON.stringify(path)} && document.readyState === 'complete' && !!document.querySelector('#main-navigation')`,path+' loaded');
      await evaluate("document.querySelector('.menu-button').click()");
      assert.equal(await evaluate("getComputedStyle(document.querySelector('.main-nav')).display"),'flex',path);
    }
    await send('Page.navigate',{url:base+'/product?item=noor'});
    await until("location.pathname === '/product' && document.readyState === 'complete'",'product loaded');
    assert.equal(await evaluate("document.querySelector('.site-header') === null"),true);
    assert.deepEqual(errors,[]);
    fs.writeFileSync('storage/app/private/home-responsive-check.json',JSON.stringify({passed:true,viewports:results,sharedNavigation:true},null,2));
    console.log(JSON.stringify({passed:true,viewports:results,sharedNavigation:true},null,2));
  } finally {
    await send('Browser.close').catch(()=>{});
    socket.close();
  }
})().catch(error=>{console.error(error);process.exitCode=1;});
