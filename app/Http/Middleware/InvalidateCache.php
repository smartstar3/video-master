<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Redirect;

class InvalidateCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        setcookie('cache.clear', true, time() + 3600, '/');

        return $next($request);
    }
}
