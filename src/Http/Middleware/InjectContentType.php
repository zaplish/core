<?php

namespace App\Http\Middleware;

use Closure;

class InjectContentType
{
    public function handle($request, Closure $next, $type)
    {
        $request->attributes->set('type', $type);
        return $next($request);
    }
}
