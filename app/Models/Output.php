<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Output extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    public static $rules = [];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function previewUpload()
    {
        return $this->belongsTo('MotionArray\Models\PreviewUpload');
    }
}
