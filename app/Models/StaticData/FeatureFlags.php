<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\FeatureFlag;

class FeatureFlags extends StaticDBData
{
    public const STOCK_PHOTOS = 'STOCK_PHOTOS';
    public const STOCK_PHOTOS_ID = 1;

    protected $modelClass = FeatureFlag::class;

    protected $data = [
        [
            'id' => self::STOCK_PHOTOS_ID,
            'slug' => self::STOCK_PHOTOS,
        ],
    ];
}
