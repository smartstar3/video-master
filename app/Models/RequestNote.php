<?php namespace MotionArray\Models;

class RequestNote extends BaseModel
{
    protected $guarded = [];

    public static $rules = [];

    public function request()
    {
        return $this->belongsTo('MotionArray\Models\Request');
    }

    public function status()
    {
        return $this->belongsTo('MotionArray\Models\RequestStatus');
    }

    public function reviewer()
    {
        return $this->belongsTo('MotionArray\Models\User', 'reviewer_id', 'id');
    }
}
