<?php

namespace MotionArray\Models;

class SubmissionReviewer extends BaseModel
{
    protected $fillable = ['id', 'submission_id', 'reviewer_id'];

    /**
     * submissionReviewer belongs to submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo('MotionArray\Models\Submission');
    }
}
