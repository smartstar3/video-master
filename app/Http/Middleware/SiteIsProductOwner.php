<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use MotionArray\Models\Product;

class SiteIsProductOwner
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

        // Check to see if the seller_id matched the logged in user.
        $product = Product::find(Route::input('id'));

        if ($product && Auth::id() === (int)$product->seller_id) {
            return $next($request);
        }

        return App::abort('403');
    }
}
