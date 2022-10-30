<?php

namespace MotionArray\Events\UserEvent\Admin;

use Illuminate\Support\Facades\Auth;
use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

class UserRoleChanged extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy {
        dataPayload as baseDataPayload;
        userEventLogToMessage as baseUserEventLogToMessage;
    }

    static protected $action = 'role ids changed';

    /**
     * @var array
     */
    protected $newRoleIds;

    public function __construct(int $userId, array $newRoleIds, User $triggeredBy = null)
    {
        parent::__construct($userId);
        if (!$triggeredBy) {
            $triggeredBy = Auth::user();
        }
        $this->triggeredBy = $triggeredBy;
        $this->newRoleIds = $newRoleIds;
    }

    public function dataPayload(): array
    {
        $data = $this->baseDataPayload();

        return array_merge($data, [
            'new_role_ids' => $this->newRoleIds
        ]);
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        $message = static::baseUserEventLogToMessage($model);
        $message .= ', New Role Ids: ' . implode(',', $model->data['new_role_ids']);
        return $message;
    }
}
