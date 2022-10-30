<?php

namespace MotionArray\Services\Subscription;

use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;

class StripeStatusParser
{
    public function parseSubscriptionStatus(string $status): int
    {
        $status = strtolower($status);

        $map = [
            'active' => SubscriptionStatuses::STATUS_ACTIVE_ID,
            'canceled' => SubscriptionStatuses::STATUS_CANCELED_ID,
        ];

        return $map[$status];
    }

    public function parsePaymentStatus(string $status): int
    {
        $status = strtolower($status);

        $map = [
            'succeeded' => SubscriptionPaymentStatuses::STATUS_SUCCESS_ID,
            'failed' => SubscriptionPaymentStatuses::STATUS_FAILED_ID,
        ];

        return $map[$status];
    }
}
