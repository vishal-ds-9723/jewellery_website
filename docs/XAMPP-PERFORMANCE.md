# XAMPP performance setup

The local website runs at **http://127.0.0.1:8088** using XAMPP's Apache, PHP 8.5, and the existing MariaDB service. The installed Composer lock requires PHP 8.4 or newer. This machine's main XAMPP Apache configuration points to PHP 8.3, so this project has its own Apache/PHP configuration and port.

## Start and stop

From the project directory in PowerShell:

    .\tools\start-xampp.ps1
    .\tools\stop-xampp.ps1

Start MySQL in XAMPP first if its service is stopped. The project launcher does not change the global XAMPP configuration. Its Apache runs separately from the XAMPP Control Panel Apache button. By default it uses C:\xampp and C:\php85; the launcher accepts -XamppRoot, -PhpRoot, and -Port overrides. PHP must be a compatible 64-bit thread-safe build with php8apache2_4.dll and the configured extensions.

Apache serves only public/, listens on loopback, and writes logs under storage/logs/xampp-*.log. This is a local development/performance setup, not a public hosting deployment.

## Applied changes

- OPcache: 256 MB, 20,000 script entries, timestamp checks every 2 seconds. PHP memory limit 256 MB/request, execution timeout 30 seconds, body limit 1 MB.
- Apache: 64 worker threads and short, 2-second idle keep-alive timeout. These are initial settings for this machine, not universal capacity settings.
- Laravel configuration, route, event, and Blade caches are built with php artisan optimize. APP_DEBUG is false locally.
- Apache requests load environment values without Laravel's process-wide putenv adapter. This avoids modifying shared process environment during concurrent requests. The original load check intermittently fell back to missing/default environment settings. Configuration caching also removes .env parsing from normal requests.
- About, collections, and product pages skip session/CSRF middleware because they have no forms or personalized content. They have a 60-second public cache lifetime and send no session cookies. If you add a form or personalized content, restore session middleware and private caching first.
- Home, contact, submissions, and admin responses remain private and are not stored in shared caches. Form CSRF and validation remain enabled.
- CSS/JS responses are compressed and cached for one day. Blade adds file modification versions to their URLs, so edits get new URLs. Unversioned images/video have a one-hour cache lifetime.
- Below-fold images use native lazy loading and asynchronous decoding. The showroom images remain eager so the hero transition is preserved.
- Dashboard lists use cursor pagination (20 records), avoiding full-table counts and deep OFFSET scans. There are no exact-total/page-number queries.
- Appointment-date filtering compares the DATE column directly. A compound index on status, appointment_date, id supports combined dashboard filters. Existing individual date/status indexes remain.
- CSV exports stream 500-row batches, default to at most 5,000 matching records, and allow two export attempts per administrator per minute. Set EXPORT_MAX_ROWS in .env if necessary (hard maximum 10,000); filter larger lists by date/status.
- Existing unique submission IDs and newsletter email constraints prevent duplicate inserts under retries. Public form rate limits are unchanged.
- File sessions/cache remain suitable for this single-machine setup. No Redis or global MySQL tuning is required. Enquiry confirmations now use a dedicated database queue and mail worker; see [email setup](ENQUIRY-EMAIL.md). Success is returned after MySQL commits both the enquiry and pending confirmation.

## Development and cache refresh

After changing routes, config, Blade templates, or .env:

    php artisan optimize:clear

Before performance checks, and after finishing edits:

    php artisan optimize

For regression tests, clear configuration first so PHPUnit uses its isolated SQLite database:

    composer test

Then restore runtime caches with php artisan optimize. Do not run migrations or tests configured against customer data. Tests and cache rebuilding should not run during a benchmark.

After editing deploy/xampp/*.template, stop and restart the project Apache. Generated configs live in storage/framework/xampp and are ignored by Git. PHP timestamp checks remain enabled for local work; a restart clears OPcache immediately.

## Validation

25 automated tests / 176 assertions passed. Coverage includes validation, persistence, retries, rate limits, admin authorization, CSV escaping, stable pagination during inserts, bounded database reads, and cache privacy. Automated tests use SQLite in memory.

Real HTTP concurrency verification sent five matching enquiries and three matching newsletter signups through Apache with valid session/CSRF credentials. MySQL contained exactly one record of each type. Synthetic rows were removed.

The browser check visits all five pages, scrolls lazy images into view, exercises real AJAX submissions, double-click prevention, server validation, network failures, CSRF rejection, mobile layout, newsletter deduplication, and protected admin routes. Its MySQL verifier removes only that run's generated test records.

### Repeatable load checks

    node tools/load-check.mjs http://127.0.0.1:8088 1000 16 after
    node tools/load-check.mjs http://127.0.0.1:8088 2000 32 stress

Arguments are base URL, total request count, concurrent workers, and report label. The script only accepts loopback destinations, uses 15-second request timeouts, and caps runs at 10,000 requests / 64 workers. Reports are written under storage/app/private/load-*.json.

The workload cycles five HTML pages with warm caches. It does not fetch images, emulate user think-time, or submit forms. It uses a closed loop with fixed concurrency on the same machine as the server. Results measure this local setup; they do not establish thousands of requests per second or thousands of simultaneous users.

Measured on this machine on 9 October 2026, with PHP 8.5 OPcache enabled in both baseline and optimized runs:

| Run | Requests | Workers | Time | Requests/sec | p95 | HTTP 200 | HTTP 500 |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| Before application/cache changes | 1,000 | 16 | 21.22 s | 47.13 | 579 ms | 997 | 3 |
| Optimized | 1,000 | 16 | 13.12 s | 76.19 | 459 ms | 1,000 | 0 |
| Larger optimized batch | 2,000 | 32 | 22.15 s | 90.30 | 472 ms | 2,000 | 0 |

An additional 1,000-request / 16-worker run with configuration caching cleared also returned 1,000 HTTP 200 responses, verifying the Apache environment fix during development (24.81 s; p95 652 ms). Optimized caches were restored afterward.

The equal-size run took about 38% less time. These are individual local runs, not repeated statistical capacity estimates. Apache gzip and browser cache headers were checked separately over HTTP.

Before claiming a hosting capacity, test a production-like server with an external load generator, realistic page/asset/form traffic, gradual arrival-rate increases, and a sustained run. Measure p95/p99 latency, unexpected errors, CPU, memory, MySQL connections/slow queries, and disk I/O. Keep legitimate form submissions separate from deliberate rate-limit traffic.

References: [Laravel configuration caching](https://github.com/laravel/docs/blob/13.x/configuration.md), [PHP environment behavior](https://bugs.php.net/bug.php?id=71607), [OPcache](https://www.php.net/manual/en/opcache.configuration.php), [Apache compression](https://httpd.apache.org/docs/2.4/mod/mod_deflate.html).
