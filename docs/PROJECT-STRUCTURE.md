# Project structure

The active website is Laravel. Its document root is public/, and requests enter through public/index.php.

    app/
      Console/Commands/       Admin creation and media import
      Http/Controllers/       Pages, forms, and administration
      Http/Requests/           Server validation
      Models/                 Database and Media Library models
      Support/                Asset URL versioning
    bootstrap/
    config/
    database/migrations/
    deploy/xampp/             Project Apache/PHP templates
    docs/
    public/
      index.php               Laravel entry point
      .htaccess               Rewrites, compression, static caching
      css/                    Live website styles
      js/                     Live website JavaScript
      assets/                 Live logos, jewellery images, existing videos
      images/                 Live editorial images
      media/                  Generated Media Library files; excluded from Git
      RA VLOG.mp4             Preserved source video
      legacy-site/            Original static site, kept together as reference
        *.html
        *.css
        *.js
        assets/
        images/
        about-content.txt
    resources/
      views/
        layouts/
        partials/
        pages/                Active Blade pages
        admin/
      css/                    Laravel/Vite starter source
      js/                     Laravel/Vite starter source
    routes/web.php
    storage/
      app/private/
        reference/            Original ZIP and temporary Laravel scaffold
        about-video/          Prepared video and poster
      framework/
      logs/
    tests/
    tools/

Edit resources/views/ and the live public/css/, public/js/, public/assets/, or public/images/ files for website changes. The original static site remains available at /legacy-site/index.html as a design reference; its old forms are not the Laravel forms.

Core Laravel files such as artisan, composer.json, composer.lock, package.json, vite.config.js, phpunit.xml, and .env stay at the project root. The live site currently serves versioned CSS/JS directly, so a Vite build is not needed.

After changing cached Blade templates or routes, run php artisan optimize:clear for development or php artisan optimize to refresh the optimized caches. The normal website URL remains http://127.0.0.1:8088.
