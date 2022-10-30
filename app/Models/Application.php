<?php

namespace MotionArray\Models;

class Application extends BaseModel
{
    public $timestamps = false;

    public function versions()
    {
        return $this->hasMany(Version::class);
    }
}
