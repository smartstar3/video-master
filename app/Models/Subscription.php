<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;

class Subscription extends Model
{
    use SoftDeletes;

    protected $casts = [
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'payment_gateway_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'subscription_status_id' => 'integer',
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function successfulPayments()
    {
         return $this->payments()
             ->where('subscription_payment_status_id', SubscriptionPaymentStatuses::STATUS_SUCCESS_ID)
            ;
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('subscription_status_id', SubscriptionStatuses::STATUS_ACTIVE_ID)
            ->whereBetween(\DB::raw('"'.now()->format('Y-m-d H:i:s').'"'), [
                \DB::raw('start_at'),
                \DB::raw('end_at'),
            ])
            ;
    }

    /**
     * There was a valid subscription for user, but we couldn't collect payment.
     * So because of that, we couldn't update `end_date`. And subscription expired.
     *
     * @return bool
     */
    public function expired(): bool
    {
        return $this->end_at->lessThan(now());
    }
}
