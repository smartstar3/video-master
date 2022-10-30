<?php

namespace MotionArray\Events\UserEvent\Subscription;

use MotionArray\Events\UserEvent;
use MotionArray\Events\UserEvent\Concerns\HandlesUserEventActions;

class SubscriptionResumedAfterPaymentHold extends UserEvent
{
    use HandlesUserEventActions;

    protected static $action = 'subscription resumed after payment on hold';
    /**
     * @var int
     */
    private $subscriptionId;
    /**
     * @var int
     */
    private $subscriptionPaymentId;

    public function __construct(int $userId, int $subscriptionId, int $subscriptionPaymentId)
    {
        parent::__construct($userId);

        $this->subscriptionId = $subscriptionId;
        $this->subscriptionPaymentId = $subscriptionPaymentId;
    }

    public function dataPayload(): array
    {
        return [
            'subscription_id' => $this->subscriptionId,
            'subscription_payment_id' => $this->subscriptionPaymentId,
        ];
    }
}
