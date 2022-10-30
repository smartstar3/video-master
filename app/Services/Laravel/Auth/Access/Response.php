<?php

namespace MotionArray\Services\Laravel\Auth\Access;

use MotionArray\Policies\PolicyAuthorizationException;

class Response
{
    /**
     * True when response allows access.
     *
     * @var bool
     */
    protected $allowed;

    /**
     * The response message.
     *
     * @var string|null
     */
    protected $message;

    /**
     * @var PolicyAuthorizationException|null
     */
    protected $exception;

    /**
     * Create a new response.
     *
     * @param PolicyAuthorizationException|string $authorizationException
     */
    public function __construct($authorizationException = null)
    {
        $this->allowed = true;

        if ($authorizationException instanceof PolicyAuthorizationException) {
            $this->allowed = false;
            $this->exception = $authorizationException;
        }

        if (is_string($authorizationException)) {
            $this->allowed = false;
            $this->message = $authorizationException;
        }
    }

    public function allowed()
    {
        return $this->allowed;
    }

    public function denied()
    {
        return !$this->allowed;
    }

    /**
     * Get the response message.
     *
     * @return string|null
     */
    public function message()
    {
        if ($this->exception) {
            return $this->exception->getMessage();
        }

        return $this->message;
    }

    /**
     * Check if the exception this instance contains is of the given type
     *
     * @param $policyAuthorizationExceptionClass
     * @return bool
     */
    public function is($policyAuthorizationExceptionClass): bool
    {
        if (!$this->exception) {
            return false;
        }
        return $this->exception instanceof $policyAuthorizationExceptionClass;
    }

    /**
     * Get the string representation of the message.
     *
     * @return string|null
     */
    public function __toString()
    {
        return $this->message();
    }
}
