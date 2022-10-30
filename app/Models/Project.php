<?php namespace MotionArray\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use MotionArray\Helpers\Helpers;
use MotionArray\Jobs\UpdateUserOnIntercom;
use MotionArray\Models\Traits\Amazon;
use MotionArray\Traits\PresentableTrait;
use MotionArray\Models\Traits\Uploadable;
use Config;

class Project extends Uploadable
{
    use SoftDeletes;

    use PresentableTrait;

    use Amazon;

    use Sluggable;

    public static $rules = [
        'slug' => 'unique:projects,slug,{:id}',
    ];

    protected $presenter = 'MotionArray\Presenters\ProjectPresenter';

    // todo: Remove aws and meta, add only as needed (Upload requests)
    protected $appends = ['plain_name', 'plain_description',
        'upload_type', 'aws', 'meta', 'url', 'reviewUrl', 'portfolioUrl', 'previews', 'placeholder_id', 'uses_password'];

    protected $fillable = ['name', 'description'];

    protected $dates = ['deleted_at'];

    public $previewPrefix = 'preview-';

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => ['name']
            ]
        ];
    }

    public static function previewsBucket()
    {
        return Config::get('aws.portfolio_previews_bucket');
    }

    public static function bucketUrl()
    {
        return Config::get('aws.portfolio_previews_s3');
    }

    public static function cdnUrl()
    {
        return Config::get("aws.portfolio_previews_cdn");
    }

    public static function imgixUrl()
    {
        return Config::get("imgix.portfolio_url");
    }

    /*
	|--------------------------------------------------------------------------
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getReviewLogoAttribute()
    {
        $settings = $this->reviewSettings;

        if ($settings && $settings->logo) {
            return $settings->logo;
        }

        $review = $this->getReview();

        if ($review) {
            return $review->logo;
        }
    }

    public function getUploadTypeAttribute()
    {
        return 'project';
    }

    public function getUsesPasswordAttribute()
    {
        return !!$this->password;
    }

    /**
     * Gets portfolio url or default to Review Url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->is_public) {
            return $this->portfolioUrl;
        } elseif ($this->has_review) {
            return $this->reviewUrl;
        }
    }

    /**
     * Returns the time elapsed since the last update
     * on version or comments
     *
     * @return string
     */
    public function getLastUpdateAttribute()
    {
        $previewUploads = $this->previewUploads()->get();

        $previewUploadIds = $previewUploads->pluck('id');

        $comments = ProjectComment::whereIn('preview_upload_id', $previewUploadIds)->get();

        $dates = array_merge([$this->created_at], $previewUploadIds->pluck('updated_at')->toArray(), $comments->pluck('updated_at')->toArray());

        $lastUpdate = max($dates);

        return Helpers::timeElapsedString($lastUpdate, true);
    }

    /**
     * Returns url for project
     *
     * @return string
     */
    public function getPortfolioUrlAttribute()
    {
        $user = $this->user;

        if (!$user || !$user->site || !$user->site->portfolio) {
            return null;
        }

        return $user->site->getPortfolioUrl('project/' . $this->slug);
    }

    public function getReviewUrlAttribute()
    {
        if ($this->has_review) {
            $user = $this->user;

            if(!$user || !$user->site) {
                return null;
            }

            return $user->site->getReviewUrl('review/' . $this->permalink);
        }
    }

    /**
     * Append queue_position attribute
     *
     * @return array
     */
    public function getMetaAttribute()
    {
        // Prepare an array of tags
        $tags = [];

        foreach ($this->tags as $tag) {
            array_push($tags, $tag->name);
        }

        return [
            'owner' => $this->owner()->first(),
            'portfolio' => $this->getPortfolio(),
            'placeholder_image' => $this->getPlaceholder(),
            'tags' => $tags,
            'is_video' => $this->isVideo(),
            'is_audio' => $this->isAudio()
        ];
    }

    public function getPlainNameAttribute()
    {
        return html_entity_decode(strip_tags($this->attributes['name']));
    }

    public function getPlainDescriptionAttribute()
    {
        return html_entity_decode(strip_tags($this->attributes['description']));
    }

    /*
	|--------------------------------------------------------------------------
	| Scopes
	|--------------------------------------------------------------------------
	*/
    public function scopePublic($query)
    {
        $query->where('is_public', '=', 1);

        return $query;
    }

    public function scopeActive($query)
    {
        $query->whereHas('activePreview', function ($q) {
            $q->whereIn('encoding_status_id', [3, 8]);
        })
            ->where('product_status_id', '=', 1);

        return $query;
    }

    public function scopeWithReview($query)
    {
        $query->where('has_review', '=', 1);

        return $query;
    }

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function owner()
    {
        return $this->user();
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('MotionArray\Models\User');
    }

    public function page()
    {
        return $this->hasOne('MotionArray\Models\PortfolioPage');
    }

    public function tags()
    {
        return $this->belongsToMany('MotionArray\Models\Tag', 'project_tags');
    }

    public function reviewSettings()
    {
        return $this->hasOne('MotionArray\Models\ProjectReviewSetting');
    }


    public function projectNotifications()
    {
        return $this->hasMany('MotionArray\Models\ProjectAuthorNotification');
    }

    public function getPortfolio()
    {
        if(!$this->user || !$this->user->site) {
            return null;
        }
        return $this->user->site->portfolio;
    }

    public function getReview()
    {
        if(!$this->user || !$this->user->site) {
            return null;
        }
        return $this->user->site->review;
    }

    /*
	|--------------------------------------------------------------------------
	| Repo Functions
	|--------------------------------------------------------------------------
	*/
    public function newerProject($cycle = false)
    {
        $user = $this->user;

        if(!$user) {
            return null;
        }

        // Get the first project created after current project
        $nextProject = $user->activeProjects()
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'ASC')
            ->first();

        if (!$nextProject && $cycle) {
            // Get the firt project ever created
            $firstProject = $user->activeProjects()->orderBy('created_at', 'ASC')->first();

            if ($firstProject && $firstProject->id != $this->id) {
                return $firstProject;
            }
        }

        return $nextProject;
    }

    public function olderProject($cycle = false)
    {
        $user = $this->user;

        if(!$user) {
            return null;
        }

        // Get the last project created before current project
        $previousProject = $user->activeProjects()
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$previousProject && $cycle) {
            // Get the last project ever created
            $lastProject = $user->activeProjects()->orderBy('created_at', 'DESC')->first();

            if ($lastProject && $lastProject->id != $this->id) {
                return $lastProject;
            }
        }

        return $previousProject;
    }

    public function isUnlocked()
    {
        if (!$this->reviewSettings || !$this->reviewSettings->password) {
            return true;
        }

        $unlockedProjects = session()->get('unlockedProjects');

        if ($unlockedProjects) {
            return in_array($this->id, $unlockedProjects);
        }
    }

    public function unlock($password)
    {
        if (Hash::check($password, $this->reviewSettings->password)) {
            $unlockedProjects = session()->get('unlockedProjects');

            if (!$unlockedProjects) {
                $unlockedProjects = [];
            }

            $unlockedProjects[] = $this->id;

            session()->put('unlockedProjects', $unlockedProjects);

            return true;
        }
    }

    public function getDiskUsage($originalOnly = true)
    {
        $bytes = 0;

        $versions = $this->previewUploads;

        foreach ($versions as $version) {
            $files = $version->files();

            if ($originalOnly) {
                $files->where('label', '=', PreviewFile::ORIGINAL);
            }

            $bytes += $files->sum('file_size_bytes');
        }

        return $bytes;
    }
}

Project::creating(function ($project) {
    $project->permalink = md5(uniqid(rand(), true));
});

// Update Intercom data
Project::saved(function ($project) {
    $user = $project->user;
    if($user)
        Bus::dispatch(new UpdateUserOnIntercom($user));
});