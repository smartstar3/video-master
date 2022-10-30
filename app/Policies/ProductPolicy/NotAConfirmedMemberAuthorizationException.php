<?php

namespace MotionArray\Policies\ProductPolicy;

use MotionArray\Policies\Concerns\HasStaticErrorMessage;
use MotionArray\Policies\PolicyAuthorizationException;

class NotAConfirmedMemberAuthorizationException extends PolicyAuthorizationException
{
    use HasStaticErrorMessage;

    protected $message = 'User is not Confirmed by email';
}
