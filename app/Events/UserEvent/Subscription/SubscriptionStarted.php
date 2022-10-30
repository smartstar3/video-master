<?php

namespace MotionArray\Events\UserEvent\Subscription;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActions;

class SubscriptionStarted extends UserEvent
{
    use HandlesUserEventActions;

    protected static $action = 'subscription started';

    /**
     * @var int
     */
    private $subscriptionId;

    /**
     * @var int
     */
    private $subscriptionPaymentId;

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

    public function __construct(
        int $userId,
        int $existingPlanId,
        int $newPlanId,
        int $paymentGatewayId,
        ?int $subscriptionId,
        ?int $subscriptionPaymentId
    ) {
        parent::__construct($userId);

        $this->subscriptionId = $subscriptionId;
        $this->subscriptionPaymentId = $subscriptionPaymentId;
        $this->existingPlanId = $existingPlanId;
        $this->newPlanId = $newPlanId;
        $this->paymentGatewayId = $paymentGatewayId;
    }

    public function dataPayload(): array
    {
        return [
            'existing_plan_id' => $this->existingPlanId,
            'new_plan_id' => $this->newPlanId,
            'subscription_id' => $this->subscriptionId,
            'subscription_payment_id' => $this->subscriptionPaymentId,
        ];
    }
}
