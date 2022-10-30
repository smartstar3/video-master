<?php namespace MotionArray\Models;

class RequestUpvote extends BaseModel
{
    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo('MotionArray\Models\Request');
    }

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }
}
