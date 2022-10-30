<?php

namespace MotionArray\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use MotionArray\Models\Collection;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\User;
use MotionArray\Policies\CollectionPolicy;
use MotionArray\Models\Product;
use MotionArray\Policies\ModelReleasePolicy;
use MotionArray\Policies\GeneralPolicy;
use MotionArray\Policies\ProductPolicy;
use MotionArray\Services\Laravel\Gate as CustomGate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Collection::class => CollectionPolicy::class,
        ModelRelease::class => ModelReleasePolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    public function register()
    {
        $this->app->singleton(GateContract::class, function ($app) {
            return new CustomGate($app, function () use ($app) {
                return call_user_func($app['auth']->userResolver());
            });
        });

        Gate::define('feature', function (User $user, string $feature) {
            return app(GeneralPolicy::class)->feature($user, $feature);
        });
    }
}
