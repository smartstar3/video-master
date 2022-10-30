<?php

namespace MotionArray\Mailers;

use MotionArray\Models\Submission;
use MotionArray\Models\SubmissionNote;
use Request;

class SubmissionMailer extends Mailer
{
    public function __construct()
    {
        $this->from = [
            'email' => "content@motionarray.com", 'name' => "Motion Array"
        ];
    }

    public function submissionReceived(Submission $submission)
    {
        /*
         * Email variables
         */
        $view = 'admin.emails.submission.received';
        $data = [
            'submission' => $submission
        ];
        $subject = 'Your submission has been received!';

        /*
         * Send mail
         */

        return $this->sendTo($submission->seller->email, $subject, $view, $data);
    }

    public function submissionApproved(Submission $submission, SubmissionNote $note)
    {
        $appUrl = config('app.url');
        $productUrl = $appUrl . '/' . $submission->product->category->slug . '/' . $submission->product->slug;

        $data = [
            'submission' => $submission,
            'feedback' => $note->body,
            'url' => $productUrl,
        ];

        /*
         * Email variables
         */
        if ($submission->product->is_kick_ass) {
            $view = 'admin.emails.submission.kick-ass';
            $data['url'] = $appUrl . '/browse';
        } else {
            $view = 'admin.emails.submission.approved';
        }

        $subject = 'Your submission was approved!';

        /*
         * Send mail
         */

        return $this->sendTo($submission->seller->email, $subject, $view, $data);
    }

    public function submissionNeedsWork(Submission $submission, SubmissionNote $note)
    {
        /*
         * Email variables
         */
        $view = 'admin.emails.submission.needs-work';
        $data = [
            'submission' => $submission,
            'feedback' => $note->body,
        ];
        $subject = 'Your submission needs work';

        /*
         * Send mail
         */

        return $this->sendTo($submission->seller->email, $subject, $view, $data);
    }

    public function submissionRejected(Submission $submission, $feedback)
    {
        /*
         * Email variables
         */

        $view = 'admin.emails.submission.rejected';
        $data = [
            'submission' => $submission,
            'feedback' => $feedback
        ];
        $subject = 'Your submission was rejected';

        /*
         * Send mail
         */

        return $this->sendTo($submission->seller->email, $subject, $view, $data);
    }
}
