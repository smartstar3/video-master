<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Models\Traits\Uploadable;
use MotionArray\Models\Traits\Amazon;
use Config;
use MotionArray\Presenters\PluginPresenter;
use MotionArray\Traits\PresentableTrait;

/**
 * @method PluginPresenter present()
 */
class Plugin extends Uploadable
{
    use SoftDeletes;

    use PresentableTrait;

    use Amazon;

    use Sluggable;

    public static $rules = [
        'slug' => 'unique:plugins,slug,{:id}',
    ];

    protected $presenter = PluginPresenter::class;

    protected $fillable = ['name', 'description'];

    protected $dates = ['deleted_at'];

    public $previewPrefix = 'preview-';

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

    public static function previewsBucket()
    {
        return Config::get('aws.portfolio_previews_bucket');
    }

    public static function bucketUrl()
    {
        return Config::get('aws.portfolio_previews_s3');
    }

    public static function cdnUrl()
    {
        return Config::get("aws.portfolio_previews_cdn");
    }

    public static function imgixUrl()
    {
        return Config::get("imgix.portfolio_url");
    }

    /*
	|--------------------------------------------------------------------------
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getUploadTypeAttribute()
    {
        return 'plugin';
    }

    public function getPreviewTypeAttribute()
    {
        return 'video';
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function category()
    {
        return $this->belongsTo('MotionArray\Models\PluginCategory');
    }

    // todo: remove
    public function parent()
    {
        return $this->category();
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
    public function isNew()
    {
        return Carbon::now()->diffInDays($this->created_at) < 30;
    }
}
