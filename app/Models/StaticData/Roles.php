<?php

namespace MotionArray\Models\StaticData;

class Roles extends StaticDBData
{
    public const SUPER_ADMIN = 'Super Admin';
    public const SUPER_ADMIN_ID = 1;

    public const ADMIN = 'Admin';
    public const ADMIN_ID = 2;

    public const SELLER = 'Seller';
    public const SELLER_ID = 3;

    public const CUSTOMER = 'Customer';
    public const CUSTOMER_ID = 4;

    public const LEGACY_CUSTOMER = 'Legacy Customer';
    public const LEGACY_CUSTOMER_ID = 5;

    public const FREELOADER = 'Freeloaders';
    public const FREELOADER_ID = 6;

    protected $modelClass = \MotionArray\Models\Role::class;

    protected $data = [
        [
            'id' => self::SUPER_ADMIN_ID,
            'name' => self::SUPER_ADMIN,
        ],
        [
            'id' => self::ADMIN_ID,
            'name' => self::ADMIN,
        ],
        [
            'id' => self::SELLER_ID,
            'name' => self::SELLER,
        ],
        [
            'id' => self::CUSTOMER_ID,
            'name' => self::CUSTOMER,
        ],
        [
            'id' => self::LEGACY_CUSTOMER_ID,
            'name' => self::LEGACY_CUSTOMER,
        ],
        [
            'id' => self::FREELOADER_ID,
            'name' => self::FREELOADER,
        ],
    ];
}
