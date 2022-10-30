<?php

namespace MotionArray\Http\Controllers\API\v2;

use MotionArray\Models\User;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController
{
    public function auth(ServerRequestInterface $request)
    {
        $tokenResponse = parent::issueToken($request);
        $token = $tokenResponse->getContent();
        $tokenInfo = json_decode($token, true);

        if ($tokenResponse->getStatusCode() === 200) {
            $username = $request->getParsedBody()['username'];
            $user = User::whereEmail($username)->first();

            if (!empty($user)) {
                $user = new \MotionArray\Http\Resources\UserPrivate($user);
            }

            $tokenInfo = collect($tokenInfo);
            $tokenInfo->put('user', $user);
        } else {
            return $tokenResponse;
        }

        return $tokenInfo;
    }
}
