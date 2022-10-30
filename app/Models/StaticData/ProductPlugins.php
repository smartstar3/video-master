<?php

namespace MotionArray\Models\StaticData;

class ProductPlugins extends StaticDBData
{
    public const NO_PLUG_INS = 'No Plug-Ins';
    public const NO_PLUG_INS_ID = 1;

    public const TRAPCODE_PARTICULAR = 'Trapcode Particular';
    public const TRAPCODE_PARTICULAR_ID = 2;

    public const TRAPCODE_FORM = 'Trapcode Form';
    public const TRAPCODE_FORM_ID = 3;

    public const TRAPCODE_SHINE = 'Trapcode Shine';
    public const TRAPCODE_SHINE_ID = 4;

    public const TRAPCODE_STARGLOW = 'Trapcode Starglow';
    public const TRAPCODE_STARGLOW_ID = 5;

    public const TRAPCODE_LUX = 'Trapcode Lux';
    public const TRAPCODE_LUX_ID = 6;

    public const TRAPCODE_3D_STROKE = 'Trapcode 3D Stroke';
    public const TRAPCODE_3D_STROKE_ID = 7;

    public const VC_OPTICAL_FLARES = 'VC Optical Flares';
    public const VC_OPTICAL_FLARES_ID = 8;

    public const VC_ELEMENT_3D = 'VC Element 3D';
    public const VC_ELEMENT_3D_ID = 9;

    public const VC_SABER = 'VC Saber';
    public const VC_SABER_ID = 10;

    public const VC_SURE_TARGET = 'VC Sure Target';
    public const VC_SURE_TARGET_ID = 11;

    public const DUIK = 'DuiK';
    public const DUIK_ID = 12;

    public const VC_COLOR_VIBRANCE = 'VC Color Vibrance';
    public const VC_COLOR_VIBRANCE_ID = 13;

    public const STARDUST = 'Stardust';
    public const STARDUST_ID = 14;

    public const PLEXUS = 'Plexus';
    public const PLEXUS_ID = 15;

    public const MOTION_ARRAY_PLUGINS = 'Motion Array Plugins';
    public const MOTION_ARRAY_PLUGINS_ID = 16;

    public const PIX_MAP_PLUGINS = 'Pix Map';
    public const PIX_MAP_PLUGINS_ID = 17;

    protected $modelClass = \MotionArray\Models\ProductPlugin::class;

    protected $data = [
        [
            'id' => self::NO_PLUG_INS_ID,
            'name' => self::NO_PLUG_INS,
        ],
        [
            'id' => self::TRAPCODE_PARTICULAR_ID,
            'name' => self::TRAPCODE_PARTICULAR,
        ],
        [
            'id' => self::TRAPCODE_FORM_ID,
            'name' => self::TRAPCODE_FORM,
        ],
        [
            'id' => self::TRAPCODE_SHINE_ID,
            'name' => self::TRAPCODE_SHINE,
        ],
        [
            'id' => self::TRAPCODE_STARGLOW_ID,
            'name' => self::TRAPCODE_STARGLOW,
        ],
        [
            'id' => self::TRAPCODE_LUX_ID,
            'name' => self::TRAPCODE_LUX,
        ],
        [
            'id' => self::TRAPCODE_3D_STROKE_ID,
            'name' => self::TRAPCODE_3D_STROKE,
        ],
        [
            'id' => self::VC_OPTICAL_FLARES_ID,
            'name' => self::VC_OPTICAL_FLARES,
        ],
        [
            'id' => self::VC_ELEMENT_3D_ID,
            'name' => self::VC_ELEMENT_3D,
        ],
        [
            'id' => self::VC_SABER_ID,
            'name' => self::VC_SABER,
        ],
        [
            'id' => self::VC_SURE_TARGET_ID,
            'name' => self::VC_SURE_TARGET,
        ],
        [
            'id' => self::DUIK_ID,
            'name' => self::DUIK,
        ],
        [
            'id' => self::VC_COLOR_VIBRANCE_ID,
            'name' => self::VC_COLOR_VIBRANCE,
        ],
        [
            'id' => self::STARDUST_ID,
            'name' => self::STARDUST,
        ],
        [
            'id' => self::PLEXUS_ID,
            'name' => self::PLEXUS,
        ],
        [
            'id' => self::MOTION_ARRAY_PLUGINS_ID,
            'name' => self::MOTION_ARRAY_PLUGINS,
        ],
        [
            'id' => self::PIX_MAP_PLUGINS_ID,
            'name' => self::PIX_MAP_PLUGINS,
        ],
    ];
}
