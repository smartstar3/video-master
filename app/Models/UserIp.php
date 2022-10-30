<?php namespace MotionArray\Models;

class UserIp extends BaseModel
{
    protected $fillable = ['user_id', 'ip'];

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }
}