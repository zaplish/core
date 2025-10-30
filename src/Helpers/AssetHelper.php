<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class AssetHelper
{
    public static function versioned($path)
    {
        if (!config('app.debug')) {
            $path = preg_replace('/\.([a-z]+)$/', '.min.$1', $path);
        }

        $fullPath = public_path($path);

        $cacheKey = 'asset_hash_' . md5($path);

        if (config('app.debug')) {
            $version = time();
        } else {
            $version = Cache::rememberForever($cacheKey, function () use ($fullPath) {
                $version = file_exists($fullPath) ? filemtime($fullPath) : time();
                $version = substr(md5($version), 0, 6);
                return $version;
            });
        }

        return asset($path) . '?v=' . $version;
    }
}
