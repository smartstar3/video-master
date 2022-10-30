<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\SubscriptionPaymentStatus;

class SubscriptionPaymentStatuses extends StaticDBData
{
    protected $modelClass = SubscriptionPaymentStatus::class;

    public const STATUS_SUCCESS_ID = 1;
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED_ID = 2;
    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING_ID = 3;
    public const STATUS_PENDING = 'pending';

    public const STATUS_REFUNDED_ID = 4;
    public const STATUS_REFUNDED = 'refunded';

    protected $data = [
        [
            'id' => self::STATUS_SUCCESS_ID,
            'slug' => self::STATUS_SUCCESS,
        ],
        [
            'id' => self::STATUS_FAILED_ID,
            'slug' => self::STATUS_FAILED,
        ],
        [
            'id' => self::STATUS_PENDING_ID,
            'slug' => self::STATUS_PENDING,
        ],
        [
            'id' => self::STATUS_REFUNDED_ID,
            'slug' => self::STATUS_REFUNDED,
        ],
    ];
}
