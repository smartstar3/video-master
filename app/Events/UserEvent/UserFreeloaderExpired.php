<?php

namespace MotionArray\Events\UserEvent;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\UserEventLog;

class UserFreeloaderExpired extends UserEvent
{
    public static function userEventLogToMessage(UserEventLog $model): string
    {
        /** @var UserEventMessageHelper $helper */
        $helper = app(UserEventMessageHelper::class);
        $userDescription = $helper->userDescription($model->user);

        return "User: {$userDescription} freeloader expired";
    }

    protected function dataPayload(): array
    {
        return [];
    }
}
