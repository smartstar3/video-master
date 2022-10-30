<?php

namespace MotionArray\Policies;

use Illuminate\Auth\Access\AuthorizationException;

/**
 * All policy authorization exceptions should extend this class.
 */
class PolicyAuthorizationException extends AuthorizationException
{
}
