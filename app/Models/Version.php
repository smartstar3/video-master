<?php namespace MotionArray\Models;

class Version extends BaseModel
{
    protected $casts = [
        'is_retired' => 'boolean',
    ];

    protected $guarded = [];

    public static $rules = [
        'name' => 'required|unique:versions'
    ];

    public static $updateRules = [
        'name' => 'required'
    ];

    public static $messages = [
        'name.required' => 'A format name is required',
        'name.unique' => 'This format already exists'
    ];

    /**
     * Format has many to many relationship with Product
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany Instance of BelongToMany
     */
    public function products()
    {
        return $this->belongsToMany('MotionArray\Models\Product', 'product_version');
    }

    public function categories()
    {
        return $this->belongsToMany('MotionArray\Models\Category', 'category_versions');
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
