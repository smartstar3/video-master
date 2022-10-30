<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\Category;

class Categories extends StaticDBData
{
    public const AFTER_EFFECTS_TEMPLATES = 'after-effects-templates';
    public const AFTER_EFFECTS_TEMPLATES_ID = 1;

    public const STOCK_VIDEO = 'stock-video';
    public const STOCK_VIDEO_ID = 2;

    public const STOCK_MOTION_GRAPHICS = 'stock-motion-graphics';
    public const STOCK_MOTION_GRAPHICS_ID = 3;

    public const STOCK_MUSIC = 'royalty-free-music';
    public const STOCK_MUSIC_ID = 4;

    public const PREMIERE_PRO_TEMPLATES = 'premiere-pro-templates';
    public const PREMIERE_PRO_TEMPLATES_ID = 5;

    public const MOTION_GRAPHICS_TEMPLATES = 'motion-graphics-templates';
    public const MOTION_GRAPHICS_TEMPLATES_ID = 6;

    public const SOUND_EFFECTS = 'sound-effects';
    public const SOUND_EFFECTS_ID = 7;

    public const PREMIERE_PRO_PRESETS = 'premiere-pro-presets';
    public const PREMIERE_PRO_PRESETS_ID = 8;

    public const AFTER_EFFECTS_PRESETS = 'after-effects-presets';
    public const AFTER_EFFECTS_PRESETS_ID = 9;

    public const DAVINCI_RESOLVE_TEMPLATES = 'davinci-resolve-templates';
    public const DAVINCI_RESOLVE_TEMPLATES_ID = 10;

    public const PREMIERE_RUSH_TEMPLATES = 'premiere-rush-templates';
    public const PREMIERE_RUSH_TEMPLATES_ID = 11;

    public const DAVINCI_RESOLVE_MACROS = 'davinci-resolve-macros';
    public const DAVINCI_RESOLVE_MACROS_ID = 12;

    public const FINAL_CUT_PRO_TEMPLATES = 'final-cut-pro-templates';
    public const FINAL_CUT_PRO_TEMPLATES_ID = 13;

    public const STOCK_PHOTOS = 'stock-photos';
    public const STOCK_PHOTOS_ID = 14;

    public const PREVIEW_TYPE_VIDEO = 'video';
    public const PREVIEW_TYPE_AUDIO = 'audio';
    public const PREVIEW_TYPE_IMAGE = 'image';

    static public function legacySlugs() {
        return array_merge(
            // first arg is array of keys, second arg is value to be repeated.
            array_fill_keys( [ 'stock-music' ], self::STOCK_MUSIC ),
            array_fill_keys( [ 'adobe-premiere-rush-templates' ], self::PREMIERE_RUSH_TEMPLATES )
        );
    }

    static public function normalizeSlug(string $slug): string
    {
        $legacySlugs = static::legacySlugs();
        if (array_key_exists($slug, $legacySlugs)) {
            return $legacySlugs[$slug];
        }
        return $slug;
    }

    public function categoryIdToVersionIds()
    {
        $versions = (new Versions())->data();

        $versionsByAppId = collect($versions)
            ->groupBy('application_id')
            ->map(function ($versions) {
                return collect($versions)->pluck('id');
            })
            ->toArray();

        $davinciResolveIds = array_merge(
            $versionsByAppId[Applications::DAVINCI_RESOLVE_ID],
            $versionsByAppId[Applications::DAVINCI_RESOLVE_STUDIO_ID]
        );

        return [
            self::DAVINCI_RESOLVE_TEMPLATES_ID => $davinciResolveIds,
            self::DAVINCI_RESOLVE_MACROS_ID => $davinciResolveIds,

            self::AFTER_EFFECTS_PRESETS_ID => $versionsByAppId[Applications::AFTER_EFFECTS_ID],
            self::AFTER_EFFECTS_TEMPLATES_ID => $versionsByAppId[Applications::AFTER_EFFECTS_ID],

            self::PREMIERE_PRO_PRESETS_ID => $versionsByAppId[Applications::PREMIERE_PRO_ID],
            self::PREMIERE_PRO_TEMPLATES_ID => $versionsByAppId[Applications::PREMIERE_PRO_ID],

            // premier pro versions supporting motion graphics templates
            self::MOTION_GRAPHICS_TEMPLATES_ID => [
                Versions::PREMIERE_CC_20171_ID,
                Versions::PREMIERE_CC_2018_ID,
                Versions::PREMIERE_CC_2019_ID,
                Versions::PREMIERE_CC_2020_ID,
            ],

            self::PREMIERE_RUSH_TEMPLATES_ID => $versionsByAppId[Applications::PREMIERE_RUSH_ID],

            self::FINAL_CUT_PRO_TEMPLATES_ID => $versionsByAppId[Applications::FINAL_CUT_PRO_ID],
        ];
    }

    protected $modelClass = Category::class;

    protected $data = [
        [
            'id' => self::AFTER_EFFECTS_TEMPLATES_ID,
            'slug' => self::AFTER_EFFECTS_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::AFTER_EFFECTS_ID,
            'name' => 'After Effects Templates',
            'menu_name' => 'After Effects Templates',
            'display_name' => 'AE Templates',
            'short_name' => 'AE Templates',
            'weight' => 4,
            'order' => 9,
            'sidebar_order' => 9,
            'seo_title' => 'The Best After Effects Templates | Motion Array',
            'meta_description' => 'Amazing After Effects templates with professional designs, neat project organization, and detailed, easy to follow video tutorials.',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::STOCK_VIDEO_ID,
            'slug' => self::STOCK_VIDEO,
            'category_type_id' => CategoryTypes::VIDEO_ID,
            'category_group_id' => CategoryGroups::STOCK_VIDEO_ID,
            'name' => 'Stock Video',
            'menu_name' => 'Stock Video',
            'display_name' => 'Stock Video',
            'short_name' => 'Stock Video',
            'weight' => 2,
            'order' => 25,
            'sidebar_order' => 25,
            'seo_title' => 'The Best Stock Video | Motion Array',
            'meta_description' => 'Download unlimited stock video footage, video effects, and compositing elements for any project. 100% Royalty-free.',
            'has_resolutions' => 1,
            'has_plugins' => 0,
            'has_versions' => 0,
            'has_compressions' => 1,
            'has_fpss' => 1,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 1,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => true,
        ],
        [
            'id' => self::STOCK_MOTION_GRAPHICS_ID,
            'slug' => self::STOCK_MOTION_GRAPHICS,
            'category_type_id' => CategoryTypes::VIDEO_ID,
            'category_group_id' => CategoryGroups::STOCK_VIDEO_ID,
            'name' => 'Stock Motion Graphics',
            'menu_name' => 'Stock Motion Graphics',
            'display_name' => 'Motion Graphics',
            'short_name' => 'Motion Graphics',
            'weight' => 2,
            'order' => 27,
            'sidebar_order' => 27,
            'seo_title' => 'The Best Stock Motion Graphics | Motion Array',
            'meta_description' => 'Beautiful, premium quality stock motion graphics. Compatible with all popular editing software.',
            'has_resolutions' => 1,
            'has_plugins' => 0,
            'has_versions' => 0,
            'has_compressions' => 1,
            'has_fpss' => 1,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 1,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::STOCK_MUSIC_ID,
            'slug' => self::STOCK_MUSIC,
            'category_type_id' => CategoryTypes::AUDIO_ID,
            'category_group_id' => CategoryGroups::STOCK_AUDIO_ID,
            'name' => 'Royalty Free Music',
            'menu_name' => 'Royalty Free Music',
            'display_name' => 'Music',
            'short_name' => 'Music',
            'weight' => 3,
            'order' => 21,
            'sidebar_order' => 21,
            'seo_title' => 'The Best Royalty Free Stock Music | Motion Array',
            'meta_description' => 'Download once & use forever. Get thousands of royalty-free stock music tracks from a wide variety of styles and genres. From podcasts to feature films, we have the professional music you’re looking for.',
            'has_resolutions' => 0,
            'has_plugins' => 0,
            'has_versions' => 0,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 1,
            'has_sample_rates' => 1,
            'has_bpms' => 1,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_AUDIO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::PREMIERE_PRO_TEMPLATES_ID,
            'slug' => self::PREMIERE_PRO_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Premiere Pro Templates',
            'menu_name' => 'Premiere Pro Templates',
            'display_name' => 'PP Templates',
            'short_name' => 'PP Templates',
            'weight' => 4,
            'order' => 1,
            'sidebar_order' => 1,
            'seo_title' => 'The Best Premiere Pro Templates | Motion Array',
            'meta_description' => 'Amazing Premiere Pro templates with professional graphics, creative edits, neat project organization, and detailed, easy to use tutorials for quick results.',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::MOTION_GRAPHICS_TEMPLATES_ID,
            'slug' => self::MOTION_GRAPHICS_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Motion Graphics Templates',
            'menu_name' => 'Motion Graphics Templates',
            'display_name' => 'MOGRT',
            'short_name' => 'MOGRT',
            'weight' => 4,
            'order' => 5,
            'sidebar_order' => 5,
            'seo_title' => 'The Best Motion Graphics Templates | Motion Array',
            'meta_description' => 'Amazing motion graphics templates with professional designs, easy customization, and detailed, easy to follow video tutorials.',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::SOUND_EFFECTS_ID,
            'slug' => self::SOUND_EFFECTS,
            'category_type_id' => CategoryTypes::AUDIO_ID,
            'category_group_id' => CategoryGroups::STOCK_AUDIO_ID,
            'name' => 'Sound Effects',
            'menu_name' => 'Sound Effects',
            'display_name' => 'Sound Effects',
            'short_name' => 'SFX',
            'weight' => 1,
            'order' => 23,
            'sidebar_order' => 23,
            'seo_title' => 'The Best Sound Effects | Motion Array',
            'meta_description' => 'Download once & use forever. Get thousands of royalty-free sound effects for any video production, app, podcast or video game. Quickly and easily find the perfect sound effects for making your next project.',
            'has_resolutions' => 0,
            'has_plugins' => 0,
            'has_versions' => 0,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 1,
            'has_sample_rates' => 1,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_AUDIO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::PREMIERE_PRO_PRESETS_ID,
            'slug' => self::PREMIERE_PRO_PRESETS,
            'category_type_id' => CategoryTypes::PRESETS_ID,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Premiere Pro Presets',
            'menu_name' => 'Premiere Pro Presets',
            'display_name' => 'PP Presets',
            'short_name' => 'PP Presets',
            'weight' => 4,
            'order' => 3,
            'sidebar_order' => 3,
            'seo_title' => 'The Best Premiere Pro Presets | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::AFTER_EFFECTS_PRESETS_ID,
            'slug' => self::AFTER_EFFECTS_PRESETS,
            'category_type_id' => CategoryTypes::PRESETS_ID,
            'category_group_id' => CategoryGroups::AFTER_EFFECTS_ID,
            'name' => 'After Effects Presets',
            'menu_name' => 'After Effects Presets',
            'display_name' => 'AE Presets',
            'short_name' => 'AE Presets',
            'weight' => 4,
            'order' => 11,
            'sidebar_order' => 11,
            'seo_title' => 'The Best After Effects Presets | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::DAVINCI_RESOLVE_TEMPLATES_ID,
            'slug' => self::DAVINCI_RESOLVE_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::DAVINCI_RESOLVE_ID,
            'name' => 'DaVinci Resolve Templates',
            'menu_name' => 'DaVinci Resolve Templates',
            'display_name' => 'DR Templates',
            'short_name' => 'DR Templates',
            'weight' => 4,
            'order' => 15,
            'sidebar_order' => 15,
            'seo_title' => 'The Best DaVinci Resolve Templates | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::PREMIERE_RUSH_TEMPLATES_ID,
            'slug' => self::PREMIERE_RUSH_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::PREMIERE_RUSH_ID,
            'name' => 'Premiere Rush Templates',
            'menu_name' => 'Premiere Rush Templates',
            'display_name' => 'Rush Templates',
            'short_name' => 'Rush Templates',
            'weight' => 3,
            'order' => 7,
            'sidebar_order' => 7,
            'seo_title' => 'The Best Adobe Premiere Rush Templates | Motion Array',
            'meta_description' => 'Download incredible Adobe Premiere Rush templates & transitions created by professionals with stunning designs, simple customization, and easy to follow video tutorials',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::DAVINCI_RESOLVE_MACROS_ID,
            'slug' => self::DAVINCI_RESOLVE_MACROS,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::DAVINCI_RESOLVE_ID,
            'name' => 'DaVinci Resolve Macros',
            'menu_name' => 'DaVinci Resolve Macros',
            'display_name' => 'DR Macros',
            'short_name' => 'DR Macros',
            'weight' => 4,
            'order' => 17,
            'sidebar_order' => 17,
            'seo_title' => 'The Best DaVinci Resolve Macros | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::FINAL_CUT_PRO_TEMPLATES_ID,
            'slug' => self::FINAL_CUT_PRO_TEMPLATES,
            'category_type_id' => CategoryTypes::TEMPLATES_ID,
            'category_group_id' => CategoryGroups::FINAL_CUT_PRO_ID,
            'name' => 'Final Cut Pro Templates',
            'menu_name' => 'Final Cut Pro Templates',
            'display_name' => 'Final Cut Pro Templates',
            'short_name' => 'Final Cut Pro Templates',
            'weight' => 4,
            'order' => 13,
            'sidebar_order' => 13,
            'seo_title' => 'The Best Final Cut Pro Templates | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 1,
            'has_plugins' => 1,
            'has_versions' => 1,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_VIDEO,
            'can_be_used_for_editorial_use' => false,
        ],
        [
            'id' => self::STOCK_PHOTOS_ID,
            'slug' => self::STOCK_PHOTOS,
            'category_type_id' => CategoryTypes::IMAGES_ID,
            'category_group_id' => CategoryGroups::IMAGES_ID,
            'name' => 'Stock Photos',
            'menu_name' => 'Stock Photos',
            'display_name' => 'Stock Photos',
            'short_name' => 'Stock Photos',
            'weight' => 1,
            'order' => 19,
            'sidebar_order' => 19,
            'seo_title' => 'The Best Stock Photos | Motion Array',
            'meta_description' => '',
            'has_resolutions' => 0,
            'has_plugins' => 0,
            'has_versions' => 0,
            'has_compressions' => 0,
            'has_fpss' => 0,
            'has_formats' => 0,
            'has_sample_rates' => 0,
            'has_bpms' => 0,
            'add_watermark' => 0,
            'preview_type' => self::PREVIEW_TYPE_IMAGE,
            'can_be_used_for_editorial_use' => true,
        ],
    ];
}
