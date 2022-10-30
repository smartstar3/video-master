<?php

namespace MotionArray\Events\UserEvent\Admin;

use Illuminate\Support\Facades\Auth;
use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;
use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

class UserUpdated extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy {
        dataPayload as baseDataPayload;
        userEventLogToMessage as baseUserEventLogToMessage;
    }

    static protected $action = 'updated';

    /**
     * @var array
     */
    protected $attributes;

    public function __construct(int $userId, array $attributes, User $triggeredBy = null)
    {
        parent::__construct($userId);
        $this->triggeredBy = $triggeredBy;
        $this->attributes = $attributes;
    }

    public function dataPayload(): array
    {
        $data = $this->baseDataPayload();

        return array_merge($data, [
            'new_attributes' => $this->attributes
        ]);
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        $helper = app(UserEventMessageHelper::class);
        $message = static::baseUserEventLogToMessage($model);
        $message .= ', New Attributes: ' . $helper->arrayToString($model->data['new_attributes']);
        return $message;
    }
}
