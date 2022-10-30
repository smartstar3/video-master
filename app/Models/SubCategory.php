<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Support\Database\CacheQueryBuilder;

class SubCategory extends BaseModel
{
    use CacheQueryBuilder, SoftDeletes;

    protected $guarded = [];

    public static $rules = [
        'name' => 'required'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A sub category name is required',
        'name.unique' => 'This sub category already exists'
    ];


    /**
     * SubCategory has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function category()
    {
        return $this->belongsTo('MotionArray\Models\Category');
    }

    /**
     * SubCategory has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_sub_category');
    }
}
