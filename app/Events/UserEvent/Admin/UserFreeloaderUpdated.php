<?php

namespace MotionArray\Events\UserEvent\Admin;

use Illuminate\Support\Facades\Auth;
use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActionsWithTriggeredBy;
use MotionArray\Events\UserEvent\UserEventMessageHelper;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

class UserFreeloaderUpdated extends UserEvent
{
    use HandlesUserEventActionsWithTriggeredBy {
        dataPayload as baseDataPayload;
        userEventLogToMessage as baseUserEventLogToMessage;
    }

    static protected $action = 'freeloader updated';
    /**
     * @var array
     */
    protected $attributes;

    public function __construct(int $userId, array $attributes, User $triggeredBy = null)
    {
        parent::__construct($userId);
        if (!$triggeredBy) {
            $triggeredBy = Auth::user();
        }
        $this->triggeredBy = $triggeredBy;

        $attributes = array_only($attributes, [
            'plan_id',
            'access_starts_at',
            'access_expires_at'
        ]);
        $attributes = $this->formatCarbonValue($attributes, 'access_starts_at');
        $attributes = $this->formatCarbonValue($attributes, 'access_expires_at');
        $this->attributes = $attributes;
    }

    protected function formatCarbonValue($array, $key)
    {
        if (array_key_exists($key, $array) && $array[$key]) {
            $array[$key] = $array[$key]->format('Y-m-d H:i:s');
        }
        return $array;
    }

    public function dataPayload(): array
    {
        $data = $this->baseDataPayload();

        return array_merge($data, [
            'updated_attributes' => $this->attributes
        ]);
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        $message = static::baseUserEventLogToMessage($model);
        $helper = app(UserEventMessageHelper::class);
        $message .= ', Updated Attributes: ' . $helper->arrayToString($model->data['updated_attributes']);
        return $message;
    }
}
