<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Request;

class PortfolioPublished
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
        $site = Request::get('current_site');

        $portfolio = $site->portfolio;

        if (!$portfolio || !$portfolio->public || !$portfolio->last_published_at)
            abort(404);

        return $next($request);
    }
}
