<?php

namespace Tests\Feature;

use App\Models\SiteVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutVideoTest extends TestCase
{
    use RefreshDatabase;

    private function importVideo(): string
    {
        Storage::fake('media');
        // Minimal MP4 file signature; no decoder or large real video needed in unit tests.
        Storage::disk('media')->put('source.mp4', hex2bin('00000018667479706d703432000000006d70343269736f6d').str_repeat("\0", 256));
        $path = Storage::disk('media')->path('source.mp4');
        $this->artisan('app:import-about-video', ['video' => $path])->assertSuccessful();

        return $path;
    }

    public function test_import_preserves_source_is_repeatable_and_invalidates_cached_metadata(): void
    {
        $path = $this->importVideo();
        $originalHash = hash_file('sha256', $path);
        $first = SiteVideo::firstOrFail()->getFirstMedia('video');
        Cache::put(SiteVideo::ABOUT_CACHE_KEY, ['url' => 'outdated'], 3600);
        $this->artisan('app:import-about-video', ['video' => $path])->assertSuccessful();

        $this->assertDatabaseCount('site_videos', 1);
        $this->assertDatabaseCount('media', 1);
        $this->assertSame($first->id, SiteVideo::firstOrFail()->getFirstMedia('video')->id);
        $this->assertSame($originalHash, hash_file('sha256', $path));
        $this->assertFileExists($first->getPath());
        $this->assertFalse(Cache::has(SiteVideo::ABOUT_CACHE_KEY));
    }

    public function test_about_uses_library_video_before_closing_section_and_caches_metadata(): void
    {
        $this->importVideo();
        $url = SiteVideo::firstOrFail()->getFirstMediaUrl('video');
        $this->get('/about')->assertOk()
            ->assertSee($url, false)
            ->assertSeeInOrder(['id="showroom-film"', 'Find a piece'], false)
            ->assertSee('preload="none"', false)
            ->assertSee('controls muted loop playsinline', false)
            ->assertDontSee('RA VLOG.mp4')
            ->assertHeader('Cache-Control', 'max-age=60, public');

        DB::enableQueryLog();
        try {
            $response = $this->get('/about')->assertOk();
            $this->assertCount(0, DB::getQueryLog(), 'Cached video metadata should avoid per-request database reads.');
            $this->assertEmpty($response->headers->getCookies());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_about_still_renders_before_a_video_has_been_imported(): void
    {
        $this->get('/about')->assertOk()->assertSee('Find a piece')
            ->assertDontSee('id="showroom-film"', false);
    }
}
