<?php

namespace MotionArray\Http\Middleware;

use Closure;
use MotionArray\Helpers\Portfolio\PortfolioHelper;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\UserSiteRepository;
use Redirect;
use Auth;

class UserSite
{
    private $portfolio;

    private $userSite;

    public function __construct(
        PortfolioRepository $portfolio,
        UserSiteRepository $userSite
    )
    {
        $this->portfolio = $portfolio;
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
        $url = $request->url();

        $routeName = $request->route()->getName();

        $userSite = null;

        if (PortfolioHelper::isPublicMode()) {
            $userSite = $this->userSite->findByUrl($url);
        } elseif ($routeName == 'portfolio.insider-preview') {
            $siteId = $request->get('site-id');

            $userSite = $this->userSite->findById($siteId);
        } else {
            if (!Auth::check()) {
                return Redirect::to('/account/login');
            }

            $userSite = $this->userSite->findOrCreateByUser(Auth::user());
        }

        // If porfolio doesnt exists, redirect to homepage
        if (!$userSite) {
            $motionArrayUrl = config('app.url');

            return Redirect::to($motionArrayUrl, 301);
        }

        $request->attributes->add(['current_site' => $userSite]);

        return $next($request);
    }
}
