<?php

namespace MotionArray\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use MotionArray\Http\Middleware\Terminable;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \MotionArray\Http\Middleware\TrimStrings::class,
//        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \MotionArray\Http\Middleware\TrustProxies::class,
        Terminable::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Run PageCache middleware after we have Auth::user() available,
            // as it only caches non-authenticated requests.
            \MotionArray\Http\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class, // Logout all sessions after password change
            \MotionArray\Http\Middleware\PageCache::class,

            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \MotionArray\Http\Middleware\VerifyCsrfToken::class,

            \MotionArray\Http\Middleware\UpdateIp::class
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // @todo figure out how to handle this conflict of auth middleware.
        // 'auth' => \MotionArray\Http\Middleware\Authenticate::class,
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \MotionArray\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'admin.auth' => \MotionArray\Http\Middleware\AdminAuth::class,
        'site.auth' => \MotionArray\Http\Middleware\SiteAuth::class,
        'invalidate_cache' => \MotionArray\Http\Middleware\InvalidateCache::class,
        'site.isSeller' => \MotionArray\Http\Middleware\SiteIsSeller::class,
        'site.isSubmissionOwner' => \MotionArray\Http\Middleware\SiteIsSubmissionOwner::class,
        'site.isProductOwner' => \MotionArray\Http\Middleware\SiteIsProductOwner::class,
        'site.isProjectOwner' => \MotionArray\Http\Middleware\SiteIsProjectOwner::class,
        'site.subscription' => \MotionArray\Http\Middleware\SiteSubscription::class,
        'site.signedUser' => \MotionArray\Http\Middleware\UserPassedBySignature::class,

        'clockwork' => \MotionArray\Http\Middleware\Clockwork::class,
        'portfolioOwner' => \MotionArray\Http\Middleware\PortfolioOwner::class,
        'portfolioThemeOwner' => \MotionArray\Http\Middleware\PortfolioThemeOwner::class,
        'requestOwner' => \MotionArray\Http\Middleware\SiteIsRequestOwner::class,

        'userSite' => \MotionArray\Http\Middleware\UserSite::class,
        'hasPortfolio' => \MotionArray\Http\Middleware\HasPortfolio::class,
        'portfolio.isPublished' => \MotionArray\Http\Middleware\PortfolioPublished::class,

        'comments.manage' => \MotionArray\Http\Middleware\ManageComments::class,
    ];
}
