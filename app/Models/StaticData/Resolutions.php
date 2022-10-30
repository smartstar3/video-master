<?php

namespace MotionArray\Models\StaticData;

class Resolutions extends StaticDBData
{
    public const RES_1920X1080_HD = '1920x1080-hd';
    public const RES_1920X1080_HD_ID = 1;

    public const RES_2048X1556_2K = '2048x1556-2k';
    public const RES_2048X1556_2K_ID = 2;

    public const RES_4096X2304_4K = '4096x2304-4k';
    public const RES_4096X2304_4K_ID = 3;

    public const RES_3840X2160_4K = '3840x2160-4k';
    public const RES_3840X2160_4K_ID = 4;

    public const RES_1280X720 = '1280x720';
    public const RES_1280X720_ID = 6;

    public const RES_1080X1920_VERTICAL = '1080x1920-vertical';
    public const RES_1080X1920_VERTICAL_ID = 7;

    public const RES_1080X1080_SQUARE = '1080x1080-square';
    public const RES_1080X1080_SQUARE_ID = 8;

    public const RES_7680X4320_8K = '7680x4320-8k';
    public const RES_7680X4320_8K_ID = 13;

    public const RES_4096X2160_4K = '4096x2160-4k';
    public const RES_4096X2160_4K_ID = 14;

    protected $modelClass = \MotionArray\Models\Resolution::class;

    protected $data = [
        [
            'id' => self::RES_1920X1080_HD_ID,
            'slug' => self::RES_1920X1080_HD,
            'name' => '1920x1080 (HD)',
            'order' => 2,
        ],
        [
            'id' => self::RES_2048X1556_2K_ID,
            'slug' => self::RES_2048X1556_2K,
            'name' => '2048x1556 (2K)',
            'order' => 3,
        ],
        [
            'id' => self::RES_4096X2304_4K_ID,
            'slug' => self::RES_4096X2304_4K,
            'name' => '4096x2304 (4K)',
            'order' => 5,
        ],
        [
            'id' => self::RES_3840X2160_4K_ID,
            'slug' => self::RES_3840X2160_4K,
            'name' => '3840x2160 (4K)',
            'order' => 4,
        ],
        [
            'id' => self::RES_1280X720_ID,
            'slug' => self::RES_1280X720,
            'name' => '1280x720',
            'order' => 1,
        ],
        [
            'id' => self::RES_1080X1920_VERTICAL_ID,
            'slug' => self::RES_1080X1920_VERTICAL,
            'name' => '1080x1920 (Vertical)',
            'order' => 6,
        ],
        [
            'id' => self::RES_1080X1080_SQUARE_ID,
            'slug' => self::RES_1080X1080_SQUARE,
            'name' => '1080x1080 (Square)',
            'order' => 7,
        ],
        [
            'id' => self::RES_7680X4320_8K_ID,
            'slug' => self::RES_7680X4320_8K,
            'name' => '7680x4320 (8K)',
            'order' => 0,
        ],
        [
            'id' => self::RES_4096X2160_4K_ID,
            'slug' => self::RES_4096X2160_4K,
            'name' => '4096x2160 (4K)',
            'order' => 0,
        ],
    ];
}
