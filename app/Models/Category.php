<?php namespace MotionArray\Models;

use MotionArray\Support\Database\CacheQueryBuilder;

class Category extends BaseModel
{
    use CacheQueryBuilder;

    protected $guarded = [];

    protected $hidden = ['weight'];

    protected $casts = [
        'can_be_used_for_editorial_use' => 'boolean',
    ];

    public static $rules = [
        'name' => 'required|unique:categories'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A category name is required',
        'name.unique' => 'This category already exists'
    ];

    public function hasFreeProducts()
    {
        if ($this->products()->where("free", "=", 1)->count()) {
            return true;
        }

        return false;
    }

    /**
     * Category has many relationship with Product
     * @return HasMany Instance of HasMany
     */
    public function products()
    {
        return $this->hasMany('MotionArray\Models\Product');
    }

    /**
     * Category has many relationship with Sub Category
     * @return HasMany Instance of HasMany
     */
    public function subCategories()
    {
        return $this->hasMany('MotionArray\Models\SubCategory');
    }

    public function versions()
    {
        return $this->belongsToMany('MotionArray\Models\Version', 'category_versions');
    }

    /**
     * Calculates the total payout for this category.
     */
    public function calculatePayoutTotal($total_payout, $total_downloads, $category_downloads)
    {
        $category_percentage = $category_downloads ? (((100 / $total_downloads) * $category_downloads->count) / 100) : 0;

        return round($total_payout * $category_percentage, 2);
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function categoryType()
    {
        return $this->belongsTo('\MotionArray\Models\CategoryType');
    }
}
