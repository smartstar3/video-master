<?php

namespace MotionArray\Models;

class SubmissionNote extends BaseModel
{


    /**
     * SubmissionNote belongs to Submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo('MotionArray\Models\Submission');
    }


    /**
     * SubmissionNote belongs to SubmissionStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('MotionArray\Models\SubmissionStatus');
    }


    /**
     * SubmissionNote belongs to User (reviewer)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewer()
    {
        return $this->belongsTo('MotionArray\Models\User', 'reviewer_id', 'id');
    }

}
