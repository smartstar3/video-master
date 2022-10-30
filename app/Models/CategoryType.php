<?php namespace MotionArray\Models;

use MotionArray\Support\Database\CacheQueryBuilder;

class CategoryType extends BaseModel
{
    use CacheQueryBuilder;

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:category_types'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A category type name is required',
        'name.unique' => 'This category type already exists'
    ];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function categories()
    {
        return $this->hasMany('\MotionArray\Models\Category');
    }
}