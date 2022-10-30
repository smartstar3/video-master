<?php

namespace MotionArray\Models\StaticData;

class Plans extends StaticDBData
{
    public const MONTHLY_SMALL = 'monthly_small';
    public const MONTHLY_SMALL_ID = 1;

    public const MONTHLY_STANDARD = 'monthly_standard';
    public const MONTHLY_STANDARD_ID = 2;

    public const MONTHLY_PRO = 'monthly_pro';
    public const MONTHLY_PRO_ID = 3;

    public const MONTHLY_UNLIMITED = 'monthly_unlimited';
    public const MONTHLY_UNLIMITED_ID = 4;

    public const FREE = 'free';
    public const FREE_ID = 5;

    public const YEARLY_SMALL = 'yearly_small';
    public const YEARLY_SMALL_ID = 7;

    public const YEARLY_STANDARD = 'yearly_standard';
    public const YEARLY_STANDARD_ID = 8;

    public const YEARLY_PRO = 'yearly_pro';
    public const YEARLY_PRO_ID = 9;

    public const YEARLY_UNLIMITED = 'yearly_unlimited';
    public const YEARLY_UNLIMITED_ID = 10;

    public const MONTHLY_LEGACY = 'monthly_legacy';
    public const MONTHLY_LEGACY_ID = 11;

    public const YEARLY_LEGACY = 'yearly_legacy';
    public const YEARLY_LEGACY_ID = 12;

    public const MONTHLY_6_LEGACY = '6monthly_legacy';
    public const MONTHLY_6_LEGACY_ID = 13;

    public const MONTHLY_LITE = 'monthly_lite';
    public const MONTHLY_LITE_ID = 14;

    public const MONTHLY_PLUS = 'monthly_plus';
    public const MONTHLY_PLUS_ID = 15;

    public const MONTHLY_PRO15 = 'monthly_pro15';
    public const MONTHLY_PRO15_ID = 16;

    public const YEARLY_LITE = 'yearly_lite';
    public const YEARLY_LITE_ID = 17;

    public const YEARLY_PLUS = 'yearly_plus';
    public const YEARLY_PLUS_ID = 18;

    public const YEARLY_PRO15 = 'yearly_pro15';
    public const YEARLY_PRO15_ID = 19;

    public const MONTHLY_LITE_2017 = 'monthly_lite_2017';
    public const MONTHLY_LITE_2017_ID = 20;

    public const MONTHLY_PLUS_2017 = 'monthly_plus_2017';
    public const MONTHLY_PLUS_2017_ID = 21;

    public const MONTHLY_PRO_2017 = 'monthly_pro_2017';
    public const MONTHLY_PRO_2017_ID = 22;

    public const YEARLY_LITE_2017 = 'yearly_lite_2017';
    public const YEARLY_LITE_2017_ID = 23;

    public const YEARLY_PLUS_2017 = 'yearly_plus_2017';
    public const YEARLY_PLUS_2017_ID = 24;

    public const YEARLY_PRO_2017 = 'yearly_pro_2017';
    public const YEARLY_PRO_2017_ID = 25;

    public const MONTHLY_UNLIMITED_2018 = 'monthly_unlimited_2018';
    public const MONTHLY_UNLIMITED_2018_ID = 26;

    public const YEARLY_UNLIMITED_2018 = 'yearly_unlimited_2018';
    public const YEARLY_UNLIMITED_2018_ID = 27;

    public const CYCLE_YEARLY = 'yearly';
    public const CYCLE_MONTHLY = 'monthly';
    public const CYCLE_6MONTHLY = '6monthly';

    protected $modelClass = \MotionArray\Models\Plan::class;

    protected $data = [
        [
            'id' => self::MONTHLY_SMALL_ID,
            'billing_id' => self::MONTHLY_SMALL,
            'name' => 'Small',
            'display_name' => 'Starting Small',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '2900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 10,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_STANDARD_ID,
            'billing_id' => self::MONTHLY_STANDARD,
            'name' => 'Standard',
            'display_name' => 'Standard',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '4900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 25,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_PRO_ID,
            'billing_id' => self::MONTHLY_PRO,
            'name' => 'Professional',
            'display_name' => 'Getting Serious',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '6900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 45,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_UNLIMITED_ID,
            'billing_id' => self::MONTHLY_UNLIMITED,
            'name' => 'Unlimited',
            'display_name' => 'The Whole Hog',
            'class' => 'unlimited',
            'symbol' => '',
            'price' => '9900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => -1,
            'disk_space' => 1,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::FREE_ID,
            'billing_id' => self::FREE,
            'name' => 'Free',
            'display_name' => '',
            'class' => 'free',
            'symbol' => 'payment-plan__symbol--small.png',
            'price' => 0,
            'cycle' => '',
            'credits' => 0,
            'disk_space' => 1,
            'active' => 1,
            'order' => 1,
        ],
        [
            'id' => self::YEARLY_SMALL_ID,
            'billing_id' => self::YEARLY_SMALL,
            'name' => 'Small',
            'display_name' => 'Starting Small',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '22620',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 10,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_STANDARD_ID,
            'billing_id' => self::YEARLY_STANDARD,
            'name' => 'Standard',
            'display_name' => 'Standard',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '38220',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 25,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_PRO_ID,
            'billing_id' => self::YEARLY_PRO,
            'name' => 'Professional',
            'display_name' => 'Getting Serious',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '52820',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 45,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_UNLIMITED_ID,
            'billing_id' => self::YEARLY_UNLIMITED,
            'name' => 'Unlimited',
            'display_name' => 'The Whole Hog',
            'class' => 'unlimited',
            'symbol' => '',
            'price' => '77220',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => -1,
            'disk_space' => 1,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_LEGACY_ID,
            'billing_id' => self::MONTHLY_LEGACY,
            'name' => 'Legacy',
            'display_name' => '',
            'class' => '',
            'symbol' => '',
            'price' => '3400',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => -1,
            'disk_space' => 1,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_LEGACY_ID,
            'billing_id' => self::YEARLY_LEGACY,
            'name' => 'Legacy',
            'display_name' => '',
            'class' => '',
            'symbol' => '',
            'price' => '15900',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => -1,
            'disk_space' => 1,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_6_LEGACY_ID,
            'billing_id' => self::MONTHLY_6_LEGACY,
            'name' => 'Legacy',
            'display_name' => '',
            'class' => '',
            'symbol' => '',
            'price' => '9900',
            'cycle' => self::CYCLE_6MONTHLY,
            'credits' => -1,
            'disk_space' => 1,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_LITE_ID,
            'billing_id' => self::MONTHLY_LITE,
            'name' => 'Lite',
            'display_name' => 'Lite',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '1900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 4,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_PLUS_ID,
            'billing_id' => self::MONTHLY_PLUS,
            'name' => 'Plus',
            'display_name' => 'Plus',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '2900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 10,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_PRO15_ID,
            'billing_id' => self::MONTHLY_PRO15,
            'name' => 'Pro',
            'display_name' => 'Pro',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '4900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 20,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_LITE_ID,
            'billing_id' => self::YEARLY_LITE,
            'name' => 'Lite',
            'display_name' => 'Lite',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '14820',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 4,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_PLUS_ID,
            'billing_id' => self::YEARLY_PLUS,
            'name' => 'Plus',
            'display_name' => 'Plus',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '22620',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 10,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_PRO15_ID,
            'billing_id' => self::YEARLY_PRO15,
            'name' => 'Pro',
            'display_name' => 'Pro',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '38220',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 20,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_LITE_2017_ID,
            'billing_id' => self::MONTHLY_LITE_2017,
            'name' => 'Lite',
            'display_name' => 'Lite',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '2400',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 4,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_PLUS_2017_ID,
            'billing_id' => self::MONTHLY_PLUS_2017,
            'name' => 'Plus',
            'display_name' => 'Plus',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '3400',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 10,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_PRO_2017_ID,
            'billing_id' => self::MONTHLY_PRO_2017,
            'name' => 'Pro',
            'display_name' => 'Pro',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '5400',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 20,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_LITE_2017_ID,
            'billing_id' => self::YEARLY_LITE_2017,
            'name' => 'Lite',
            'display_name' => 'Lite',
            'class' => 'small',
            'symbol' => 'payment-plan__symbol--standard.png',
            'price' => '21600',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 4,
            'disk_space' => 10,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_PLUS_2017_ID,
            'billing_id' => self::YEARLY_PLUS_2017,
            'name' => 'Plus',
            'display_name' => 'Plus',
            'class' => 'standard',
            'symbol' => 'payment-plan__symbol--pro.png',
            'price' => '30600',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 10,
            'disk_space' => 25,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::YEARLY_PRO_2017_ID,
            'billing_id' => self::YEARLY_PRO_2017,
            'name' => 'Pro',
            'display_name' => 'Pro',
            'class' => 'pro',
            'symbol' => 'payment-plan__symbol--unlimited.png',
            'price' => '48600',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 20,
            'disk_space' => 50,
            'active' => 0,
            'order' => 0,
        ],
        [
            'id' => self::MONTHLY_UNLIMITED_2018_ID,
            'billing_id' => self::MONTHLY_UNLIMITED_2018,
            'name' => 'Monthly',
            'display_name' => 'Pro',
            'class' => 'pro',
            'symbol' => '',
            'price' => '2900',
            'cycle' => self::CYCLE_MONTHLY,
            'credits' => 0,
            'disk_space' => 10,
            'active' => 1,
            'order' => 2,
        ],
        [
            'id' => self::YEARLY_UNLIMITED_2018_ID,
            'billing_id' => self::YEARLY_UNLIMITED_2018,
            'name' => 'Annual',
            'display_name' => 'Pro 365',
            'class' => 'pro',
            'symbol' => '',
            'price' => '19200',
            'cycle' => self::CYCLE_YEARLY,
            'credits' => 0,
            'disk_space' => 20,
            'active' => 1,
            'order' => 3,
        ],
    ];
}
