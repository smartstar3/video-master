<?php

namespace MotionArray\Events;

use Illuminate\Queue\SerializesModels;
use MotionArray\Models\Submission;

class SubmissionApproved extends Event
{
    use SerializesModels;

    public $submission;

    /**
     * Create a new event instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }
}
