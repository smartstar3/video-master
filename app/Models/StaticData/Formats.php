<?php

namespace MotionArray\Models\StaticData;

class Formats extends StaticDBData
{
    public const WAV = '.wav';
    public const WAV_ID = 1;

    public const MP3 = '.mp3';
    public const MP3_ID = 2;

    public const AIFF = '.aiff';
    public const AIFF_ID = 3;

    protected $modelClass = \MotionArray\Models\Format::class;

    protected $data = [
        [
            'id' => self::WAV_ID,
            'name' => self::WAV,
        ],
        [
            'id' => self::MP3_ID,
            'name' => self::MP3,
        ],
        [
            'id' => self::AIFF_ID,
            'name' => self::AIFF,
        ],
    ];
}
