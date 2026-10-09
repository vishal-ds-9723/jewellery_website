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

        return view('pages.about', compact('showroomVideo'));
    }
}
