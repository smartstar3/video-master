<?php

namespace MotionArray\Models\StaticData;

class RequestStatuses extends StaticDBData
{
    public const NEW = 'new';
    public const NEW_ID = 1;

    public const ACTIVE = 'active';
    public const ACTIVE_ID = 2;

    public const COMPLETE = 'complete';
    public const COMPLETE_ID = 3;

    public const REJECTED = 'rejected';
    public const REJECTED_ID = 4;

    protected $modelClass = \MotionArray\Models\RequestStatus::class;

    protected $data = [
        [
            'id' => self::NEW_ID,
            'slug' => self::NEW,
            'name' => 'New',
        ],
        [
            'id' => self::ACTIVE_ID,
            'slug' => self::ACTIVE,
            'name' => 'Active',
        ],
        [
            'id' => self::COMPLETE_ID,
            'slug' => self::COMPLETE,
            'name' => 'Complete',
        ],
        [
            'id' => self::REJECTED_ID,
            'slug' => self::REJECTED,
            'name' => 'Rejected',
        ],
    ];
}
