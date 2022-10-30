<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;
use MotionArray\Models\StaticData\UserEventLogTypes;

class UserEventLog extends Model
{
    protected $table = 'log__user_event_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_event_log_type_id',
        'data',
        'created_at',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userEventLogType()
    {
        return $this->hasOne(UserEventLogType::class);
    }

    public function userEventClass(): string
    {
        return (new UserEventLogTypes)->idToEventClassOrFail($this->user_event_log_type_id);
    }

    public function userEventMessage(): string
    {
        $eventClass = $this->userEventClass();
        return $eventClass::userEventLogToMessage($this);
    }

    public function getCreatedAtAttribute()
    {
        if ($this->attributes['created_at']) {
            return $this->asDateTime($this->attributes['created_at']);
        }
    }
}
