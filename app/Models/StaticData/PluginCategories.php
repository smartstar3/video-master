<?php

namespace MotionArray\Models\StaticData;

class PluginCategories extends StaticDBData
{
    public const PLUGINS = 'plugins';
    public const PLUGINS_ID = 1;

    public const TRANSITIONS = 'transitions';
    public const TRANSITIONS_ID = 2;

    public const EFFECTS = 'effects';
    public const EFFECTS_ID = 3;

    protected $modelClass = \MotionArray\Models\PluginCategory::class;

    protected $data = [
        [
            'id' => self::PLUGINS_ID,
            'slug' => self::PLUGINS,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Plugins',
            'order' => 0,
            'deleted_at' => '2018-08-10 00:00:00'
        ],
        [
            'id' => self::TRANSITIONS_ID,
            'slug' => self::TRANSITIONS,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Transitions',
            'order' => 0,
            'deleted_at' => null,
        ],
        [
            'id' => self::EFFECTS_ID,
            'slug' => self::EFFECTS,
            'category_group_id' => CategoryGroups::PREMIERE_PRO_ID,
            'name' => 'Effects',
            'order' => 0,
            'deleted_at' => null,
        ],
    ];
}
