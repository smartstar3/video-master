<?php namespace MotionArray\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Support\Database\CacheQueryBuilder;

class CategoryGroup extends BaseModel
{
    use SoftDeletes;

    use CacheQueryBuilder;

    use Sluggable;

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:category_groups'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A category type name is required',
        'name.unique' => 'This category type already exists'
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => ['name']
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function getUrlAttribute()
    {
        $categories = $this->categories->toArray();

        return '/browse?categories=' . implode(',', array_pluck($categories, 'slug'));
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function categories()
    {
        return $this->hasMany('\MotionArray\Models\Category')->orderBy('sidebar_order');
    }

    public function pluginCategories()
    {
        return $this->hasMany('\MotionArray\Models\PluginCategory')->orderBy('order');
    }
}