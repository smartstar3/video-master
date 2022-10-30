<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\Application;

class Applications extends StaticDBData
{
    public const AFTER_EFFECTS = 'after-effects';
    public const AFTER_EFFECTS_ID = 1;

    public const PREMIERE_PRO = 'premiere-pro';
    public const PREMIERE_PRO_ID = 2;

    public const PREMIERE_RUSH = 'premiere-rush';
    public const PREMIERE_RUSH_ID = 3;

    public const DAVINCI_RESOLVE = 'davinci-resolve';
    public const DAVINCI_RESOLVE_ID = 4;

    public const DAVINCI_RESOLVE_STUDIO = 'davinci-resolve-studio';
    public const DAVINCI_RESOLVE_STUDIO_ID = 5;

    public const FINAL_CUT_PRO = 'final-cut-pro';
    public const FINAL_CUT_PRO_ID = 6;

    protected $modelClass = Application::class;

    protected $data = [
        [
            'id' => self::AFTER_EFFECTS_ID,
            'slug' => self::AFTER_EFFECTS,
            'name' => 'Adobe After Effects',
        ],
        [
            'id' => self::PREMIERE_PRO_ID,
            'slug' => self::PREMIERE_PRO,
            'name' => 'Adobe Premiere Pro',
        ],
        [
            'id' => self::PREMIERE_RUSH_ID,
            'slug' => self::PREMIERE_RUSH,
            'name' => 'Adobe Premiere Rush',
        ],
        [
            'id' => self::DAVINCI_RESOLVE_ID,
            'slug' => self::DAVINCI_RESOLVE,
            'name' => 'Davinci Resolve',
        ],
        [
            'id' => self::DAVINCI_RESOLVE_STUDIO_ID,
            'slug' => self::DAVINCI_RESOLVE_STUDIO,
            'name' => 'Davinci Resolve Studio',
        ],
        [
            'id' => self::FINAL_CUT_PRO_ID,
            'slug' => self::FINAL_CUT_PRO,
            'name' => 'Final Cut Pro',
        ],
    ];
}
