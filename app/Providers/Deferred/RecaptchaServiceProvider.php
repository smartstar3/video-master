<?php

namespace MotionArray\Providers\Deferred;

use Illuminate\Support\ServiceProvider;
use ReCaptcha\ReCaptcha;

class RecaptchaServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->bind(ReCaptcha::class, function() {
            $secret = \Config::get('recaptcha.secret_key');

            return new \ReCaptcha\ReCaptcha($secret);
        });
    }

    public function provides()
    {
        return [ReCaptcha::class];
    }
}
