<?php namespace MotionArray\Models;

use MotionArray\Helpers\Imgix;

abstract class UserSiteApp extends BaseModel
{
    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function getEmailAttribute($value)
    {
        return $value ? $value : $this->owner->email;
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function site()
    {
        return $this->belongsTo('\MotionArray\Models\UserSite', 'user_site_id');
    }

    public function owner()
    {
        return $this->site->user();
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/

}