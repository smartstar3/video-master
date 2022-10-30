<?php namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Jenssegers\Agent\Facades\Agent;
use MotionArray\Jobs\UpdateUserOnIntercom;
use MotionArray\Services\Intercom\IntercomPortfolioObserver;
use MotionArray\Services\Intercom\IntercomService;
use MotionArray\Services\Intercom\IntercomUserObserver;
use Request;
use Config;

class Portfolio extends UserSiteApp
{
    use SoftDeletes;

    protected $appends = ['url'];

    protected $casts = [
        'settings' => 'json',
        'unpublished_settings' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        if (app(IntercomService::class)->enabled()) {
            static::observe(IntercomPortfolioObserver::class);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */
    public function getUrlAttribute()
    {
        return $this->site->getPortfolioUrl();
    }

    public function getEditableClassAttribute()
    {
        if ($this->canEdit()) {
            return 'editable js-populated';
        }
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function pages()
    {
        return $this->hasMany('\MotionArray\Models\PortfolioPage');
    }

    public function portfolioTheme()
    {
        return $this->belongsTo('\MotionArray\Models\PortfolioTheme');
    }

    public function projects()
    {
        return $this->owner->projects()->public();
    }

    public function activeProjects()
    {
        return $this->owner->activeProjects();
    }

    public function uploads()
    {
        return $this->hasMany('\MotionArray\Models\PortfolioUpload');
    }

    public function scopePublished($query)
    {
        $query->whereNotNull('last_published_at');

        return $query;
    }

    /*
	|--------------------------------------------------------------------------
	| Repo functions
	|--------------------------------------------------------------------------
	*/
    public function hasBeenSaved()
    {
        return !!$this->last_saved_at;
    }

    public function hasBeenPublished()
    {
        return !!$this->last_published_at;
    }

    public function canEdit()
    {
        $url = \Request::url();

        $editMode = str_contains($url, 'edit');

        return $this->isAuthOwner() && !Agent::isMobile() && $editMode;
    }

    public function isAuthOwner()
    {
        return $this->isOwner(Auth::user());
    }

    public function isOwner(User $user = null)
    {
        if ($user) {
            return $this->site->user_id == $user->id;
        }
    }

    public function getUrlTo($url)
    {
        if (starts_with($url, '/')) {
            $url = preg_replace('#^/#', '', $url);
        }

        if ($this->isAuthOwner()) {
            $base = '/account/portfolio/edit/';
        } else {
            $base = '/';
        }

        return $base . $url;
    }

    /*
	|--------------------------------------------------------------------------
	| foreign function
	|--------------------------------------------------------------------------
	*/

    // todo: remove from here
    public function getFontFamily($font)
    {
        $res = [];
        $font_family = [
            'Open Sans' => 'Open Sans',
            'Roboto' => 'Roboto',
            'Rubik' => 'Rubik',
            'Open Sans' => 'Open Sans',
            'Teko' => 'Teko',
            'Merriweather' => 'Merriweather',
            'Zilla Slab Highlight' => 'Zilla Slab Highlight',
            'Patua One' => 'Patua One',
            'Prata' => 'Prata',
            'Oswald' => 'Oswald',
            'Satisfy' => 'Satisfy',
            'Shrikhand' => 'Shrikhand',
            'Abril Fatface' => 'Abril Fatface',
            'Great Vibes' => 'Great Vibes',
            'Homemade Apple' => 'Homemade Apple',
            'Leckerli One' => 'Leckerli One',
            'Croissant One' => 'Croissant One',
        ];

        if (isset($font_family[$font])) {
            $res[$font] = $font_family[$font];

            foreach ($font_family as $key => $item_font) {
                if ($key != $font) {
                    $res[$key] = $item_font;
                }
            }

            return $res;
        }

        return $font_family;
    }
}


