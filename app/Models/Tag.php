<?php namespace MotionArray\Models;

class Tag extends BaseModel
{

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:tags'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A tag name is required',
        'name.unique' => 'This tag already exists'
    ];

    /**
     * Category has many relationship with Product
     * @return HasMany Instance of HasMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_tag');
    }
}
