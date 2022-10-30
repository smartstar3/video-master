<?php

namespace MotionArray\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use MotionArray\Facades\Flash;

class SiteSubscription
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
        /**
         * Whitelist of routes excluded from this check.
         */
        $EXCLUDED_ROUTES = [
            'AccountsController@index',
            'AccountsController@billing',
            'AccountsController@upgrade',
            'AccountsController@subscription',
            'AccountsController@downgrade',
            'AccountsController@invoices',
            'AccountsController@deleteConfirm',
            'UsersController@update',
            'UsersController@updateCard',
            'UsersController@cancelSubscription',
            'UsersController@resumeSubscription',
            'UsersController@changeSubscription',
            'UsersController@createSubscription',
            'UsersController@downloadInvoice'
        ];

        $current_action = explode('\\', Route::currentRouteAction());
        $current_action = $current_action[count($current_action) - 1];

        // Check if subscription has expired.
        if (!in_array($current_action, $EXCLUDED_ROUTES) && !Auth::user()->isSubscriptionActive()) {
            Flash::danger("Uh oh! We did not receive payment for your membership, and we have now put your account on hold.<br>Please update your payment details to access your previous downloads and to continue downloading new content.", "locked");

            return Redirect::to('account/billing');
        }

        return $next($request);
    }
}
