<?php

namespace MotionArray\Models\StaticData;

class Collections extends StaticDBData
{
    public const ADOBE_PREMIERE_PRO_TEMPLATES = '5c7838299738a';
    public const ADOBE_PREMIERE_PRO_TRANSITIONS = '5c7868fe61178';
    public const ADOBE_AFTER_EFFECTS_TEMPLATES = '5c786bd86469a';
    public const STOCK_FOOTAGE = '5c81a489c7123';
    public const AE_INTRO = '5c93cb858042b';
    public const AE_TRANSITIONS = '5c93bfd996b60';
    public const AE_LOGO = '5c93bbc572eb7';
    public const AE_TITLES = '5c93cef214f4d';
    public const AE_SLIDESHOW = '5c93d138b456e';
    public const AE_LOWER_THIRD = '5c93d9627dae5';

    protected $modelClass = \MotionArray\Models\Collection::class;

    protected $data = [
        [
            'slug' => self::ADOBE_PREMIERE_PRO_TEMPLATES,
            'title' => 'Adobe Premiere Pro Templates',
            'deleted_at' => null,
        ],
        [
            'slug' => self::ADOBE_PREMIERE_PRO_TRANSITIONS,
            'title' => 'Adobe Premiere Pro Transitions',
            'deleted_at' => null,
        ],
        [
            'slug' => self::ADOBE_AFTER_EFFECTS_TEMPLATES,
            'title' => 'Adobe After Effects Templates',
            'deleted_at' => null,
        ],
        [
            'slug' => self::STOCK_FOOTAGE,
            'title' => 'Stock Footage',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_INTRO,
            'title' => 'AE Intro',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_TRANSITIONS,
            'title' => 'AE Transitions',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_LOGO,
            'title' => 'AE Logo',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_TITLES,
            'title' => 'AE Titles',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_SLIDESHOW,
            'title' => 'AE Slideshow',
            'deleted_at' => null,
        ],
        [
            'slug' => self::AE_LOWER_THIRD,
            'title' => 'AE Lower_third',
            'deleted_at' => null,
        ],
    ];

    protected function prepareData(): array
    {
        return collect($this->data)
            ->keyBy('slug')
            ->toArray();
    }
}
