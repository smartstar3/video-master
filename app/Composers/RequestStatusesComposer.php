<?php namespace MotionArray\Composers;

use MotionArray\Models\RequestStatus;

class RequestStatusesComposer
{
    public function compose($view)
    {
        $view->with('requestStatuses', RequestStatus::orderBy("name", "asc")->get());
    }
}