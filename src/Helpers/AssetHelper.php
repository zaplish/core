<?php

namespace Zaplish\Core\Helpers;

use Illuminate\Support\Facades\Cache;

class AssetHelper
{
    /**
     * Return full URL to an admin asset.
     */
    public static function admin(string $path): string
    {
        return '/vendor/zaplish/admin/' . ltrim($path, '/');
    }

    /**
     * Return full URL to a theme asset.
     */
    public static function theme(string $path): string
    {
        $theme = config('cms.theme', 'default');
        return '/themes/' . $theme . '/' . ltrim($path, '/');
    }

    /**
     * Return a versioned asset URL (with optional .min handling).
     */
    public static function versioned(string $path): string
    {
        // Handle .min replacement only for non-debug mode
        if (!config('app.debug')) {
            $path = preg_replace('/\.([a-z]+)$/i', '.min.$1', $path);
        }

        $fullPath = public_path($path);
        $cacheKey = 'admin.assets.' . md5($path);

        if (config('app.debug')) {
            // In debug mode, always use current time for instant cache busting
            $version = time();
        } else {
            // In production, cache the version hash to avoid hitting filesystem each time
            $version = Cache::rememberForever($cacheKey, function () use ($fullPath) {
                if (file_exists($fullPath)) {
                    return substr(md5(filemtime($fullPath)), 0, 6);
                }
                return substr(md5(time()), 0, 6);
            });
        }

        return asset($path) . '?v=' . $version;
    }
}
