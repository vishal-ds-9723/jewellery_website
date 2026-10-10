# R.A. Parikh Jewellers — Laravel / Blade

The working website is now a Laravel application with Blade pages, AJAX appointment enquiries, and newsletter subscriptions stored in MySQL. The original static site is grouped in public/legacy-site/. Edit resources/views, public/css, and public/js for the running site. See [project structure](docs/PROJECT-STRUCTURE.md).

## Local development

Requirements: PHP 8.4+ (the installed lock file uses Symfony 8), Composer, and MySQL or compatible MariaDB with PDO MySQL enabled. This machine uses PHP 8.5 and the existing local MariaDB service.

Local setup has been run: .env points to database ra_parikh on 127.0.0.1:3306, and the migrations have been applied. No frontend build is required; existing CSS, JavaScript, images, and logos live in public/.

For the optimized XAMPP setup, run from this folder in PowerShell:

    .\tools\start-xampp.ps1

Open http://127.0.0.1:8088. See [XAMPP performance setup and measured results](docs/XAMPP-PERFORMANCE.md) for configuration, stopping/restarting Apache, cache refresh, and repeatable load checks. This project uses XAMPP Apache with a separate PHP 8.5 configuration because the main XAMPP configuration points to an older PHP version.

For a simple development-only server, php artisan serve --host=127.0.0.1 --port=8000 is also available. Do not open index.html directly or use a static Live Server for the forms.

## Fresh installation

1. Run composer install.
2. Copy .env.example to .env if it does not already exist.
3. Set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD in .env.
4. Create that empty MySQL database with utf8mb4 / utf8mb4_unicode_ci.
5. Run php artisan key:generate once for a new installation.
6. Run php artisan migrate.
7. Run php artisan optimize, then .\tools\start-xampp.ps1 (or php artisan serve for simple development).

Local root/blank-password database settings are for this machine only. Use a dedicated database user and password on hosting.

## Administrator

Create an account from the terminal, entering your own name and private password when prompted:

    php artisan app:create-admin your-email@example.com

Then open /admin/login. There is no public registration route and no default administrator password.

The dashboard lists enquiries and newsletter signups, filters enquiries by status or preferred date, updates enquiry statuses, and exports matching enquiries to CSV. Access requires an administrator account. Account passwords are hashed; the is_admin flag cannot be assigned through public forms.

## Form behavior

- POST /enquiries: name, email, contact number, optional shopping interest, preferred date, morning/evening preference, and message.
- POST /newsletter: email.
- Laravel web middleware handles sessions and request-forgery protection. Laravel 13 accepts trusted same-origin browser metadata or a valid CSRF token. AJAX sends FormData with the CSRF token and requests JSON.
- Form requests validate all data on the server. Invalid fields return HTTP 422.
- Only a successful database write produces a success response and clears the form.
- AJAX disables the submit button while pending, preserves input after errors, and handles validation, network failures, timeouts, expired sessions, and rate limits.
- Enquiries have a unique submission UUID so retrying an uncertain request does not insert it twice. Emails are normalized and newsletter emails are unique.
- Honeypot fields and per-IP rate limits reduce automated abuse.
- Appointment dates use Asia/Kolkata. A morning/evening preference is a request, not an automatically confirmed booking.
- Submission timestamps are stored in UTC and displayed in India time in the dashboard.
- New enquiries queue a branded thank-you email for the submitting visitor. Live delivery requires the Gmail sender settings and a running worker; see [enquiry email setup](docs/ENQUIRY-EMAIL.md). Newsletter campaigns and automatic appointment confirmation are not enabled.
- Existing .html URLs redirect to their Laravel routes, preserving query strings.

## Project files

- resources/views/pages/: home, about, collections, contact, product
- resources/views/layouts/site.blade.php: shared page layout
- resources/views/partials/: navigation, footer, CSRF/honeypot fields
- resources/views/admin/: login and enquiry dashboard
- public/js/forms.js: shared AJAX submission behavior
- app/Http/Requests/: validation
- app/Http/Controllers/: form storage and admin actions
- app/Models/: enquiries and newsletter subscribers
- database/migrations/: versioned database schema
- routes/web.php: page, submission, legacy URL, and admin routes

## Verification

    composer test
    php artisan optimize
    node --check public/js/forms.js

Verification completed locally: 33 automated tests (234 assertions), compiled Blade templates, valid Composer metadata, and real browser AJAX submissions persisted in MySQL. Browser checks also covered missing/invalid CSRF credentials without trusted origin metadata, retained input after server/network errors, duplicate clicks, newsletter deduplication, mobile viewport fit, and protected admin access. Synthetic database records were removed after verification.

Automated feature tests use SQLite in memory, not the local MySQL database. They cover form persistence, validation, duplicate requests, spam fields, India-date handling, rate limits, database failure handling, non-JavaScript submissions, administrator access, login/logout, escaped messages, CSV export, cache privacy, stable cursor pagination, bounded queries, and export limits.

## About video

The showroom video above the closing About section is managed by Spatie Media Library. See [video preparation, import, and playback](docs/ABOUT-VIDEO.md).

## Hosting

Point the domain document root to this project's public/ directory. Laravel's Apache rewrite file is already there. Never serve the entire project root: .env, application code, logs and dependencies belong outside the document root.

Set APP_ENV=production, APP_DEBUG=false, APP_URL to the HTTPS site URL, SESSION_SECURE_COOKIE=true, and the hosting database credentials. Keep the application key stable. Make storage/ and bootstrap/cache/ writable by the web server.

Deploy with:

    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan optimize

Create the administrator using the command above. Arrange regular database backups through your hosting provider. Do not run migrate:fresh against customer data.

Laravel references: https://laravel.com/docs/13.x/validation and https://laravel.com/docs/13.x/csrf
