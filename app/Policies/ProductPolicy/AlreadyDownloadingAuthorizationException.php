<?php

namespace MotionArray\Policies\ProductPolicy;

use MotionArray\Policies\Concerns\HasStaticErrorMessage;
use MotionArray\Policies\PolicyAuthorizationException;

class AlreadyDownloadingAuthorizationException extends PolicyAuthorizationException
{
    use HasStaticErrorMessage;

    protected $message = 'user is already downloading this file';
}
