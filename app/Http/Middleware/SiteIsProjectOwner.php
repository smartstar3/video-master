<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use MotionArray\Models\Project;

class SiteIsProjectOwner
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
        $slug = Route::input('project');

        if ($id) {
            // Check to see if the seller_id matched the logged in user.
            $project = Project::find($id);
        } elseif ($slug) {
            $project = Project::where('slug', '=', $slug)->first();
        }

        if ($project && Auth::id() === (int)$project->user->id) {
            return $next($request);
        }

        return App::abort('403');
    }
}
