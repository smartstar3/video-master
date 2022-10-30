<?php

namespace MotionArray\Support\UserEvents;

use Config;
use MotionArray\Events\UserEvent;

class UserEventLogger
{
    protected $enabled = false;

    public function __construct()
    {
        $this->enabled = Config::get('logging.user_events.enabled');
    }

    public function log(UserEvent $event)
    {
        if ($this->enabled) {
            $model = $event->toUserEventLog();
            $model->save();
        }
    }
}
