<?php namespace MotionArray\Models;

class Fps extends BaseModel
{
    protected $table = 'fpss';

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:fpss',
        'slug' => 'required|unique:fpss'
    ];

    public static $updateRules = [
        'name' => 'required',
        'slug' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A fps name is required',
        'name.unique' => 'This fps already exists',
        'slug.required' => 'A fps slug is required',
        'slug.unique' => 'This fps already exists'
    ];

    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_fps');
    }

    public function ffmpegSlugs()
    {
        return $this->morphMany('MotionArray\Models\FfmpegSlug', 'ffmpeg_sluggable');
    }
}
