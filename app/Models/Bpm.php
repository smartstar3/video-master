<?php namespace MotionArray\Models;

class Bpm extends BaseModel
{
    protected $table = 'bpms';

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:bpms'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_bpm');
    }
}
