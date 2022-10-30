<?php namespace MotionArray\Models;

class Compression extends BaseModel
{
    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:compressions',
        'slug' => 'required|unique:compressions'
    ];

    public static $updateRules = [
        'name' => 'required',
        'slug' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A compression name is required',
        'name.unique' => 'This compression already exists',
        'slug.required' => 'A compression slug is required',
        'slug.unique' => 'This compression already exists'
    ];

    /**
     * Format has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_compression');
    }

    public function ffmpegSlugs()
    {
        return $this->morphMany('MotionArray\Models\FfmpegSlug', 'ffmpeg_sluggable');
    }
}
