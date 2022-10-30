<?php

namespace MotionArray\Models\StaticData;

class FramesPerSeconds extends StaticDBData
{
    public const FPS_29_97_FPS = '29-97-fps';
    public const FPS_29_97_FPS_ID = 1;

    public const FPS_23_97_FPS = '23-97-fps';
    public const FPS_23_97_FPS_ID = 2;

    public const FPS_24_FPS = '24-fps';
    public const FPS_24_FPS_ID = 3;

    public const FPS_30_FPS = '30-fps';
    public const FPS_30_FPS_ID = 4;

    public const FPS_25_FPS = '25-fps';
    public const FPS_25_FPS_ID = 5;

    public const FPS_60_FPS = '60-fps';
    public const FPS_60_FPS_ID = 6;

    public const FPS_59_94_FPS = '59-94-fps';
    public const FPS_59_94_FPS_ID = 7;

    public const FPS_120_FPS = '120-fps';
    public const FPS_120_FPS_ID = 8;

    protected $modelClass = \MotionArray\Models\Fps::class;

    protected $data = [
        [
            'id' => self::FPS_29_97_FPS_ID,
            'slug' => self::FPS_29_97_FPS,
            'name' => '29.97 FPS',
        ],
        [
            'id' => self::FPS_23_97_FPS_ID,
            'slug' => self::FPS_23_97_FPS,
            'name' => '23.97 FPS',
        ],
        [
            'id' => self::FPS_24_FPS_ID,
            'slug' => self::FPS_24_FPS,
            'name' => '24 FPS',
        ],
        [
            'id' => self::FPS_30_FPS_ID,
            'slug' => self::FPS_30_FPS,
            'name' => '30 FPS',
        ],
        [
            'id' => self::FPS_25_FPS_ID,
            'slug' => self::FPS_25_FPS,
            'name' => '25 FPS',
        ],
        [
            'id' => self::FPS_60_FPS_ID,
            'slug' => self::FPS_60_FPS,
            'name' => '60 FPS',
        ],
        [
            'id' => self::FPS_59_94_FPS_ID,
            'slug' => self::FPS_59_94_FPS,
            'name' => '59.94 FPS',
        ],
        [
            'id' => self::FPS_120_FPS_ID,
            'slug' => self::FPS_120_FPS,
            'name' => '120 FPS',
        ],
    ];
}
