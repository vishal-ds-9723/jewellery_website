<?php

namespace App\Http\Controllers;

use App\Models\SiteVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $showroomVideo = Cache::remember(SiteVideo::ABOUT_CACHE_KEY, 3600, function (): array {
            $video = SiteVideo::with('media')->where('slug', SiteVideo::ABOUT_SLUG)->first();

            return [
                'url' => $video?->getFirstMediaUrl('video') ?? '',
                'poster' => $video?->getFirstMediaUrl('poster') ?? '',
            ];
        });

        // A fresh deployment may have the video file but no imported media yet.
        // Check outside the cache so a previously cached empty library cannot hide it.
        if (empty($showroomVideo['url'])) {
            foreach (['videos/ra-vlog.mp4', 'RA VLOG.mp4'] as $path) {
                if (is_file(public_path($path))) {
                    $showroomVideo = [
                        'url' => asset(str_replace(' ', '%20', $path)),
                        'poster' => is_file(public_path('assets/ra-vlog-poster.jpg'))
                            ? asset('assets/ra-vlog-poster.jpg') : '',
                    ];

                    break;
                }
            }
        }

        return view('pages.about', compact('showroomVideo'));
    }
}
