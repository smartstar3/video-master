<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDowngrade extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['downgrade_reason', 'downgrade_feedback'];

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }
}