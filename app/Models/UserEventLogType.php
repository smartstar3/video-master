<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class UserEventLogType extends Model
{
    public $timestamps = false;

    protected $table = 'log__user_event_log_types';

    protected $fillable = [
        'id',
        'event_class'
    ];

    public function userEventLogs()
    {
        return $this->belongsToMany(UserEventLog::class);
    }
}
