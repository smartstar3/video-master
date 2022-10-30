<?php

namespace MotionArray\Events\UserEvent\Concerns;

use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\UserEventLog;

trait HandlesUserEventActions
{
    public function dataPayload(): array
    {
        return [];
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        /** @var UserEventMessageHelper $helper */
        $helper = app(UserEventMessageHelper::class);
        $userDescription = "(id: '{$model->user_id}')";
        if ($model->user) {
            $userDescription = $helper->userDescription($model->user);
        }
        $action = static::action();

        return "User: {$userDescription} {$action}";
    }

    protected static function action()
    {
        return static::$action;
    }
}
