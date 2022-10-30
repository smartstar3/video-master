<?php namespace MotionArray\Http\Controllers\Site;

use View;
use Request;
use Redirect;
use Response;

class ElementsController extends BaseController
{

    public function elements()
    {
        if (Request::ajax()) {
            return Response::json([
                'nav-browse' => addslashes(View::make('layouts._partials.site.nav-browse')->render()),
                'nav-account' => addslashes(View::make('layouts._partials.site.nav-account')->render()),
                'nav-browse-smartphone' => addslashes(View::make('layouts._partials.site.nav-browse-smartphone')->render()),
                'nav-account-smartphone' => addslashes(View::make('layouts._partials.site.nav-account-smartphone')->render()),
                'search' => addslashes(View::make('layouts._partials.site.search')->render()),
                'latest-products' => addslashes(View::make('site._partials.latest-products')->render()),
                'growth' => addslashes(View::make('site._partials.growth')->render()),
                'nav-categories' => addslashes(View::make('layouts._partials.site.nav-secondary.categories')->render()),
                'nav-sign-up' => addslashes(View::make('layouts._partials.site.nav-secondary.sign-up')->render()),
                'contact-form' => addslashes(View::make('site._partials.contact-form')->render())
            ], 200);
        }

        return Redirect::to('/');
    }

    public function plans()
    {
        return Response::json([
            'plans' => addslashes(View::make('site._partials.plans')->render())
        ], 200);
    }

    public function signUp()
    {
        return Response::json([
            'sign-up-form' => addslashes(View::make('site._partials.sign-up-form')->render()),
            'producer-sign-up-form' => addslashes(View::make('site._partials.producer-sign-up')->render()),
            'free-product-preview' => addslashes(View::make('site._partials.free-product-preview')->render())
        ], 200);
    }
}
