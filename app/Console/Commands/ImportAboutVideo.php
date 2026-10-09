<?php

namespace App\Console\Commands;

use App\Models\SiteVideo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ImportAboutVideo extends Command
{
    protected $signature = 'app:import-about-video {video? : Path to an MP4 file} {--poster= : Path to its poster image}';

    protected $description = 'Import the About showroom video into Media Library, preserving the source files';

    public function handle(): int
    {
        $optimized = storage_path('app/private/about-video/ra-vlog.mp4');
        $source = $this->argument('video') ?: (is_file($optimized) ? $optimized : public_path('RA VLOG.mp4'));
        $poster = $this->option('poster') ?: ($this->argument('video') ? null : storage_path('app/private/about-video/ra-vlog-poster.jpg'));

        if (! is_file($source)) {
            $this->error('Video not found: '.$source);

            return self::FAILURE;
        }
        if ($this->option('poster') && ! is_file($poster)) {
            $this->error('Poster not found: '.$poster);

            return self::FAILURE;
        }

        $video = SiteVideo::firstOrCreate(['slug' => SiteVideo::ABOUT_SLUG]);
        try {
            $this->import($video, $source, 'video', 'ra-vlog.mp4');
            if ($poster && is_file($poster)) {
                $this->import($video, $poster, 'poster', 'ra-vlog-poster.'.pathinfo($poster, PATHINFO_EXTENSION));
            }
        } finally {
            Cache::forget(SiteVideo::ABOUT_CACHE_KEY);
        }

        $this->info('About video is ready: '.$video->fresh()->getFirstMediaUrl('video'));

        return self::SUCCESS;
    }

    private function import(SiteVideo $video, string $path, string $collection, string $filename): void
    {
        $hash = hash_file('sha256', $path);
        $current = $video->getFirstMedia($collection);
        if ($current && $current->getCustomProperty('source_sha256') === $hash && is_file($current->getPath())) {
            $this->line(ucfirst($collection).' is already imported.');

            return;
        }

        $video->addMedia($path)->preservingOriginal()
            ->usingName('R.A. Parikh showroom '.$collection)
            ->usingFileName($filename)
            ->withCustomProperties(['source_sha256' => $hash])
            ->toMediaCollection($collection);
    }
}
