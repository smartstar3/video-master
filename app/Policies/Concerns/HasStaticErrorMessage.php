<?php

namespace MotionArray\Policies\Concerns;

use Throwable;

trait HasStaticErrorMessage
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = $this->message;
        parent::__construct($message, $code, $previous);
    }
}
