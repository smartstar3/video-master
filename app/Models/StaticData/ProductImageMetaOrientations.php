<?php

namespace MotionArray\Models\StaticData;

class ProductImageMetaOrientations extends StaticDBData
{
    public const VERTICAL_ID = 1;
    public const VERTICAL = 'vertical';

    public const HORIZONTAL_ID = 2;
    public const HORIZONTAL = 'horizontal';

    public const SQUARE_ID = 3;
    public const SQUARE = 'square';

    protected $modelClass = \MotionArray\Models\ProductImageMetaOrientation::class;

    protected $data = [
        [
            'id' => self::VERTICAL_ID,
            'slug' => self::VERTICAL,
            'name' => 'Vertical',
        ],
        [
            'id' => self::HORIZONTAL_ID,
            'slug' => self::HORIZONTAL,
            'name' => 'Horizontal',
        ],
        [
            'id' => self::SQUARE_ID,
            'slug' => self::SQUARE,
            'name' => 'Square',
        ],
    ];

}
