<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use MotionArray\Models\Project;
use MotionArray\Models\ProjectComment;

class ManageComments
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

        $commentId = Route::input('commentId');

        $comment = ProjectComment::find($commentId);

        if ($comment) {
            $projectId = Route::input('projectId');

            $user = Project::find($projectId)->user;

            if ($comment->isOwner || Auth::id() === $user->id) {
                return $next($request);
            }
        }

        return App::abort('405');
    }
}
