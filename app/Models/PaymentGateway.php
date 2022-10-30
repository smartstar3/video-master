<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    public $timestamps = false;

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}
