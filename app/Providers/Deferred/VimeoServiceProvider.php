<?php

namespace MotionArray\Providers\Deferred;

use Illuminate\Support\ServiceProvider;
use Vimeo\Vimeo;

class VimeoServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Vimeo::class, function ($app) {
            $config = $app['config']['services.vimeo'];

            return new Vimeo($config['key'], $config['secret'], $config['token']);
        });

        $this->app->alias(Vimeo::class, 'vimeo');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Vimeo::class, 'vimeo'];
    }
}
