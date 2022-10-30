<?php

namespace MotionArray\Services\Subscription;

use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\SubscriptionStatus;

class PaypalStatusParser
{
    public function parseSubscriptionStatus(string $status): int
    {
        $status = strtolower($status);

        $map = [
            'active' => SubscriptionStatuses::STATUS_ACTIVE_ID,
            'canceled' => SubscriptionStatuses::STATUS_CANCELED_ID,
            'suspended' => SubscriptionStatuses::STATUS_SUSPENDED_ID,
            'pending' => SubscriptionStatuses::STATUS_PENDING_ID,
        ];

        return $map[$status];
    }

    public function parsePaymentStatus(string $status): int
    {
        $status = strtolower($status);

        $map = [
            'success' => SubscriptionPaymentStatuses::STATUS_SUCCESS_ID,
            'completed' => SubscriptionPaymentStatuses::STATUS_SUCCESS_ID,
            'failed' => SubscriptionPaymentStatuses::STATUS_FAILED_ID,
            'pending' => SubscriptionPaymentStatuses::STATUS_PENDING_ID,
        ];

        return $map[$status];
    }
}
