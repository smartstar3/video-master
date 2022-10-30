<?php

namespace MotionArray\Models\StaticData;

class EncodingStatuses extends StaticDBData
{
    public const WAITING = 'Waiting';
    public const WAITING_ID = 1;

    public const IN_PROGRESS = 'In progress';
    public const IN_PROGRESS_ID = 2;

    public const PARTIALLY_FINISHED = 'Partially Finished';
    public const PARTIALLY_FINISHED_ID = 3;

    public const FAILED = 'Failed';
    public const FAILED_ID = 4;

    public const PARTIALLY_FAILED = 'Partially failed';
    public const PARTIALLY_FAILED_ID = 5;

    public const CANCELLED = 'Cancelled';
    public const CANCELLED_ID = 6;

    public const READY_TO_SEND = 'Ready to send';
    public const READY_TO_SEND_ID = 7;

    public const FINISHED = 'Finished';
    public const FINISHED_ID = 8;

    protected $modelClass = \MotionArray\Models\EncodingStatus::class;

    protected $data = [
        [
            'id' => self::WAITING_ID,
            'status' => self::WAITING,
        ],
        [
            'id' => self::IN_PROGRESS_ID,
            'status' => self::IN_PROGRESS,
        ],
        [
            'id' => self::PARTIALLY_FINISHED_ID,
            'status' => self::PARTIALLY_FINISHED,
        ],
        [
            'id' => self::FAILED_ID,
            'status' => self::FAILED,
        ],
        [
            'id' => self::PARTIALLY_FAILED_ID,
            'status' => self::PARTIALLY_FAILED,
        ],
        [
            'id' => self::CANCELLED_ID,
            'status' => self::CANCELLED,
        ],
        [
            'id' => self::READY_TO_SEND_ID,
            'status' => self::READY_TO_SEND,
        ],
        [
            'id' => self::FINISHED_ID,
            'status' => self::FINISHED,
        ],
    ];
}
