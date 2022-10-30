<?php namespace MotionArray\Models;

class SampleRate extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:sample_rates'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A sample rate name is required',
        'name.unique' => 'This sample rate already exists'
    ];

    /**
     * SampleRate has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_sample_rate');
    }
}
