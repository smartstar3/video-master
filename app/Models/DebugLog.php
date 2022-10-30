<?php namespace MotionArray\Models;

/**
 * This model is only used for debugging. Code writing to this model should not be commited to production.
 */
class DebugLog extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->request_time = $_SERVER['REQUEST_TIME'];
        });
    }
}
