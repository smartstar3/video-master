<?php namespace MotionArray\Composers;

use MotionArray\Models\Collection;
use Auth;

class CollectionsComposer
{

    public function compose($view)
    {
        $view->with('collections', Auth::user()->collections->unique());
    }

}
