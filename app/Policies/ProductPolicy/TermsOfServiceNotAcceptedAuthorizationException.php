<?php

namespace MotionArray\Policies\ProductPolicy;

use MotionArray\Policies\Concerns\HasStaticErrorMessage;
use MotionArray\Policies\PolicyAuthorizationException;

class TermsOfServiceNotAcceptedAuthorizationException extends PolicyAuthorizationException
{
    use HasStaticErrorMessage;

    protected $message = 'user has not accepted terms of service';
}
