<?php namespace MotionArray\Models;

class UserToken extends BaseModel
{
    protected $guarded = [];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }
}
