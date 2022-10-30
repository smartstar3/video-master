<?php

namespace MotionArray\Models\StaticData;

class PaymentGateways extends StaticDBData
{
    public const STRIPE = 'Stripe';
    public const STRIPE_ID = 1;

    public const PAYPAL = 'Paypal';
    public const PAYPAL_ID = 2;

    protected $modelClass = \MotionArray\Models\PaymentGateway::class;

    protected $data = [
        [
            'id' => self::STRIPE_ID,
            'name' => self::STRIPE,
            'is_enabled' => true,
        ],
        [
            'id' => self::PAYPAL_ID,
            'name' => self::PAYPAL,
            'is_enabled' => true,
        ],
    ];
}
