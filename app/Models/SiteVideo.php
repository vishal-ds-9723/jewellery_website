<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteVideo extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const ABOUT_SLUG = 'about-showroom';

    public const ABOUT_CACHE_KEY = 'site-media.about-showroom';

    protected $fillable = ['slug'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')->useDisk('media')
            ->acceptsMimeTypes(['video/mp4'])->singleFile();
        $this->addMediaCollection('poster')->useDisk('media')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])->singleFile();
    }
}
