<?php

namespace MotionArray\Repositories\OAuth;

use MotionArray\Models\OAuthAccessToken;
use MotionArray\Models\User;
use MotionArray\Models\OAuthClient;

class AccessTokenRepository
{
    /**
     * @var OAuthAccessToken
     */
    private $model;

    public function __construct(OAuthAccessToken $accessToken)
    {
        $this->model = $accessToken;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasUsedAdobePanel(User $user)
    {
        // oauth token expiration time is 1 year. This is close enough for our purposes.
        $accessTokenCount = $this->model->where('user_id', $user->id)
            ->whereIn('client_id', [OAuthClient::AFTER_EFFECT_CLIENT_ID, OAuthClient::PREMIERE_PRO_CLIENT_ID])
            ->count();

        return (bool)$accessTokenCount;
    }
}
