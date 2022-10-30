<?php namespace MotionArray\Models;

use App;

class ProductDownloadsCount extends BaseModel
{
    protected $table = 'product_downloads_count';

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }
}