<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Redirect;

class SiteAuth
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
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guest()) {
            return Redirect::guest('account/login');
        }

        if (Auth::user()->disabled) {
            Auth::logout();

            return Redirect::guest('account/login');
        }

        if (Auth::user()->forceLogOut()) {
            // Reset force_log_out flag.
            Auth::user()->setForceLogOut(0);

            Auth::logout();

            return Redirect::guest('account/login');
        }

        return $next($request);
    }
}
