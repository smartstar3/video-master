<?php

namespace MotionArray\Http\Middleware;

use Auth;
use Closure;
use MotionArray\Repositories\UserSiteRepository;

class HasPortfolio
{
    private $userSite;

    public function __construct(
        UserSiteRepository $userSite
    )
    {
        $this->userSite = $userSite;
    }

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
        $userSite = null;

        if (auth()->check()) {
            $user = auth()->user();

            $userSite = $this->userSite->findByUser($user);
        }

        if (!$userSite || !$userSite->portfolio) {
            return redirect()->to('/account/uploads/portfolio#portfolio-theme');
        }

        return $next($request);
    }
}
