<?php

namespace MotionArray\Events\UserEvent\Admin;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;

class UserForceLogoutEnabled extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy;

    static protected $action = 'force logout enabled';
}
