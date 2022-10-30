<?php

namespace MotionArray\Models\StaticData;

class SubmissionStatuses extends StaticDBData
{
    public const NEW = 'new';
    public const NEW_ID = 1;

    public const PENDING = 'pending';
    public const PENDING_ID = 2;

    public const APPROVED = 'approved';
    public const APPROVED_ID = 3;

    public const NEEDS_WORK = 'needs-work';
    public const NEEDS_WORK_ID = 4;

    public const REJECTED = 'rejected';
    public const REJECTED_ID = 5;

    protected $modelClass = \MotionArray\Models\SubmissionStatus::class;

    protected $data = [
        [
            'id' => self::NEW_ID,
            'status' => self::NEW,
            'label' => 'New',
        ],
        [
            'id' => self::PENDING_ID,
            'status' => self::PENDING,
            'label' => 'Pending Review',
        ],
        [
            'id' => self::APPROVED_ID,
            'status' => self::APPROVED,
            'label' => 'Approved',
        ],
        [
            'id' => self::NEEDS_WORK_ID,
            'status' => self::NEEDS_WORK,
            'label' => 'Needs Work',
        ],
        [
            'id' => self::REJECTED_ID,
            'status' => self::REJECTED,
            'label' => 'Rejected',
        ],
    ];
}
