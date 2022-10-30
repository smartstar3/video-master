<?php

namespace MotionArray\Models\StaticData;

class SampleRates extends StaticDBData
{
    public const RATE_44_1_KHZ = '44.1kHz';
    public const RATE_44_1_KHZ_ID = 1;

    public const RATE_48_KHZ = '48 kHz';
    public const RATE_48_KHZ_ID = 2;

    protected $modelClass = \MotionArray\Models\SampleRate::class;

    protected $data = [
        [
            'id' => self::RATE_44_1_KHZ_ID,
            'name' => self::RATE_44_1_KHZ,
        ],
        [
            'id' => self::RATE_48_KHZ_ID,
            'name' => self::RATE_48_KHZ,
        ],
    ];
}
