<?php namespace MotionArray\Models;

class Resolution extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:resolutions'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A resolution name is required',
        'name.unique' => 'This resolution already exists'
    ];

    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_resolution');
    }

    public function ffmpegSlug()
    {
        return $this->morphOne('MotionArray\Models\FfmpegSlug', 'ffmpeg_sluggable');
    }
}
