<?php

namespace MotionArray\Events\UserEvent\Concerns;

use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

trait HandlesUserEventActionsWithTriggeredBy
{
    /**
     * @var User
     */
    protected $triggeredBy;

    public function __construct(int $userId, User $triggeredBy)
    {
        parent::__construct($userId);
        $this->triggeredBy = $triggeredBy;
    }

    public function dataPayload(): array
    {
        $triggeredBy = [];
        if ($this->triggeredBy) {
            $triggeredBy = array_only($this->triggeredBy->toArray(), [
                'id',
                'email',
                'firstname',
                'lastname',
            ]);
        }

        return [
            'triggered_by_user' => $triggeredBy
        ];
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        /** @var UserEventMessageHelper $helper */
        $helper = app(UserEventMessageHelper::class);
        $triggeredBy = $model->data['triggered_by_user'];
        $triggeredByDescription = $helper->triggeredByDescription($model->user_id, $triggeredBy);
        $userDescription = "(id: '{$model->user_id}')";
        if ($model->user) {
            $userDescription = $helper->userDescription($model->user);
        }
        $action = static::action();

        return "User: {$userDescription} was {$action}, by User: " . $triggeredByDescription;
    }

    protected static function action()
    {
        return static::$action;
    }
}
