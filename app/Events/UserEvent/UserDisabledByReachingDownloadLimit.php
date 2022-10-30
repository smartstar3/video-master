<?php

namespace MotionArray\Events\UserEvent;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActions;

class UserDisabledByReachingDownloadLimit extends UserEvent
{
    use HandlesUserEventActions;

    static protected $action = 'disabled by reaching download limit';
}
