<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Session;

class ProjectComment extends BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['token'];

    public static $rules = [
        'time' => 'required',
        'body' => 'required'
    ];

    public $appends = ['rank', 'isOwner'];

    public function previewUpload()
    {
        return $this->belongsTo('MotionArray\Models\PreviewUpload');
    }

    public function parent()
    {
        return $this->belongsTo('MotionArray\Models\ProjectComment');
    }

    public function children()
    {
        return $this->hasMany('MotionArray\Models\ProjectComment', 'parent_id');
    }

    public function author()
    {
        return $this->belongsTo('MotionArray\Models\ProjectCommentAuthor');
    }

    public function getIsOwnerAttribute()
    {
        if ($this->session_id) {
            return Session::getId() == $this->session_id;
        }

        return $this->getIsOwnerOld();
    }

    protected function getIsOwnerOld()
    {
        // Old comments
        $now = Carbon::now();

        if ($now->diffInHours($this->created_at) < 48) {
            $isOwner = Hash::check(Session::getId(), $this->token);

            if ($isOwner) {
                $this->session_id = Session::getId();

                $this->save();
            }

            return $isOwner;
        }
    }

    public function getRankAttribute()
    {
        $time = floor($this->time * 100);

        $time = str_pad($time, 6, "0", STR_PAD_LEFT);

        return $time;
    }

    public function belongsToProjectOwner()
    {
        $project = $this->previewUpload->uploadable;

        if ($project) {
            return $project->owner->email == $this->author->email;
        }

        return false;
    }
}

ProjectComment::creating(function ($comment) {
//    $comment->token = Hash::make(Session::getId());

    $comment->session_id = Session::getId();
});