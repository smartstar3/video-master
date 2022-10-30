<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use MotionArray\Jobs\UpdateUserOnIntercom;

class UserSite extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'slug' => 'nullable|unique:user_sites,slug,{:id}|subdomain|required_if:use_domain,0|max:100',
        'domain' => 'nullable|unique:user_sites,domain,{:id}|required_if:use_domain,1|max:255'
    ];

    public static $messages = [
        'slug.unique' => 'This subdomain has already been taken.',
        'slug.subdomain' => 'This is not a valid subdomain',
        'slug.required_if' => 'Please enter a valid subdomain',
        'domain.unique' => 'This domain has already been taken.',
        'domain.required_if' => 'Please enter a valid domain',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo('\MotionArray\Models\User');
    }

    public function portfolio()
    {
        return $this->hasOne('\MotionArray\Models\Portfolio');
    }

    public function review()
    {
        return $this->hasOne('\MotionArray\Models\Review');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function setDomainAttribute($domain)
    {
        $domain = preg_replace('#https?\:\/\/#', '', $domain);

        $domain = preg_replace('#www\.#', '', $domain);

        $this->attributes['domain'] = $domain;
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
    public function getPortfolioUrl($path = null)
    {
        if ($this->use_domain) {
            $url = trim($this->domain);

            if (strpos('://', $url) === false) {
                $url = 'http://' . $url;
            }
        } else {
            $url = $this->getSubdomainUrl();
        }

        if ($path) {
            $url = $url . '/' . $path;
        }

        return $url;
    }

    public function getReviewUrl($path = null)
    {
        if ($this->use_domain && !$this->reviews_same_url) {
            return $this->getSubdomainUrl($path);
        }

        return $this->getPortfolioUrl($path);
    }

    public function getSubdomainUrl($path = null)
    {
        $url = 'http' . (config('portfolio.secure') ? 's' : '') . '://' . trim($this->slug) . '.' . config('portfolio.domain');

        if ($path) {
            return $url . '/' . $path;
        }

        return $url;
    }

    public function setDefaultSlug()
    {
        $user = $this->user;

        $slug = $user->firstname . $user->lastname . $user->id;

        // Strip everything but alphanumeric
        $this->slug = preg_replace("/[^a-z0-9]+/i", "", $slug);
    }
}

UserSite::saving(function (UserSite $site) {
    if ($site->domain) {
        $site->domain = strtolower(trim($site->domain));
    }

    if ($site->slug) {
        $site->slug = strtolower(trim($site->slug));
    }

    return $site->validate();
});

UserSite::creating(function (UserSite $site) {
    $site->setDefaultSlug();

    return true;
});

// Update Intercom data
UserSite::saved(function ($userSite) {
    $user = $userSite->user;

    if ($user) {
        Bus::dispatch(new UpdateUserOnIntercom($user));
    }
});