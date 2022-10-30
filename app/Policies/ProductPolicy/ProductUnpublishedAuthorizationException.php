<?php

namespace MotionArray\Policies\ProductPolicy;

use MotionArray\Policies\Concerns\HasStaticErrorMessage;
use MotionArray\Policies\PolicyAuthorizationException;

class ProductUnpublishedAuthorizationException extends PolicyAuthorizationException
{
    use HasStaticErrorMessage;

    protected $message = 'product is unpublished';
}
