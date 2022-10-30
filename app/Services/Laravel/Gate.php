<?php

namespace MotionArray\Services\Laravel;

use MotionArray\Policies\PolicyAuthorizationException;
use MotionArray\Services\Laravel\Auth\Access\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Gate as BaseGate;

class Gate extends BaseGate
{
    public function authorizeResult($ability, $arguments = []): Response
    {
        try {
            $result = $this->raw($ability, $arguments);

            if ($result instanceof Response) {
                return $result;
            }

            return new Response();

        } catch (PolicyAuthorizationException $e) {
            return new Response($e);
        } catch (AuthorizationException $e) {
            return new Response($e->getMessage());
        }
    }
}
