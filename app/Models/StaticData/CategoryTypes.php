<?php

namespace MotionArray\Models\StaticData;

class CategoryTypes extends StaticDBData
{
    public const TEMPLATES = 'Templates';
    public const TEMPLATES_ID = 1;

    public const PRESETS = 'Presets';
    public const PRESETS_ID = 2;

    public const AUDIO = 'Audio';
    public const AUDIO_ID = 3;

    public const VIDEO = 'Video';
    public const VIDEO_ID = 4;

    public const IMAGES = 'Images';
    public const IMAGES_ID = 5;

    protected $modelClass = \MotionArray\Models\CategoryType::class;

    protected $data = [
        [
            'id' => self::TEMPLATES_ID,
            'name' => self::TEMPLATES,
            'order' => 10,
        ],
        [
            'id' => self::PRESETS_ID,
            'name' => self::PRESETS,
            'order' => 20,
        ],
        [
            'id' => self::AUDIO_ID,
            'name' => self::AUDIO,
            'order' => 30,
        ],
        [
            'id' => self::VIDEO_ID,
            'name' => self::VIDEO,
            'order' => 40,
        ],
        [
            'id' => self::IMAGES_ID,
            'name' => self::IMAGES,
            'order' => 50,
        ],
    ];
}
