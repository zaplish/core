<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Settings
{
    protected array $settings;

    public function __construct()
    {
        $this->settings = Cache::rememberForever('settings.all', function () {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }

    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function all()
    {
        return $this->settings;
    }

    public function clearCache()
    {
        Cache::forget('settings.all');
    }
}
