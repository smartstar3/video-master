<?php

namespace MotionArray\Events\UserEvent;

use MotionArray\Events\UserEvent;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\UserEventLog;

class UserChangedPlan extends UserEvent
{
    /**
     * @var int
     */
    protected $newPlanId;

    public function __construct(int $userId, int $newPlanId)
    {
        parent::__construct($userId);

        $this->newPlanId = $newPlanId;
    }

    public function dataPayload(): array
    {
        $plan = (new Plans)->findOrFail($this->newPlanId);

        return [
            'new_plan' => array_only($plan, [
                'id',
                'billing_id',
                'name',
                'cycle',
            ])
        ];
    }

    public static function userEventLogToMessage(UserEventLog $model): string
    {
        /** @var UserEventMessageHelper $helper */
        $helper = app(UserEventMessageHelper::class);
        $userDescription = $helper->userDescription($model->user);
        $plan = $model->data['new_plan'];

        return "User: {$userDescription} changed plan to: '{$plan['name']}' (billing_id: '{$plan['billing_id']}', name: '{$plan['name']}', cycle: '{$plan['cycle']}')";
    }
}
