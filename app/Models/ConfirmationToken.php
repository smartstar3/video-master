<?php namespace MotionArray\Models;

class ConfirmationToken extends BaseModel
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }
}
