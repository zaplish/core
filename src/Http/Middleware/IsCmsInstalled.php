<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCmsInstalled
{
    public function handle(Request $request, Closure $next)
    {
        $isInstalled = \App\Models\User::count() > 0 || file_exists(storage_path('installed.lock'));

        if (!$isInstalled) {
            // Allow access to the installer
            if ($request->is('admin/install') || $request->is('admin/install/*')) {
                return $next($request);
            }

            // Redirect everything else to the installer
            return redirect()->route('admin.install');
        }

        // If CMS is already installed, prevent access to /install
        if ($isInstalled && ($request->is('admin/install') || $request->is('admin/install/*'))) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
