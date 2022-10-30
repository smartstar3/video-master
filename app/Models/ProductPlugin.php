<?php namespace MotionArray\Models;

class ProductPlugin extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:plugins'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A plugin name is required',
        'name.unique' => 'This plugin already exists'
    ];

    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_uses_plugin');
    }
}
