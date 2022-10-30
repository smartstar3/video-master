<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    public const DEFAULT_CLIENT_ID = 1;
    public const AFTER_EFFECT_CLIENT_ID = 2;
    public const PREMIERE_PRO_CLIENT_ID = 3;

    protected $table = 'oauth_clients';
}
