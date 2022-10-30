<?php

namespace MotionArray\Events\UserEvent\Admin;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;
use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

class UserCreated extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy {
        dataPayload as baseDataPayload;
        userEventLogToMessage as baseUserEventLogToMessage;
    }

    static protected $action = 'created';

    /**
     * @var array
     */
    protected $attributes;

    public function __construct(int $userId, array $attributes, User $triggeredBy)
    {
        parent::__construct($userId);
        $this->attributes = $attributes;
        $this->triggeredBy = $triggeredBy;
    }

    public function dataPayload(): array
    {
        $data = $this->baseDataPayload();

        return array_merge($data, [
            'attributes' => $this->attributes
        ]);
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        $helper = app(UserEventMessageHelper::class);
        $message = static::baseUserEventLogToMessage($model);
        $message .= ', Attributes: ' . $helper->arrayToString($model->data['attributes']);
        return $message;
    }
}
