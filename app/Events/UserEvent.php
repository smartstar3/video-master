<?php

namespace MotionArray\Events;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\UserEventLog;

abstract class UserEvent
{
    protected $userId;
    protected $createdAt;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->createdAt = Carbon::now();
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function createdAt(): Carbon
    {
        return $this->createdAt;
    }

    public function toUserEventLog(): UserEventLog
    {
        $userEventLogTypeId = (new UserEventLogTypes())->classToIdOrFail(static::class);
        return new UserEventLog([
            'user_id' => $this->userId,
            'user_event_log_type_id' => $userEventLogTypeId,
            'created_at' => $this->createdAt,
            'data' => $this->dataPayload()
        ]);
    }

    abstract protected function dataPayload(): array;

    abstract public static function userEventLogToMessage(UserEventLog $model): string;
}
