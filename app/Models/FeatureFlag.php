<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class, 'feature_flag_users', 'feature_flag_id', 'user_id');
    }
}
