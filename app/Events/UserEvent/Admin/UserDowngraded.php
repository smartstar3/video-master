<?php

namespace MotionArray\Events\UserEvent\Admin;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;

class UserDowngraded extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy;

    static protected $action = 'downgraded';
}
