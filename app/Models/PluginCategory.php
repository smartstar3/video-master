<?php namespace MotionArray\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Support\Database\CacheQueryBuilder;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Models\Traits\Amazon;
use Config;

class PluginCategory extends Uploadable
{
    use SoftDeletes;

    use CacheQueryBuilder;

    use Sluggable;

    public static $rules = [
        'slug' => 'unique:plugins,slug,{:id}',
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


    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function plugins()
    {
        return $this->hasMany('MotionArray\Models\Plugin');
    }

    public function categoryGroup()
    {
        return $this->belongsTo('MotionArray\Models\CategoryGroup');
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
}