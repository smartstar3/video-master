<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use MotionArray\Jobs\SendProductToAlgolia;
use MotionArray\Models\StaticData\ProductStatuses;
use MotionArray\Presenters\ProductPresenter;
use MotionArray\Traits\PresentableTrait;
use MotionArray\Support\Database\CacheQueryBuilder;
use League\HTMLToMarkdown\HtmlConverter;
use MotionArray\Models\Traits\Amazon;
use MotionArray\Models\Traits\HasMusic;
use MotionArray\Models\Traits\Uploadable;
use Config;
use AWS;

/**
 * @method ProductPresenter present()
 */
class Product extends Uploadable
{
    use SoftDeletes;

    use PresentableTrait;

    use CacheQueryBuilder;

    use Amazon, HasMusic;

    protected $presenter = ProductPresenter::class;

    protected $guarded = [];

    protected $hidden = ['weight'];

    protected $dates = ['published_at', 'deleted_at', 'kick_ass_at'];

    public static $rules = [
        'seller_id' => 'required|exists:users,id',
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|max:40',
        'slug' => 'unique:products'
    ];

    public static $updateRules = [
        'seller_id' => 'required|exists:users,id',
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|max:40',
        'slug' => 'required|unique:products,slug,{:id}'
    ];

    public static $messages = [
        'seller_id.required' => "A seller is required",
        'seller_id.exists' => "The seller you've selected does not exist",
        'category_id.required' => "A category is required",
        'category_id.exists' => "The category you've selected does not exist",
        'name.required' => "A product name is required",
        'slug.unique' => "This product slug is already taken"
    ];

    public $previewPrefix = 'preview-';

    public $packagePrefix = 'motion-array-';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */

    // todo: Remove aws and meta, add only as needed (Upload requests)
    protected $appends = ['meta', 'music_url', 'is_kick_ass', 'preview_type', 'upload_type', 'previews', 'placeholder_id'];

    protected $casts = ['is_editorial_use' => 'boolean'];

    public static function previewsBucket()
    {
        return Config::get('aws.previews_bucket');
    }

    public static function bucketUrl()
    {
        return Config::get('aws.previews_s3');
    }

    public static function cdnUrl()
    {
        return Config::get("aws.previews_cdn");
    }

    public static function imgixUrl()
    {
        return Config::get("imgix.previews_url");
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function getUploadTypeAttribute()
    {
        return 'product';
    }

    public function getPreviewTypeAttribute()
    {
        return $this->category->preview_type;
    }

    /**
     * Alias for previewUrl
     *
     * @return mixed
     */
    public function getUrlAttribute()
    {
        return $this->getPreviewUrlAttribute();
    }

    /**
     * Product details Page
     *
     * @return mixed
     */
    public function getPreviewUrlAttribute()
    {
        $url = URL::route('products.details', ['category' => $this->category->slug, 'product' => $this->slug]);

        $url = str_replace('/product/', '/', $url);

        $url = str_replace('/browse/', '/', $url);

        return $url;
    }

    public function getDescriptionAttribute($value)
    {
        $converter = new HtmlConverter();

        return stripslashes($converter->convert(nl2br($value)));
    }

    /**
     * Append queue_position attribute
     *
     * @return array
     */
    public function getMetaAttribute()
    {
        // Prepare an array of sub-categories
        $subCategories = [];

        foreach ($this->subCategories as $category) {
            array_push($subCategories, $category->name);
        }

        // Prepare an array of tags
        $tags = [];

        foreach ($this->tags as $tag) {
            array_push($tags, $tag->name);
        }

        $s3 = AWS::get('s3');
        $package_url = null;
        if ($this->package_filename) {
            $bucket = Config::get("aws.packages_bucket");
            $filename = $this->package_filename . "." . $this->package_extension;
            $package_url = $s3->getObjectUrl($bucket, $filename, '+86400 minutes');
        }

        return [
            'package_url' => $package_url,
            'placeholder_image' => $this->getPlaceholder(),
            'category' => $this->category->name,
            'sub_categories' => $subCategories,
            'tags' => $tags,
            'spec' => $this->getPackageSpec(),
            'product_changes' => $this->productChanges()->get(),
            'is_video' => $this->isVideo(),
            'is_image' => $this->isImage(),
            'is_audio' => $this->isAudio()
        ];
    }

    /**
     * Generate an object containing S3 credentials.
     *
     * @return array An array of preview and package AWS credentials
     */
    public function getAwsAttribute()
    {
        $packagePolicy = $this->generateAWSPolicy('package');
        $modelReleasePolicy = $this->generateAWSPolicy('model_release');

        return [
            'preview' => $this->getAwsPreviewPolicy(),
            'package' => [
                'bucket' => $this->getBucket('package'),
                'key' => $this->getAWSKey(),
                'policy' => $packagePolicy,
                'signature' => $this->generateAWSSignature($packagePolicy),
                'bucketKey' => $this->getBucketKey('package'),
                'newFilename' => $this->generateFilename($this->packagePrefix . $this->id)
            ],
            'model_release' => [
                'bucket' => $this->getBucket('model_release'),
                'key' => $this->getAWSKey(),
                'policy' => $modelReleasePolicy,
                'signature' => $this->generateAWSSignature($modelReleasePolicy),
                'bucketKey' => $this->getBucketKey('model_release'),
                'newFilename' => $this->generateFilename($this->packagePrefix . $this->id)
            ],
        ];
    }

    public function isPublished(): bool
    {
        return $this->product_status_id == ProductStatuses::PUBLISHED_ID;
    }

    public function getIsKickAssAttribute()
    {
        $kickAsslevel = ProductLevel::getKickAssLevel();

        if ($productLevel = $this->productLevel) {
            return $productLevel->id == $kickAsslevel->id;
        }

        return false;
    }

    /**
     * Returns true if this product is linked to a request. Used to show request icon on relevant product cards.
     *
     * @return boolean
     */
    public function getIsRequestedAttribute()
    {
        return !empty($this->requests()->first());
    }

    public function getExcludedAttribute()
    {
        return $this->productSearchExclusion()->exists();
    }

    public function setIsKickAssAttribute($value)
    {
        if ($value) {
            $kickAsslevel = ProductLevel::getKickAssLevel();

            $this->product_level_id = $kickAsslevel->id;
        }
    }

    public function getPackageSpec()
    {
        $spec = [];
        $compressions = $this->compressions()->get();
        $plugins = $this->plugins()->get();
        $resolutions = $this->resolutions()->get();
        $versions = $this->versions()->get();
        $formats = $this->formats()->get();
        $fpss = $this->fpss()->get();
        $bpms = $this->bpms()->get();
        $sampleRates = $this->sampleRates()->get();

        if ($versions) {
            foreach ($versions as $key => $version) {
                $spec["version"][$key]["id"] = $version->id;
                $spec["version"][$key]["name"] = $version->name;
            }
        }

        if ($compressions) {
            foreach ($compressions as $key => $compression) {
                $spec["compression"][$key]["id"] = $compression->id;
                $spec["compression"][$key]["name"] = $compression->name;
                $spec["compression"][$key]["quality"] = "";
            }
        }

        if ($resolutions) {
            foreach ($resolutions as $key => $resolution) {
                $spec["resolution"][$key]["id"] = $resolution->id;
                $spec["resolution"][$key]["name"] = $resolution->name;

                $quality = "-custom";
                if (strpos(strtolower($resolution->name), 'hd') !== false) $quality = "-hd";
                if (strpos(strtolower($resolution->name), '4k') !== false) $quality = "-4k";
                if (strpos(strtolower($resolution->name), '2k') !== false) $quality = "-2k";

                $spec["resolution"][$key]["quality"] = $quality;
            }
        }

        if ($plugins) {
            foreach ($plugins as $key => $plugin) {
                $spec["plugins"][$key]["id"] = $plugin->id;
                $spec["plugins"][$key]["name"] = $plugin->name;
                $spec["plugins"][$key]["quality"] = "";
            }
        }

        if ($fpss) {
            foreach ($fpss as $key => $fps) {
                $spec["fps"][$key]["id"] = $fps->id;
                $spec["fps"][$key]["name"] = $fps->name;
                $spec["fps"][$key]["quality"] = "";
            }
        }

        if ($formats) {
            foreach ($formats as $key => $format) {
                $spec["format"][$key]["id"] = $format->id;
                $spec["format"][$key]["name"] = $format->name;
                $spec["format"][$key]["quality"] = "";
            }
        }

        if ($sampleRates) {
            foreach ($sampleRates as $key => $sampleRate) {
                $spec["sampleRate"][$key]["id"] = $sampleRate->id;
                $spec["sampleRate"][$key]["name"] = $sampleRate->name;
                $spec["sampleRate"][$key]["quality"] = "";
            }
        }

        if ($bpms) {
            foreach ($bpms as $key => $bpm) {
                $spec["bpm"][$key]["id"] = $bpm->id;
                $spec["bpm"][$key]["name"] = $bpm->name;
                $spec["bpm"][$key]["quality"] = "";
            }
        }

        return $spec;
    }

    public function trackDurationsToArray(): array
    {
        $isAudio = $this->isAudio();
        if ($isAudio && $this->track_durations) {
            return explode(',', str_replace(' ', '', $this->track_durations));
        }

        return [];
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships && Scopes
	|--------------------------------------------------------------------------
	*/
    public function scopePublished($query)
    {
        $publishedStatus = ProductStatus::where('status', '=', 'Published')->first();

        return $query->where('product_status_id', $publishedStatus->id);
    }

    public function seller()
    {
        return $this->owner();
    }

    public function owner()
    {
        return $this->belongsTo('MotionArray\Models\User', "seller_id", "id");
    }

    public function submission()
    {
        return $this->hasOne('MotionArray\Models\Submission');
    }

    public function parent()
    {
        return $this->category();
    }

    public function category()
    {
        return $this->belongsTo('MotionArray\Models\Category');
    }

    public function subCategories()
    {
        return $this->belongsToMany('MotionArray\Models\SubCategory', 'product_sub_category');
    }

    public function encodingStatus()
    {
        return $this->belongsTo('MotionArray\Models\EncodingStatus');
    }

    public function productStatus()
    {
        return $this->belongsTo('MotionArray\Models\ProductStatus');
    }

    public function productLevel()
    {
        return $this->belongsTo('MotionArray\Models\ProductLevel');
    }

    public function compressions()
    {
        return $this->belongsToMany('MotionArray\Models\Compression', 'product_compression');
    }

    public function plugins()
    {
        return $this->belongsToMany('MotionArray\Models\ProductPlugin', 'product_uses_plugin');
    }

    public function resolutions()
    {
        return $this->belongsToMany('MotionArray\Models\Resolution', 'product_resolution');
    }

    public function versions()
    {
        return $this->belongsToMany('MotionArray\Models\Version', 'product_version');
    }

    public function formats()
    {
        return $this->belongsToMany('MotionArray\Models\Format', 'product_format');
    }

    public function fpss()
    {
        return $this->belongsToMany('MotionArray\Models\Fps', 'product_fps');
    }

    public function bpms()
    {
        return $this->belongsToMany('MotionArray\Models\Bpm', 'product_bpm');
    }

    public function sampleRates()
    {
        return $this->belongsToMany('MotionArray\Models\SampleRate', 'product_sample_rate');
    }

    public function products()
    {
        return $this->hasMany($this, 'music_id', 'id');
    }

    public function music()
    {
        return $this->belongsTo($this);
    }

    public function tags()
    {
        return $this->belongsToMany('MotionArray\Models\Tag', 'product_tag');
    }

    public function downloads()
    {
        return $this->hasMany('MotionArray\Models\Download');
    }

    public function productChanges()
    {
        return $this->belongsToMany('MotionArray\Models\ProductChangeOption', 'product_changes');
    }

    public function downloadsCount()
    {
        return $this->hasMany('MotionArray\Models\Download')->count();
    }

    public function requests()
    {
        return $this->belongsToMany('MotionArray\Models\Request', 'request_products');
    }

    public function getRequestAttribute()
    {
        return $this->requests()->first();
    }

    public function collections()
    {
        return $this->belongsToMany('MotionArray\Models\Collection');
    }

    public function impressions()
    {
        return $this->hasMany('MotionArray\Models\ProductImpression');
    }

    public function productDownloadsCount()
    {
        return $this->hasMany('MotionArray\Models\ProductDownloadsCount');
    }

    public function productSearchExclusion()
    {
        return $this->hasOne('MotionArray\Models\ProductSearchExclusion');
    }

    public function modelReleases()
    {
        return $this->hasMany('MotionArray\Models\ModelRelease');
    }
}

Product::creating(function ($product) {
    $maUserIDs = [2, 3, 6785, 873367];

    // Cached categories
    $categories = Category::all();

    $category = $categories->where('id', $product->category_id)->first();

    if ($category) {
        $product->weight = $category->weight;
    }

    if (in_array($product->seller_id, $maUserIDs)) {
        $product->credit_seller = false;
    }
});

Product::updated(function (Product $product) {
    if ($product->isPublished() && !$product->excluded) {
        dispatch((new SendProductToAlgolia($product))->onQueue('high'));
    }
});

Product::deleting(function ($product) {
    $productRepository = App::make('MotionArray\Repositories\Products\ProductRepository');

    $productRepository->unpublish($product);
});
