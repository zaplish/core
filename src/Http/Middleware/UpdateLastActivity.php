<?php

namespace Zaplish\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UpdateLastActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user-last-activity-' . $user->id;

            if (!Cache::has($cacheKey)) {
                $user->last_activity = now();
                $user->saveQuietly();
                Cache::put($cacheKey, true, now()->addMinutes(1));
            }
        }

        return $next($request);
    }
}
