<?php

namespace MotionArray\Models\StaticData;

class ProductChangeOptions extends StaticDBData
{
    public const PACKAGE_CHANGED = 'package';
    public const PACKAGE_CHANGED_ID = 1;

    public const PREVIEW_CHANGED = 'preview';
    public const PREVIEW_CHANGED_ID = 2;

    public const PRODUCT_NAME_CHANGED = 'product-name';
    public const PRODUCT_NAME_CHANGED_ID = 3;

    public const DESCRIPTION_CHANGED = 'description';
    public const DESCRIPTION_CHANGED_ID = 4;

    public const META_DESCRIPTION_CHANGED = 'meta-description';
    public const META_DESCRIPTION_CHANGED_ID = 5;

    public const AUDIO_PLACEHOLDER_CHANGED = 'audio-placeholder';
    public const AUDIO_PLACEHOLDER_CHANGED_ID = 6;

    public const PLACEHOLDER_URL_CHANGED = 'placeholder-url';
    public const PLACEHOLDER_URL_CHANGED_ID = 7;

    public const FREE_CHANGED = 'free';
    public const FREE_CHANGED_ID = 8;

    public const TRACK_DURATIONS_CHANGED = 'track-durations';
    public const TRACK_DURATIONS_CHANGED_ID = 9;

    public const MUSIC_URL_CHANGED = 'music-url';
    public const MUSIC_URL_CHANGED_ID = 10;

    public const SUB_CATEGORY_CHANGED = 'sub-category';
    public const SUB_CATEGORY_CHANGED_ID = 11;

    public const COMPRESSION_CHANGED = 'compression';
    public const COMPRESSION_CHANGED_ID = 12;

    public const FORMAT_CHANGED = 'format';
    public const FORMAT_CHANGED_ID = 13;

    public const RESOLUTION_CHANGED = 'resolution';
    public const RESOLUTION_CHANGED_ID = 14;

    public const VERSION_CHANGED = 'version';
    public const VERSION_CHANGED_ID = 15;

    public const BPM_CHANGED = 'bpm';
    public const BPM_CHANGED_ID = 16;

    public const FPS_CHANGED = 'fps';
    public const FPS_CHANGED_ID = 17;

    public const SAMPLE_RATE_CHANGED = 'sample-rate';
    public const SAMPLE_RATE_CHANGED_ID = 18;

    public const PLUGIN_CHANGED = 'plugin';
    public const PLUGIN_CHANGED_ID = 19;

    public const TAG_CHANGED = 'tag';
    public const TAG_CHANGED_ID = 20;

    public const EDITORIAL_USE_CHANGED = 'editorial-use';
    public const EDITORIAL_USE_CHANGED_ID = 21;

    protected $modelClass = \MotionArray\Models\ProductChangeOption::class;

    protected $data = [
        [
            'id' => self::PACKAGE_CHANGED_ID,
            'slug' => self::PACKAGE_CHANGED,
            'name' => 'Package',
        ],
        [
            'id' => self::PREVIEW_CHANGED_ID,
            'slug' => self::PREVIEW_CHANGED,
            'name' => 'Preview',
        ],
        [
            'id' => self::PRODUCT_NAME_CHANGED_ID,
            'slug' => self::PRODUCT_NAME_CHANGED,
            'name' => 'Product Name',
        ],
        [
            'id' => self::DESCRIPTION_CHANGED_ID,
            'slug' => self::DESCRIPTION_CHANGED,
            'name' => 'Description',
        ],
        [
            'id' => self::META_DESCRIPTION_CHANGED_ID,
            'slug' => self::META_DESCRIPTION_CHANGED,
            'name' => 'Meta Description',
        ],
        [
            'id' => self::AUDIO_PLACEHOLDER_CHANGED_ID,
            'slug' => self::AUDIO_PLACEHOLDER_CHANGED,
            'name' => 'Audio Placeholder',
        ],
        [
            'id' => self::PLACEHOLDER_URL_CHANGED_ID,
            'slug' => self::PLACEHOLDER_URL_CHANGED,
            'name' => 'Placeholder',
        ],
        [
            'id' => self::FREE_CHANGED_ID,
            'slug' => self::FREE_CHANGED,
            'name' => 'Free CheckBox',
        ],
        [
            'id' => self::TRACK_DURATIONS_CHANGED_ID,
            'slug' => self::TRACK_DURATIONS_CHANGED,
            'name' => 'Duration',
        ],
        [
            'id' => self::MUSIC_URL_CHANGED_ID,
            'slug' => self::MUSIC_URL_CHANGED,
            'name' => 'Music Url',
        ],
        [
            'id' => self::SUB_CATEGORY_CHANGED_ID,
            'slug' => self::SUB_CATEGORY_CHANGED,
            'name' => 'Sub Category',
        ],
        [
            'id' => self::COMPRESSION_CHANGED_ID,
            'slug' => self::COMPRESSION_CHANGED,
            'name' => 'Compression',
        ],
        [
            'id' => self::FORMAT_CHANGED_ID,
            'slug' => self::FORMAT_CHANGED,
            'name' => 'Format',
        ],
        [
            'id' => self::RESOLUTION_CHANGED_ID,
            'slug' => self::RESOLUTION_CHANGED,
            'name' => 'Resolution',
        ],
        [
            'id' => self::VERSION_CHANGED_ID,
            'slug' => self::VERSION_CHANGED,
            'name' => 'Version',
        ],
        [
            'id' => self::BPM_CHANGED_ID,
            'slug' => self::BPM_CHANGED,
            'name' => 'BPM',
        ],
        [
            'id' => self::FPS_CHANGED_ID,
            'slug' => self::FPS_CHANGED,
            'name' => 'FPS',
        ],
        [
            'id' => self::SAMPLE_RATE_CHANGED_ID,
            'slug' => self::SAMPLE_RATE_CHANGED,
            'name' => 'Sample Rate',
        ],
        [
            'id' => self::PLUGIN_CHANGED_ID,
            'slug' => self::PLUGIN_CHANGED,
            'name' => 'Plugin',
        ],
        [
            'id' => self::TAG_CHANGED_ID,
            'slug' => self::TAG_CHANGED,
            'name' => 'Tag',
        ],
        [
            'id' => self::EDITORIAL_USE_CHANGED_ID,
            'slug' => self::EDITORIAL_USE_CHANGED,
            'name' => 'Editorial Use',
        ],
    ];
}
