<?php

namespace MotionArray\Models\StaticData;

class CategoryGroups extends StaticDBData
{
    public const PREMIERE_PRO = 'premiere-pro';
    public const PREMIERE_PRO_ID = 1;

    public const AFTER_EFFECTS = 'after-effects';
    public const AFTER_EFFECTS_ID = 2;

    public const STOCK_VIDEO = 'stock-video';
    public const STOCK_VIDEO_ID = 3;

    public const STOCK_AUDIO = 'stock-audio';
    public const STOCK_AUDIO_ID = 4;

    public const DAVINCI_RESOLVE = 'davinci-resolve';
    public const DAVINCI_RESOLVE_ID = 5;

    public const PREMIERE_RUSH = 'premiere-rush';
    public const PREMIERE_RUSH_ID = 6;

    public const FINAL_CUT_PRO = 'final-cut-pro';
    public const FINAL_CUT_PRO_ID = 7;

    public const IMAGES = 'images';
    public const IMAGES_ID = 8;

    protected $modelClass = \MotionArray\Models\CategoryGroup::class;

    protected $data = [
        [
            'id' => self::PREMIERE_PRO_ID,
            'slug' => self::PREMIERE_PRO,
            'name' => 'Adobe Premiere Pro',
            'shortname' => 'PP',
            'order' => 10,
        ],
        [
            'id' => self::PREMIERE_RUSH_ID,
            'slug' => self::PREMIERE_RUSH,
            'name' => 'Adobe Premiere Rush',
            'shortname' => 'PR',
            'order' => 20,
        ],
        [
            'id' => self::AFTER_EFFECTS_ID,
            'slug' => self::AFTER_EFFECTS,
            'name' => 'Adobe After Effects',
            'shortname' => 'AE',
            'order' => 30,
        ],
        [
            'id' => self::FINAL_CUT_PRO_ID,
            'slug' => self::FINAL_CUT_PRO,
            'name' => 'Final Cut Pro',
            'shortname' => 'FCP',
            'order' => 40,
        ],
        [
            'id' => self::DAVINCI_RESOLVE_ID,
            'slug' => self::DAVINCI_RESOLVE,
            'name' => 'DaVinci Resolve',
            'shortname' => 'Resolve',
            'order' => 50,
        ],
        [
            'id' => self::STOCK_VIDEO_ID,
            'slug' => self::STOCK_VIDEO,
            'name' => 'Stock Video',
            'shortname' => 'Video',
            'order' => 60,
        ],
        [
            'id' => self::STOCK_AUDIO_ID,
            'slug' => self::STOCK_AUDIO,
            'name' => 'Stock Audio',
            'shortname' => 'Audio',
            'order' => 70,
        ],
        [
            'id' => self::IMAGES_ID,
            'slug' => self::IMAGES,
            'name' => 'Images',
            'shortname' => 'images',
            'order' => 80,
        ],
    ];
}
