<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use MotionArray\Models\Request;

class SiteIsRequestOwner
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
        // Admin users can do what they want!
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        $id = Route::input('id');

        if ($id) {
            $productRequest = Request::find($id);
        }

        if ($productRequest && Auth::id() === (int)$productRequest->user_id) {
            return $next($request);
        }

        return App::abort('403');
    }
}
