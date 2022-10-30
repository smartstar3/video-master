<?php

namespace MotionArray\Models;

class SubmissionStatus extends BaseModel
{
    /**
     * SubmissionStatus has many Submissions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submissions()
    {
        return $this->hasMany('MotionArray\Models\Submission');
    }


    /**
     * SubmissionStatus has many SubmissionNotes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('MotionArray\Models\SubmissionNote');
    }
}
