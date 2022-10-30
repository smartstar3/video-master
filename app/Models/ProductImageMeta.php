<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImageMeta extends Model
{
    protected $table = 'product_image_meta';
    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'product_image_meta_orientation_id' => 'integer',
        'product_id' => 'integer',
    ];
}
