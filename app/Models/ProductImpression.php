<?php namespace MotionArray\Models;

use App;

class ProductImpression extends BaseModel
{
    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }
}
