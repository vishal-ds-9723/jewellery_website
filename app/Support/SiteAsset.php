<?php

namespace App\Support;

class SiteAsset
{
    /** Version asset URLs when files change, without a Node build dependency. */
    public static function url(string $path): string
    {
        $file = public_path($path);
        $version = is_file($file) ? filemtime($file) : null;

        return asset($path).($version !== null ? '?v='.$version : '');
    }
}
