<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends UserSiteApp
{
    use SoftDeletes;

    const EMAIL_NOTIFICATION_INSTANT = 1;
    const EMAIL_NOTIFICATION_30_MINS = 2;
    const EMAIL_NOTIFICATION_NEVER = 3;

    protected $appends = ['url'];

    protected $casts = [
        'settings' => 'json',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function getUrlAttribute()
    {
        return $this->site->getReviewUrl();
    }

    public function getFaviconAttribute()
    {
        if ($this->settings && isset($this->settings['favicon']) && $this->settings['favicon']) {
            return $this->settings['favicon'];
        }
    }

    public function getLogoAttribute()
    {
        if ($this->settings && isset($this->settings['logo']) && $this->settings['logo']) {
            return $this->settings['logo'];
        }
    }

    public function getEmailNotificationAttribute()
    {
        if ($this->settings && isset($this->settings['email_notification']) && $this->settings['email_notification']) {
            return intval($this->settings['email_notification']);
        }
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function projects()
    {
        return $this->owner->projects()->public();
    }

    public function activeProjects()
    {
        return $this->projects()->active();
    }

    public function reviewProjects()
    {
        return $this->owner->projects()->withReview();
    }
}
