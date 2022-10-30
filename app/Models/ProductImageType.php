<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImageType extends Model
{
    public $timestamps = false;

    protected $casts = [
        'max_width' => 'integer',
        'max_height' => 'integer',
        'has_watermark' => 'boolean',
    ];
}
