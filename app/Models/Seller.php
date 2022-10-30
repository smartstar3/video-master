<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

Trait Seller
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function products()
    {
        return $this->hasMany('MotionArray\Models\Product', 'seller_id', 'id');
    }

    public function submissions()
    {
        return $this->hasMany('MotionArray\Models\Submission', 'seller_id', 'id');
    }

    public function profileImage()
    {
        return $this->hasOne('MotionArray\Models\File', 'id', 'profile_image_id');
    }

    public function headerImage()
    {
        return $this->hasOne('MotionArray\Models\File', 'id', 'header_image_id');
    }

    public function sellerProfile()
    {
        return $this->hasOne('MotionArray\Models\SellerProfile', 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Getters and Setters
    |--------------------------------------------------------------------------
    */
    public function getSellerNameAttribute()
    {
        if (!empty($this->company_name)) {
            return $this->company_name;
        } else {
            return trim("{$this->firstname} {$this->lastname}");
        }
    }

    public function getProfileImageUrlAttribute()
    {
        if (!empty($this->profileImage)) {
            return $this->profileImage->url;
        } else {
            return null;
        }
    }

    public function getHeaderImageUrlAttribute()
    {
        if (!empty($this->headerImage)) {
            return $this->headerImage->url;
        } else {
            return null;
        }
    }

    public function getIsNewSellerAttribute()
    {
        $yesterday = Carbon::now()->subDays(1);

        $firstApprovedSubmission = $this->submissions()->approved()->orderBy('created_at')->first();

        return (!$firstApprovedSubmission || ($firstApprovedSubmission->created_at > $yesterday));
    }

    public function getSubmissionLimit()
    {
        if($this->sellerProfile()->exists()) {
            return $this->sellerProfile->submission_limit;
        }

        return config('submissions.limit');
    }

    /*
    |--------------------------------------------------------------------------
    | Repo Methods
    |--------------------------------------------------------------------------
    | These methods may be moved to a repository later on in the development
    | cycle as needed.
    */
    public function amIFollowing()
    {
        if (!auth()->check()) {
            return false;
        }

        $sellerFollower = SellerFollower::whereSellerId($this->id)->whereFollowerId(Auth::user()->id)->first();

        return !empty($sellerFollower);
    }

    public function follow()
    {
        $sellerFollower = SellerFollower::whereSellerId($this->id)->whereFollowerId(Auth::user()->id)->first();
        if (empty($sellerFollower)) {
            $sellerFollower = new SellerFollower();
            $sellerFollower->seller_id = $this->id;
            $sellerFollower->follower_id = Auth::user()->id;
            $sellerFollower->save();
        }

        return $sellerFollower;
    }

    public function unfollow()
    {
        $sellerFollower = SellerFollower::whereSellerId($this->id)->whereFollowerId(Auth::user()->id)->first();
        if (!empty($sellerFollower)) {
            $sellerFollower->delete();
        }

        return $sellerFollower;
    }

    public function getFollowerCount()
    {
        return SellerFollower::whereSellerId($this->id)->count();
    }

    public function hasApprovedSubmissions()
    {
        return $this->submissions()->approved()->exists();
    }
}
