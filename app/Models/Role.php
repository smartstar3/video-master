<?php namespace MotionArray\Models;

use MotionArray\Support\Database\CacheQueryBuilder;

class Role extends BaseModel
{
    use CacheQueryBuilder;

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:roles'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public function users()
    {
        return $this->belongsToMany('MotionArray\Models\User', "user_role");
    }
}
