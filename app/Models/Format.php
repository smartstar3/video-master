<?php namespace MotionArray\Models;

class Format extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:formats'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A format name is required',
        'name.unique' => 'This format already exists'
    ];


    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_format');
    }
}
