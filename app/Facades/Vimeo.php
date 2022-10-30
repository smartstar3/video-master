<?php

namespace MotionArray\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vimeo\Vimeo
 */
class Vimeo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'vimeo';
    }
}
