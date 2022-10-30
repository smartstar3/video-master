<?php

namespace MotionArray\Models;


class ProjectAuthorNotification extends BaseModel
{
    public function author()
    {
        return $this->belongsTo('MotionArray\Models\ProjectCommentAuthor');
    }

    public function project()
    {
        return $this->belongsTo('MotionArray\Models\Project');
    }
}
