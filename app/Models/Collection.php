<?php namespace MotionArray\Models;

use MotionArray\Support\Database\CacheQueryBuilder;
use MotionArray\Traits\PresentableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends BaseModel
{
    use SoftDeletes, PresentableTrait;

    use CacheQueryBuilder;

    protected $table = 'collections';

    protected $presenter = 'MotionArray\Presenters\CollectionPresenter';

    protected $guarded = [];

    protected $dates = ['published_at', 'deleted_at'];

    public static $rules = [
        'user_id' => 'required|exists:users,id',
        'title' => 'required',
        'slug' => 'required|unique:collections'
    ];

    public static $updateRules = [
        'user_id' => 'required|exists:users,id',
        'title' => 'required',
        'slug' => 'required|unique:collections'
    ];

    public static $messages = [
        'user_id.required' => "The collection must belong to a user",
        'user_id.exists' => "The user does not exist",
        'title.required' => "A collection title is required",
        'slug.required' => "A slug is required",
        'slug.unique' => "This collection slug is already taken"
    ];

    public $previewPrefix = 'preview-';

    public $packagePrefix = 'motion-array-';

    /**
     * This generates a unique slug for the url of the collection
     * @return String Unique slug
     */
    public function generateSlug($prefix = '', $extension = '')
    {
        $slug = \Ramsey\Uuid\Uuid::uuid4();

        return $slug;
    }

    /**
     * Collection has many to many relationship with Product
     * @return BelongToMany Instance of BelongToMany
     */
    public function products()
    {
        $downloadIds = [];

        if (\Auth::check()) {
            $downloadIds = Download::withTrashed()->where('user_id', '=', \Auth::id())->pluck('product_id')->toArray();
        }

        return $this->belongsToMany('MotionArray\Models\Product')
            ->withTimestamps()
            ->withTrashed()
            ->where(function ($query) use ($downloadIds) {
                $query->whereIn('products.id', $downloadIds);
                $query->whereNotNull('products.package_file_path');
                $query->where('products.package_file_path', '!=', '');
                $query->orWhereNull('products.deleted_at');
            });
    }

    public function collectionProducts()
    {
        return $this->hasMany(CollectionProduct::class);
    }

    public function books()
    {
        return $this->belongsToMany('MotionArray\Models\Book', 'collection_book');
    }
}
