<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\SubscriptionStatus;

class SubscriptionStatuses extends StaticDBData
{
    protected $modelClass = SubscriptionStatus::class;

    public const STATUS_ACTIVE_ID = 1;
    public const STATUS_ACTIVE = 'active';

    /**
     * From State: STATUS_ACTIVE
     * - If users cancel their subscription from MotionArray or Paypal, we mark them as CANCELED at the end of subscription period, not at the same day they've canceled.
     *   I.e. User subscribed 1st October. User canceled subscription from Paypal/Motionarray at 5th October. User can continue to use subscription until 31 October.
     *
     * From State: STATUS_SUSPENDED
     * - Paypal tried to collect money for subscription, but it failed for 5 days. Then Paypal changes subscription status as CANCELED.
     */
    public const STATUS_CANCELED_ID = 2;
    public const STATUS_CANCELED = 'canceled';

    /*
     * From State: STATUS_ACTIVE
     * - Paypal tries to collect money for subscription. But it failed. Then we mark subscription status as SUSPENDED.
     *   If next try succeeds, we mark subscription as STATUS_ACTIVE. If next tries fails, we mark subscription as STATUS_CANCELED.
     *   Payments will be retried up to 3 times, 5 days apart
     *
     * From State: STATUS_CANCELED
     * - This shouldn't happen.
     */
    public const STATUS_SUSPENDED_ID = 3;
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Sometimes Paypal processes payment but takes it under investigation, it stays as Pending.
     *
     * User won't be able to use their subscription if status is pending.
     */
    public const STATUS_PENDING_ID = 4;
    public const STATUS_PENDING = 'pending';

    protected $data = [
        [
            'id' => self::STATUS_ACTIVE_ID,
            'slug' => self::STATUS_ACTIVE,
        ],
        [
            'id' => self::STATUS_CANCELED_ID,
            'slug' => self::STATUS_CANCELED,
        ],
        [
            'id' => self::STATUS_SUSPENDED_ID,
            'slug' => self::STATUS_SUSPENDED,
        ],
        [
            'id' => self::STATUS_PENDING_ID,
            'slug' => self::STATUS_PENDING,
        ],
    ];
}
