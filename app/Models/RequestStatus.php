<?php namespace MotionArray\Models;

class RequestStatus extends BaseModel
{
    protected $guarded = [];

    public static $rules = [];

    public function requests()
    {
        return $this->hasMany('MotionArray\Models\Request', 'id', 'request_status_id');
    }
}
