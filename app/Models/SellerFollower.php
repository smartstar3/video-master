<?php namespace MotionArray\Models;

class SellerFollower extends BaseModel
{
    protected $table = 'seller_followers';

    public function seller()
    {
        return $this->belongsTo('MotionArray\Models\User', "seller_id");
    }

    public function follower()
    {
        return $this->belongsTo('MotionArray\Models\User', "follower_id");
    }
}
