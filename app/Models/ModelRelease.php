<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Releases are forms signed by models that appear in stock videos.
 * We need to store these signed forms, but not make them available to users.
 */
class ModelRelease extends BaseModel
{
    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product');
    }
}
