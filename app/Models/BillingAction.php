<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class BillingAction extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['actionable_at'];

    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function plan()
    {
        return $this->belongsTo('MotionArray\Models\Plan', 'change_to_plan_id');
    }

    public function scopeDowngrades($query)
    {
        return $query->where('action', '=', 'downgrade')->orderBy('created_at', 'DESC');
    }
}
