<?php

namespace MotionArray\Events\UserEvent;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActions;

class UserDowngradedByPaymentFailure extends UserEvent
{
    use HandlesUserEventActions;

    static protected $action = 'downgraded by payment failure';
}
