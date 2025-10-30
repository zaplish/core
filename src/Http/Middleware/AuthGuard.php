<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthGuard
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (Auth::check()) {
            if ($user && !$user->active) {
                Auth::logout();

                return redirect()->route('admin.login');
            }
        }

        return $next($request);
    }
}
