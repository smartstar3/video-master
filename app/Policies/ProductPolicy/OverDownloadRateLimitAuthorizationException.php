<?php

namespace MotionArray\Policies\ProductPolicy;

use MotionArray\Policies\Concerns\HasStaticErrorMessage;
use MotionArray\Policies\PolicyAuthorizationException;

class OverDownloadRateLimitAuthorizationException extends PolicyAuthorizationException
{
    use HasStaticErrorMessage;

    protected $message = 'user is not a paying member';
}
