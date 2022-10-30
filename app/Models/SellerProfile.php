<?php namespace MotionArray\Models;

class SellerProfile extends BaseModel
{
    protected $table = 'seller_profile';

    public function seller()
    {
        return $this->belongsTo(User::class, "seller_id");
    }

}
