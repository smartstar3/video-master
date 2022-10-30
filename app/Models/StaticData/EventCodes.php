<?php

namespace MotionArray\Models\StaticData;

class EventCodes extends StaticDBData
{
    public const READY = 'Ready';
    public const READY_ID = 1;

    public const SEND_PREVIEW_FOR_ENCODING = 'Send preview for encoding';
    public const SEND_PREVIEW_FOR_ENCODING_ID = 2;

    public const STORE_VIDEO_PREVIEW_FILE_DETAILS = 'Store video preview file details';
    public const STORE_VIDEO_PREVIEW_FILE_DETAILS_ID = 3;

    public const DELETE_PREVIEW = 'Delete preview';
    public const DELETE_PREVIEW_ID = 4;

    public const DELETE_PACKAGE = 'Delete package';
    public const DELETE_PACKAGE_ID = 5;

    protected $modelClass = \MotionArray\Models\EventCode::class;

    protected $data = [
        [
            'id' => self::READY_ID,
            'event' => self::READY,
        ],
        [
            'id' => self::SEND_PREVIEW_FOR_ENCODING_ID,
            'event' => self::SEND_PREVIEW_FOR_ENCODING,
        ],
        [
            'id' => self::STORE_VIDEO_PREVIEW_FILE_DETAILS_ID,
            'event' => self::STORE_VIDEO_PREVIEW_FILE_DETAILS,
        ],
        [
            'id' => self::DELETE_PREVIEW_ID,
            'event' => self::DELETE_PREVIEW,
        ],
        [
            'id' => self::DELETE_PACKAGE_ID,
            'event' => self::DELETE_PACKAGE,
        ],
    ];
}
