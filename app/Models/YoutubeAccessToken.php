<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeAccessToken extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'access_token',
        'created',
        'expires_in',
        'refresh_token'
    ];
}
