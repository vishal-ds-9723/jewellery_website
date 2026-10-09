import assert from 'node:assert/strict';
import { randomUUID } from 'node:crypto';
import { writeFileSync } from 'node:fs';
const base = 'http://127.0.0.1:8088';
const email = 'qa-' + randomUUID() + '@example.com';
const report = 'storage/app/private/concurrent-check.json';
writeFileSync(report, JSON.stringify({ email, passed: false }));
const sessions = await Promise.all(Array.from({ length: 5 }, async () => {
  const response = await fetch(base + '/contact');
  assert.equal(response.status, 200);
  const html = await response.text();
  const token = html.match(/name="csrf-token" content="([^"]+)"/)?.[1];
  assert.ok(token);
  const cookie = response.headers.getSetCookie().map(c => c.split(';')[0]).join('; ');
  return { token, cookie };
}));
const submissionId = randomUUID();
const date = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date(Date.now() + 86400000));
const post = async (path, session, data) => {
  const response = await fetch(base + path, {
    method: 'POST', signal: AbortSignal.timeout(15000),
    headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', Cookie: session.cookie, 'X-CSRF-TOKEN': session.token },
    body: new URLSearchParams({ ...data, _token: session.token }),
  });
  await response.text();
  return response.status;
};
const enquiries = await Promise.all(sessions.map(s => post('/enquiries', s, {
  submission_id: submissionId, name: 'QA Concurrent Check', email, phone: '+91 9876543210',
  appointment_date: date, appointment_slot: 'morning', message: 'Concurrent duplicate verification.',
})));
const subscribers = await Promise.all(sessions.slice(0, 3).map(s => post('/newsletter', s, { email })));
assert.deepEqual(enquiries.sort(), [200, 200, 200, 200, 201]);
assert.deepEqual(subscribers, [200, 200, 200]);
writeFileSync(report, JSON.stringify({ email, passed: true, enquiries, subscribers }));
console.log('Parallel POSTs passed: 5 matching enquiry submissions and 3 matching newsletter submissions; now verify MySQL and clean fixtures.');
