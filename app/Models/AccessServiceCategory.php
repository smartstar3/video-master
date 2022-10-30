<?php

namespace MotionArray\Models;

class AccessServiceCategory extends BaseModel
{
    public function accessServices()
    {
        return $this->hasMany('MotionArray\Models\AccessService');
    }
}
