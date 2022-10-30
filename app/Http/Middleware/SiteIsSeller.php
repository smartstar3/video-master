<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App;

class SiteIsSeller
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
        if (!Auth::user()->isSeller()) {
            return App::abort('403'); // Forbidden
        }

        return $next($request);
    }
}
