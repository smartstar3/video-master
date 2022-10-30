<?php

namespace MotionArray\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'MotionArray\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            if (isMotionArrayDomain()) {
                require app_path('Http/Routes/admin.php');
                require app_path('Http/Routes/site.php');
            }

            require app_path('Http/Routes/portfolio.php');
            require app_path('Http/Routes/internal-api.php');
        });

        Route::group([
            'namespace' => $this->namespace,
        ], function ($router) {
            require app_path('Http/Routes/api.php');
            require app_path('Http/Routes/adobe-panel.php');
        });
    }
}
