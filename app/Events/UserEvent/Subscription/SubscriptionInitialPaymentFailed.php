<?php

namespace MotionArray\Events\UserEvent\Subscription;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActions;

class SubscriptionInitialPaymentFailed extends UserEvent
{
    use HandlesUserEventActions;

    protected static $action = 'subscription initial payment failed';

    /**
     * @var int
     */
    private $existingPlanId;

    /**
     * @var int
     */
    private $newPlanId;

    /**
     * @var int
     */
    private $paymentGatewayId;
    private $paymentGatewayPaymentId;

    public function __construct(
        int $userId,
        int $existingPlanId,
        int $newPlanId,
        int $paymentGatewayId
    ) {
        parent::__construct($userId);

        $this->existingPlanId = $existingPlanId;
        $this->newPlanId = $newPlanId;
        $this->paymentGatewayId = $paymentGatewayId;
    }

    public function dataPayload(): array
    {
        return [
            'existing_plan_id' => $this->existingPlanId,
            'new_plan_id' => $this->newPlanId,
            'payment_gateway_id' => $this->paymentGatewayId,
        ];
    }
}
