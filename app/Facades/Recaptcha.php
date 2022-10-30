<?php namespace MotionArray\Facades;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;

class Recaptcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ReCaptcha\ReCaptcha::class;
    }
}
