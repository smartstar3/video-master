<?php

namespace MotionArray\Events\UserEvent\Admin;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;

class UserEnabled extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy;

    static protected $action = 'enabled';
}
