<?php namespace MotionArray\Models;

class SellerPayout extends BaseModel
{
    protected $table = 'seller_payouts';

    protected $dates = ['period_start_at'];

    protected $fillable = ['user_id', 'period_start_at', 'amount'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function seller()
    {
        return $this->belongsTo('MotionArray\Models\User', 'user_id');
    }
}
