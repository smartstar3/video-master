<?php

namespace MotionArray\Models\StaticData;

class AccessServiceCategories extends StaticDBData
{
    public const REVIEW_PRODUCTS = 'Review Products';
    public const REVIEW_PRODUCTS_ID = 1;

    public const APPROVED_PRODUCTS = 'Approved Products';
    public const APPROVED_PRODUCTS_ID = 2;

    public const USERS = 'Users';
    public const USERS_ID = 3;

    protected $modelClass = \MotionArray\Models\AccessServiceCategory::class;

    protected $data = [
        [
            'id' => self::REVIEW_PRODUCTS_ID,
            'name' => self::REVIEW_PRODUCTS,
        ],
        [
            'id' => self::APPROVED_PRODUCTS_ID,
            'name' => self::APPROVED_PRODUCTS,
        ],
        [
            'id' => self::USERS_ID,
            'name' => self::USERS,
        ],
    ];
}
