<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioTheme;

class PortfolioThemeOwner
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

        // Check portfolio theme owner.
        $portfolioTheme = PortfolioTheme::find(Route::input('themeId'));

        if ($portfolioTheme && Auth::id() === (int)$portfolioTheme->user_id) {
            return $next($request);
        }

        return App::abort('403');
    }
}
