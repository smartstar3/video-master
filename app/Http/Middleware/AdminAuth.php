<?php

namespace MotionArray\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Redirect;

class AdminAuth
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
        if (Auth::guest()) return Redirect::guest('mabackend/login');

        $allowed_roles = [1, 2];
        $roles = Auth::user()->roles()->get();
        $passes = false;
        foreach ($roles as $role) {
            if (in_array($role->id, $allowed_roles)) {
                $passes = true;
                break;
            }
        }

        if (!$passes) {
            return Redirect::guest('mabackend/login');
        }

        return $next($request);
    }
}
