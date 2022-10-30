<?php namespace MotionArray\Models;

class ProductLevel extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:product_levels'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A product level name is required',
        'name.unique' => 'This product level already exists'
    ];

    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->hasMany('MotionArray\Models\Product');
    }

    public static function getKickAssLevel()
    {
        return static::where('label', '=', 'kick-ass')->first();
    }
}
