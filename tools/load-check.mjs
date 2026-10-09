import { performance } from 'node:perf_hooks';
import { writeFileSync } from 'node:fs';

const base = new URL(process.argv[2] || 'http://127.0.0.1:8088');
if (!['127.0.0.1', 'localhost', '[::1]'].includes(base.hostname)) throw new Error('This check is restricted to your local server.');
const count = Number(process.argv[3] || 1000);
const concurrency = Number(process.argv[4] || 16);
const label = process.argv[5] || 'after';
if (!Number.isInteger(count) || count < 1 || count > 10000 || !Number.isInteger(concurrency) || concurrency < 1 || concurrency > 64 || !/^[a-z-]+$/.test(label)) throw new Error('Use 1-10000 requests, 1-64 workers, and a simple report label.');
const paths = ['/', '/about', '/collections', '/contact', '/product?item=noor'];
for (const path of paths) {
  const r = await fetch(new URL(path, base));
  if (!r.ok) throw new Error('Warmup failed: ' + path + ' ' + r.status);
  await r.arrayBuffer();
}
const samples = [];
const statuses = {};
let next = 0;
const start = performance.now();
await Promise.all(Array.from({length: concurrency}, async () => {
  while (next < count) {
    const i = next++;
    const before = performance.now();
    let status = 'network-error';
    try {
      const r = await fetch(new URL(paths[i % paths.length], base), { signal: AbortSignal.timeout(15000) });
      await r.arrayBuffer();
      status = String(r.status);
    } catch {}
    samples.push(performance.now() - before);
    statuses[status] = (statuses[status] || 0) + 1;
  }
}));
samples.sort((a,b) => a-b);
const elapsed = (performance.now() - start) / 1000;
const percentile = (p) => Math.round(samples[Math.min(samples.length - 1, Math.ceil(samples.length * p) - 1)]);
const report = { base: base.href, requests: count, concurrency, seconds: +elapsed.toFixed(2), requests_per_second: +(count / elapsed).toFixed(2), p50_ms: percentile(.5), p95_ms: percentile(.95), p99_ms: percentile(.99), statuses, note: 'Local, warm-cache, closed-loop HTML GET test. No assets or form writes. Not a production capacity guarantee.' };
writeFileSync('storage/app/private/load-' + label + '.json', JSON.stringify(report, null, 2));
console.log(JSON.stringify(report, null, 2));
if (statuses['200'] !== count) process.exitCode = 1;
