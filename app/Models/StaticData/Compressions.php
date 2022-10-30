<?php

namespace MotionArray\Models\StaticData;

class Compressions extends StaticDBData
{
    public const PHOTO_JPEG = 'photo-jpeg';
    public const PHOTO_JPEG_ID = 1;

    public const PRO_RES_422 = 'prores-422';
    public const PRO_RES_422_ID = 2;

    public const PNG__ALPHA = 'png--alpha';
    public const PNG__ALPHA_ID = 3;

    public const H_264 = 'h-264';
    public const H_264_ID = 4;

    public const H_265 = 'h-265';
    public const H_265_ID = 5;

    public const ANIMATION = 'animation';
    public const ANIMATION_ID = 6;

    public const ANIMATION_PLUS_ALPHA = 'animation---alpha';
    public const ANIMATION_PLUS_ALPHA_ID = 7;

    public const PNG = 'png';
    public const PNG_ID = 8;

    public const PRO_RES_4444 = 'prores-4444';
    public const PRO_RES_4444_ID = 9;

    protected $modelClass = \MotionArray\Models\Compression::class;

    protected $data = [
        [
            'id' => self::PHOTO_JPEG_ID,
            'slug' => self::PHOTO_JPEG,
            'name' => 'Photo Jpeg',
        ],
        [
            'id' => self::PRO_RES_422_ID,
            'slug' => self::PRO_RES_422,
            'name' => 'ProRes 422',
        ],
        [
            'id' => self::PNG__ALPHA_ID,
            'slug' => self::PNG__ALPHA,
            'name' => 'PNG +Alpha',
        ],
        [
            'id' => self::H_264_ID,
            'slug' => self::H_264,
            'name' => 'H.264',
        ],
        [
            'id' => self::H_265_ID,
            'slug' => self::H_265,
            'name' => 'H.265',
        ],
        [
            'id' => self::ANIMATION_ID,
            'slug' => self::ANIMATION,
            'name' => 'Animation',
        ],
        [
            'id' => self::ANIMATION_PLUS_ALPHA_ID,
            'slug' => self::ANIMATION_PLUS_ALPHA,
            'name' => 'Animation + Alpha',
        ],
        [
            'id' => self::PNG_ID,
            'slug' => self::PNG,
            'name' => 'PNG',
        ],
        [
            'id' => self::PRO_RES_4444_ID,
            'slug' => self::PRO_RES_4444,
            'name' => 'ProRes 4444',
        ],
    ];
}
