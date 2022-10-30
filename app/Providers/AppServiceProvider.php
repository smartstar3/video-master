<?php

namespace MotionArray\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use MotionArray\Services\FeatureFlag\FeatureFlagService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('subdomain', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]$/', $value);
        });

        // Set session lifetime to 24 hours on alternate domains
        if (!\isMotionArrayDomain()) {
            Config::set('session.lifetime', config('portfolio.session-time'));
        }

        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::default');

        Blade::withoutDoubleEncoding();

        Blade::if('iffeature', function (string $feature) {
            return app(FeatureFlagService::class)->check($feature, Auth::user());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.debug')) {
            $this->app->register(\Laracasts\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        if ($this->app->environment(['local', 'testing'])) {
            $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
            $this->app->register(\Staudenmeir\DuskUpdater\DuskServiceProvider::class);
        }
    }
}
