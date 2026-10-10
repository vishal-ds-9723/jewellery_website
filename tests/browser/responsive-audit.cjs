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
  try {
    await send('Page.enable'); await send('Runtime.enable');
    await send('Network.enable'); await send('Network.setCacheDisabled', {cacheDisabled:true});
    await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
    const results=[];
    for (const path of ['/','/about','/collections','/contact','/product?item=mens-signature-chain','/admin/login']) {
      await send('Page.navigate',{url:base+path});
      await until(`location.pathname === ${JSON.stringify(path.split('?')[0])} && document.readyState === 'complete'`,path);
      for (const [width,height] of [[320,568],[360,640],[390,844],[600,320],[768,1024],[820,1180],[1024,768],[1440,1000]]) {
        await send('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:width<=1024});
        await evaluate("window.scrollTo({top:0,behavior:'instant'})"); await delay(80);
        const info=await evaluate(`(() => {
          const off=[...document.querySelectorAll('main *')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0 && (r.right>innerWidth+1 || r.left < -1) && !e.closest('[data-horizontal-scroll],.closing-orbit,.intro-section');}).slice(0,8).map(e=>({tag:e.tagName,cls:e.className,width:e.getBoundingClientRect().width}));
          const small=[...document.querySelectorAll('input:not([type=hidden]),select,textarea')].filter(e=>e.getBoundingClientRect().width>0 && !e.closest('.form-trap')).filter(e=>parseFloat(getComputedStyle(e).fontSize)<16).length;
          return {width:innerWidth,scrollWidth:document.documentElement.scrollWidth,off,smallFields:small};
        })()`);
        results.push({path,requestedWidth:width,height,...info});
        if(width===320 || width===768) {
          await evaluate("document.querySelectorAll('.reveal').forEach(e=>e.classList.add('visible'))");
          await delay(180);
          const capture=await send('Page.captureScreenshot',{format:'png'});
          fs.writeFileSync(`storage/app/private/responsive-${path.split('?')[0].replaceAll('/','')||'home'}-${width}.png`,Buffer.from(capture.data,'base64'));
        }
      }
    }
    await send('Emulation.setDeviceMetricsOverride',{width:320,height:568,deviceScaleFactor:1,mobile:true});
    await send('Page.navigate',{url:base+'/collections'});
    await until("location.pathname === '/collections' && document.readyState === 'complete'",'collection interactions');
    assert.ok(await evaluate(`(() => {
      const tabs=[...document.querySelectorAll('[data-filter]')];
      if(tabs.length!==1 || tabs[0].textContent.trim()!=='Everyday') return false;
      tabs[0].click();
      const cards=[...document.querySelectorAll('.collection-grid .product-card')];
      const mens=cards.filter(c=>c.dataset.category==='mens');
      return cards.length===25 && document.querySelectorAll('.jewellery-set-card').length===14 && mens.length===4 && cards.every(c=>!c.hidden) && !document.querySelector('.mens-collection');
    })()`),'Everyday shows all portraits and fourteen jewellery images on a small phone');
    await send('Page.navigate',{url:base+'/product?item=mens-signature-chain'});
    await until("location.pathname === '/product' && document.readyState === 'complete'",'product interactions');
    assert.ok(await evaluate(`(() => {
      const button=document.querySelector('.zoom-button');button.click();
      const zoomed=button.getAttribute('aria-pressed')==='true' && document.documentElement.scrollWidth<=320;
      button.click();return zoomed && button.getAttribute('aria-pressed')==='false';
    })()`),'Product zoom works without horizontal page overflow');
    const adminUrl = require('node:url').pathToFileURL(require('node:path').resolve('storage/app/private/admin-responsive-preview.html')).href;
    await send('Page.navigate',{url:adminUrl});
    await until("location.protocol === 'file:' && document.readyState === 'complete'",'admin preview');
    for(const width of [320,360,600,768,1024,1440]) {
      await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<=1024});
      await delay(100);
      const info=await evaluate(`(() => {const t=document.querySelector('.table-wrap');t.scrollLeft=300;return {width:innerWidth,scrollWidth:document.documentElement.scrollWidth,smallFields:[...document.querySelectorAll('input:not([type=hidden]),select')].filter(e=>e.getBoundingClientRect().width>0 && parseFloat(getComputedStyle(e).fontSize)<16).length,tableScrollable:t.scrollWidth>t.clientWidth,scrolled:t.scrollLeft>0};})()`);
      results.push({path:'/admin (synthetic preview)',requestedWidth:width,height:900,...info});
      if(width<=600) assert.ok(info.tableScrollable && info.scrolled,'Admin table must scroll on small screens');
      if(width===320) {
        const capture=await send('Page.captureScreenshot',{format:'png'});
        fs.writeFileSync('storage/app/private/responsive-admin-320.png',Buffer.from(capture.data,'base64'));
      }
    }
    const issues=results.filter(r=>r.scrollWidth>r.requestedWidth+1 || (r.requestedWidth<=1024 && r.smallFields>0));
    assert.deepEqual(issues,[],JSON.stringify(issues));
    assert.deepEqual(errors,[]);
    fs.writeFileSync('storage/app/private/responsive-audit.json',JSON.stringify({results,errors},null,2));
    console.log(JSON.stringify({checks:results.length,issues:results.filter(r=>r.scrollWidth>r.requestedWidth+1 || (r.requestedWidth<=1024 && r.smallFields>0)),errors},null,2));
  } finally {await send('Browser.close').catch(()=>{});socket.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
