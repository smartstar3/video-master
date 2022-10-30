<?php

namespace MotionArray\Models\StaticData;

class ProductStatuses extends StaticDBData
{
    public const PUBLISHED = 'Published';
    public const PUBLISHED_ID = 1;

    public const UNPUBLISHED = 'Unpublished';
    public const UNPUBLISHED_ID = 2;

    public const PROCESSING = 'Processing';
    public const PROCESSING_ID = 3;

    protected $modelClass = \MotionArray\Models\ProductStatus::class;

    protected $data = [
        [
            'id' => self::PUBLISHED_ID,
            'status' => self::PUBLISHED,
        ],
        [
            'id' => self::UNPUBLISHED_ID,
            'status' => self::UNPUBLISHED,
        ],
        [
            'id' => self::PROCESSING_ID,
            'status' => self::PROCESSING,
        ],
    ];
}
