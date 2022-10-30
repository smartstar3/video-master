<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class UserSiteStatusCheck extends Model
{
    protected $fillable = ['user_site_id', 'domain', 'is_success', 'last_request_timestamp'];

    public $timestamps = false;

    public function userSite()
    {
        return $this->belongsTo('MotionArray\Models\UserSite');
    }
}
