<?php

namespace MotionArray\Models\StaticData;

class ProductLevels extends StaticDBData
{
    public const KICK_ASS = 'kick-ass';
    public const KICK_ASS_ID = 1;

    protected $modelClass = \MotionArray\Models\ProductLevel::class;

    protected $data = [
        [
            'id' => self::KICK_ASS_ID,
            'label' => self::KICK_ASS,
            'name' => 'Kick Ass',
            'value' => 100,
        ],
    ];
}
