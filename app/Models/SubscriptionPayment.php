<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'subscription_id' => 'integer',
        'attempted_at' => 'datetime',
        'amount' => 'integer',
        'fee' => 'integer',
        'subscription_payment_status_id' => 'integer',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
