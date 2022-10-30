<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];
}
