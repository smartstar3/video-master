<?php namespace MotionArray\Models;

class ProjectCommentAuthor extends BaseModel
{
    protected $guarded = [];

    protected $appends = [];

    public static $rules = [];

    public function comment()
    {
        return $this->hasMany('MotionArray\Models\ProjectComment');
    }

    public function authorNotifications()
    {
        return $this->hasMany('MotionArray\Models\ProjectAuthorNotification');
    }

    /*public function getThumbnailAttribute()
    {
        $email = trim($this->email);
        $email = strtolower($email);

        $url = 'https://www.gravatar.com/avatar/' . md5($email) . '.jpg?s=60&d=404';

        return $url;
    }*/
}
