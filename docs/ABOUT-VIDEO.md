# About showroom video

The About page displays the showroom video immediately before ?Find a piece for you.?

Spatie Laravel Media Library 11 manages the video and poster through App\Models\SiteVideo. Files live on the media disk in public/media/{media-id}/. This uses Apache static-file delivery with byte-range support; PHP does not stream the video or encode it during web requests.

The source public/RA VLOG.mp4 is preserved. The prepared copy is 4,458,755 bytes, compared with the 21,336,619-byte source (about 79% smaller), with H.264 video, AAC audio, and MP4 fast-start metadata. Original dimensions are retained. The poster is a frame of the showroom interior.

## Preparing or replacing the video

FFmpeg is only needed when preparing a new video. Use an installed ffmpeg executable, pass its path with -Ffmpeg, or install the project-local helper:

    python -m pip install --target storage/framework/video-tools imageio-ffmpeg

Then run:

    .\tools\prepare-about-video.ps1
    php artisan app:import-about-video

The preparation script stores its output in storage/app/private/about-video/. It accepts -Source, -Ffmpeg, and -PosterSeconds (18 by default for the supplied video). Choose an earlier poster time if importing a shorter film.

To import another prepared MP4 and poster:

    php artisan app:import-about-video "path/to/video.mp4" --poster="path/to/poster.jpg"

The command preserves source files, skips identical imports, replaces each single-file media collection, and clears the cached About video metadata. For an explicit video path, a poster is used only when --poster is supplied. New media IDs give replacements new URLs.

## Deployment and files

Install locked Composer dependencies and run php artisan migrate. Back up both the media database records and public/media/. Restore them together, or run the import command to populate a fresh installation. The media folder is generated content and is excluded from Git. The original video is not imported during a web request.

The local XAMPP PHP template enables EXIF and GD for Media Library. If changing PHP configuration, restart the project Apache. CSS/JS are under public/css/ and public/js/; the Blade markup is resources/views/pages/about.blade.php.

## Playback behavior and verification

The player reserves its layout space and initially requests no MP4 data. Metadata loads near the viewport; muted playback begins when at least 35% is visible. It pauses offscreen or when the tab is hidden. A manual pause is respected. Reduced-motion and data-saving preferences use manual playback. Native controls allow sound, seeking, and fullscreen.

The browser check tests deferred loading, HTTP 206 byte ranges, seeking, pause/resume, manual pause, reduced motion, and a 375-pixel mobile viewport. On the local test, playback advanced 3.02 seconds during a 3-second observation on a simulated 3 Mbps / 80 ms connection. That is a local browser measurement, not a guarantee for every connection or device.

Run tests/browser/about-video.cjs with a local headless Chromium/Edge debugging endpoint at 127.0.0.1:9223 and the site at http://127.0.0.1:8088. It saves a report and mobile screenshot under storage/app/private/. PHP feature tests also cover repeatable imports, original-file preservation, cache invalidation, and placement before the closing section.

[Media Library documentation](https://spatie.be/docs/laravel-medialibrary/v11/introduction)
