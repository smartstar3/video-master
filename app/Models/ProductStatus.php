<?php namespace MotionArray\Models;

class ProductStatus extends BaseModel
{
    protected $guarded = [];

    public static $rules = [];

    public function products()
    {
        return $this->hasMany('MotionArray\Models\Product');
    }
}
