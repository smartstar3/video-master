<?php

namespace MotionArray\Events\UserEvent\Admin;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;

class UserChangedSubscriptionToMonthly extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy;

    static protected $action = 'changed subscription to monthly';
}
