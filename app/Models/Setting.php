<?php namespace MotionArray\Models;

use MotionArray\Support\Database\CacheQueryBuilder;

class Setting extends BaseModel
{
    use CacheQueryBuilder;
    protected $fillable = [];
}