<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeen
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user-last-seen-' . $user->id;

            if (!Cache::has($cacheKey)) {
                $user->last_seen = now();
                $user->save();
                Cache::put($cacheKey, true, now()->addMinutes(1));
            }
        }

        return $next($request);
    }
}
