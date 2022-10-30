<?php

namespace MotionArray\Models;

class AccessService extends BaseModel
{
    public function users()
    {
        return $this->belongsToMany('MotionArray\Models\User', 'user_access_service');
    }

    public function accessServiceCategory()
    {
        return $this->belongsTo('MotionArray\Models\accessServiceCategory');
    }
}