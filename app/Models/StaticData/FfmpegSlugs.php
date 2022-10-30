<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Models\Resolution;
use MotionArray\Models\Fps;
use MotionArray\Models\Compression;
use MotionArray\Models\FfmpegSlug;

class FfmpegSlugs extends StaticDBData
{
    public const COMPRESSION_SLUG_TYPE = Compression::class;
    public const FPS_SLUG_TYPE = Fps::class;
    public const RESOLUTION_SLUG_TYPE = Resolution::class;

    public const COMPRESSION_MJPEG_YUVJ422P = 'mjpeg-yuvj422p';
    public const COMPRESSION_MJPEG_YUVJ422P_ID = 1;

    public const COMPRESSION_MJPEG_YUVJ420P = 'mjpeg-yuvj420p';
    public const COMPRESSION_MJPEG_YUVJ420P_ID = 2;

    public const COMPRESSION_PRORES_YUV422P101E = 'prores-yuv422p10le';
    public const COMPRESSION_PRORES_YUV422P101E_ID = 3;

    public const COMPRESSION_PNG_RGBAS = 'png-rgba';
    public const COMPRESSION_PNG_RGBAS_ID = 4;

    public const COMPRESSION_H264_YUV420P = 'h264-yuv420p';
    public const COMPRESSION_H264_YUV420P_ID = 5;

    public const COMPRESSION_HEVC_YUVJ420P = 'hevc-yuvj420p';
    public const COMPRESSION_HEVC_YUVJ420P_ID = 6;

    public const COMPRESSION_QTRLE_RGB24 = 'qtrle-rgb24';
    public const COMPRESSION_QTRLE_RGB24_ID = 7;

    public const COMPRESSION_QTRLE_BGRA = 'qtrle-bgra';
    public const COMPRESSION_QTRLE_BGRA_ID = 8;

    public const COMPRESSION_PNG_RGB24 = 'png-rgb24';
    public const COMPRESSION_PNG_RGB24_ID = 9;

    public const COMPRESSION_PRORES_YUV444P101E = 'prores-yuv444p10le';
    public const COMPRESSION_PRORES_YUV444P101E_ID = 10;

    public const FPS_30000_DIVIDE_1001 = '30000/1001';
    public const FPS_30000_DIVIDE_1001_ID = 11;

    public const FPS_24000_DIVIDE_1001 = '24000/1001';
    public const FPS_24000_DIVIDE_1001_ID = 12;

    public const FPS_24_DIVIDE_1 = '24/1';
    public const FPS_24_DIVIDE_1_ID = 13;

    public const FPS_30_DIVIDE_1 = '30/1';
    public const FPS_30_DIVIDE_1_ID = 14;

    public const FPS_25_DIVIDE_1 = '25/1';
    public const FPS_25_DIVIDE_1_ID = 15;

    public const FPS_60_DIVIDE_1 = '60/1';
    public const FPS_60_DIVIDE_1_ID = 16;

    public const FPS_60000_DIVIDE_1001 = '60000/1001';
    public const FPS_60000_DIVIDE_1001_ID = 17;

    public const RES_1920_X_1080 = '1920x1080';
    public const RES_1920_X_1080_ID = '18';

    public const RES_2048_X_1556 = '2048x1556';
    public const RES_2048_X_1556_ID = '19';

    public const RES_4096_X_2304 = '4096x2304';
    public const RES_4096_X_2304_ID = '20';

    public const RES_3840_X_2160 = '3840x2160';
    public const RES_3840_X_2160_ID = '21';

    public const RES_1280_X_720 = '1280x720';
    public const RES_1280_X_720_ID = '22';

    public const RES_1080_X_1920 = '1080x1920';
    public const RES_1080_X_1920_ID = '23';

    public const RES_1080_X_1080 = '1080x1080';
    public const RES_1080_X_1080_ID = '24';

    public const RES_7680_X_4320 = '7680x4320';
    public const RES_7680_X_4320_ID = '25';

    public const RES_4096_X_2160 = '4096x2160';
    public const RES_4096_X_2160_ID = '26';

    public const FPS_120_DIVIDE_1 = '120/1';
    public const FPS_120_DIVIDE_1_ID = 27;

    public const FPS_2997_DIVIDE_125 = '2997/125';
    public const FPS_2997_DIVIDE_125_ID = 28;

    protected $modelClass = FfmpegSlug::class;

    protected $data = [
        [
            'id' => self::COMPRESSION_MJPEG_YUVJ422P_ID,
            'slug' => self::COMPRESSION_MJPEG_YUVJ422P,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PHOTO_JPEG_ID,
        ],
        [
            'id' => self::COMPRESSION_MJPEG_YUVJ420P_ID,
            'slug' => self::COMPRESSION_MJPEG_YUVJ420P,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PHOTO_JPEG_ID,
        ],
        [
            'id' => self::COMPRESSION_PRORES_YUV422P101E_ID,
            'slug' => self::COMPRESSION_PRORES_YUV422P101E,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PRO_RES_422_ID,
        ],
        [
            'id' => self::COMPRESSION_PNG_RGBAS_ID,
            'slug' => self::COMPRESSION_PNG_RGBAS,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PNG__ALPHA_ID,
        ],
        [
            'id' => self::COMPRESSION_H264_YUV420P_ID,
            'slug' => self::COMPRESSION_H264_YUV420P,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::H_264_ID,
        ],
        [
            'id' => self::COMPRESSION_HEVC_YUVJ420P_ID,
            'slug' => self::COMPRESSION_HEVC_YUVJ420P,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::H_265_ID,
        ],
        [
            'id' => self::COMPRESSION_QTRLE_RGB24_ID,
            'slug' => self::COMPRESSION_QTRLE_RGB24,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::ANIMATION_ID,
        ],
        [
            'id' => self::COMPRESSION_QTRLE_BGRA_ID,
            'slug' => self::COMPRESSION_QTRLE_BGRA,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::ANIMATION_PLUS_ALPHA_ID,
        ],
        [
            'id' => self::COMPRESSION_PNG_RGB24_ID,
            'slug' => self::COMPRESSION_PNG_RGB24,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PNG_ID,
        ],
        [
            'id' => self::COMPRESSION_PRORES_YUV444P101E_ID,
            'slug' => self::COMPRESSION_PRORES_YUV444P101E,
            'ffmpeg_sluggable_type' => self::COMPRESSION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Compressions::PRO_RES_4444_ID,
        ],
        [
            'id' => self::FPS_30000_DIVIDE_1001_ID,
            'slug' => self::FPS_30000_DIVIDE_1001,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_29_97_FPS_ID,
        ],
        [
            'id' => self::FPS_24000_DIVIDE_1001_ID,
            'slug' => self::FPS_24000_DIVIDE_1001,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_23_97_FPS_ID,
        ],
        [
            'id' => self::FPS_24_DIVIDE_1_ID,
            'slug' => self::FPS_24_DIVIDE_1,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_24_FPS_ID,
        ],
        [
            'id' => self::FPS_30_DIVIDE_1_ID,
            'slug' => self::FPS_30_DIVIDE_1,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_30_FPS_ID,
        ],
        [
            'id' => self::FPS_25_DIVIDE_1_ID,
            'slug' => self::FPS_25_DIVIDE_1,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_25_FPS_ID,
        ],
        [
            'id' => self::FPS_60_DIVIDE_1_ID,
            'slug' => self::FPS_60_DIVIDE_1,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_60_FPS_ID,
        ],
        [
            'id' => self::FPS_60000_DIVIDE_1001_ID,
            'slug' => self::FPS_60000_DIVIDE_1001,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_59_94_FPS_ID,
        ],
        [
            'id' => self::FPS_120_DIVIDE_1_ID,
            'slug' => self::FPS_120_DIVIDE_1,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_120_FPS_ID,
        ],
        [
            'id' => self::FPS_2997_DIVIDE_125_ID,
            'slug' => self::FPS_2997_DIVIDE_125,
            'ffmpeg_sluggable_type' => self::FPS_SLUG_TYPE,
            'ffmpeg_sluggable_id' => FramesPerSeconds::FPS_23_97_FPS_ID,
        ],
        [
            'id' => self::RES_1920_X_1080_ID,
            'slug' => self::RES_1920_X_1080,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_1920X1080_HD_ID,
        ],
        [
            'id' => self::RES_2048_X_1556_ID,
            'slug' => self::RES_2048_X_1556,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_2048X1556_2K_ID,
        ],
        [
            'id' => self::RES_4096_X_2304_ID,
            'slug' => self::RES_4096_X_2304,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_4096X2304_4K_ID,
        ],
        [
            'id' => self::RES_3840_X_2160_ID,
            'slug' => self::RES_3840_X_2160,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_3840X2160_4K_ID,
        ],
        [
            'id' => self::RES_1280_X_720_ID,
            'slug' => self::RES_1280_X_720,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_1280X720_ID,
        ],
        [
            'id' => self::RES_1080_X_1920_ID,
            'slug' => self::RES_1080_X_1920,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_1080X1920_VERTICAL_ID,
        ],
        [
            'id' => self::RES_1080_X_1080_ID,
            'slug' => self::RES_1080_X_1080,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_1080X1080_SQUARE_ID,
        ],
        [
            'id' => self::RES_7680_X_4320_ID,
            'slug' => self::RES_7680_X_4320,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_7680X4320_8K_ID,
        ],
        [
            'id' => self::RES_4096_X_2160_ID,
            'slug' => self::RES_4096_X_2160,
            'ffmpeg_sluggable_type' => self::RESOLUTION_SLUG_TYPE,
            'ffmpeg_sluggable_id' => Resolutions::RES_4096X2160_4K_ID,
        ],
    ];
}
