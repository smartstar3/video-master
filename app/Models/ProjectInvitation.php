<?php namespace MotionArray\Models;

use Illuminate\Support\Facades\Hash;

class ProjectInvitation extends BaseModel
{
    protected $guarded = [];

    protected $appends = [];

    public static $rules = [];

    /*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
    public function project()
    {
        return $this->belongsTo('MotionArray\Models\Project');
    }

    /*
	|--------------------------------------------------------------------------
	| Accesors & Mutators
	|--------------------------------------------------------------------------
	*/
    public function getUrlAttribute()
    {
        $userSite = $this->project->user->site;

        if ($userSite) {
            return $userSite->getReviewUrl('review/invitation/' . $this->token);
        }
    }
}

ProjectInvitation::creating(function ($invitation) {
    $token = Hash::make($invitation->email);

    $token = str_slug($token);

    $invitation->token = $token;
});