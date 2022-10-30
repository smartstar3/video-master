<?php namespace MotionArray\Providers;

use Illuminate\Support\ServiceProvider;

class FlashServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('flash', function () {
            return $this->app->make("MotionArray\Notifications\FlashNotifier");
        });
    }
}
