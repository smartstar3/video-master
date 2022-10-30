<?php namespace MotionArray\Models;

use Illuminate\Support\Facades\App;
use MotionArray\Helpers\Helpers;
use MotionArray\Traits\PresentableTrait;

class SellerReview extends BaseModel
{
    use PresentableTrait;

    protected $presenter = 'MotionArray\Presenters\SellerReviewPresenter';

    protected $guarded = [];

    public static $rules = [];

    public static $updateRules = [];

    public static $messages = [];

    public function seller()
    {
        return $this->belongsTo('MotionArray\Models\User', 'seller_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo('MotionArray\Models\User', 'reviewer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('MotionArray\Models\Product', 'product_id', 'id');
    }
}
