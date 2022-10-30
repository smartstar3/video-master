<?php

namespace MotionArray\Models\StaticData;

class ProductImageTypes extends StaticDBData
{

    public const SMALL_ID = 2;
    public const SMALL = 'small';

    public const LARGE_ID = 3;
    public const LARGE = 'large';

    protected $modelClass = \MotionArray\Models\ProductImageMetaOrientation::class;

    protected $data = [
        [
            'id' => self::SMALL_ID,
            'slug' => self::SMALL,
            'name' => 'Small',
            'max_width' => 750,
            'max_height' => 422,
            'has_watermark' => false,
            'preview_file_label' => 'placeholder high image-small',
        ],
        [
            'id' => self::LARGE_ID,
            'slug' => self::LARGE,
            'name' => 'Large',
            'max_width' => 1920,
            'max_height' => 1080,
            'has_watermark' => true,
            'preview_file_label' => 'image-large',
        ],
    ];
}
