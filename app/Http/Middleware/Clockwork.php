<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Event;
use App;

class Clockwork
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
        if (App::environment() !== "production") {
            Event::fire('clockwork.controller.start');

            $response = $next($request);

            Event::fire('clockwork.controller.end');
        }

        return $next($request);
    }
}