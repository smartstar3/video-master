<?php

namespace MotionArray\Mailers;

use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Project;
use MotionArray\Models\ProjectComment;
use MotionArray\Models\ProjectCommentAuthor;
use MotionArray\Models\ProjectInvitation;
use Config;

class ReviewMailer extends Mailer
{
    public function __construct()
    {
        $this->from = [
            'email' => "noreply@post.pro", 'name' => "Video Review"
        ];
    }

    public function invitation(ProjectInvitation $invitation, PreviewUpload $previewUpload, $message = null, $author = null)
    {
        $project = $previewUpload->uploadable;

        $data = [
            'project' => $project,
            'url' => $invitation->url . '/version/' . $previewUpload->version,
            'body' => $message,
            'author' => $author
        ];

        $view = 'post.emails.review.invite';

        $subject = 'Invitation to the "' . $project->name . '"  review project';

        return $this->sendTo($invitation->email, $subject, $view, $data);
    }

    public function projectShared(Array $recipients = [], PreviewUpload $previewUpload, $message = null, $author = null)
    {
        $project = $previewUpload->uploadable;

        $url = $previewUpload->uploadable->reviewUrl . '/version/' . $previewUpload->version;

        $data = [
            'project' => $project,
            'url' => $url,
            'body' => $message,
            'author' => $author
        ];

        $view = 'post.emails.review.share';

        $subject = $project->user->firstname . ' Invited you to collaborate on the "' . $project->name . '"  review project';

        return $this->sendTo($recipients, $subject, $view, $data);
    }

    public function userComment(PreviewUpload $previewUpload, ProjectComment $comment)
    {
        $project = $previewUpload->uploadable;

        $url = $previewUpload->uploadable->reviewUrl . '/version/' . $previewUpload->version;

        $data = [
            'project' => $project,
            'url' => $url,
            'comment' => $comment
        ];

        $view = 'post.emails.review.user-comment';

        $subject = $comment->author->name . ' commented on your "' . $project->name . '"  review project';

        return $this->sendTo($project->user->email, $subject, $view, $data);
    }

    public function commentReply(ProjectComment $comment, ProjectComment $parentComment, $authorNotificationId)
    {
        $project = $comment->previewUpload->uploadable;

        $site = $project->user()->first()->site;

        $key = sha1($authorNotificationId.Config::get('reviews.unsubscribe-secure'));

        $unsubscribeUrl = $project->reviewUrl.'/unsubscribe/'.$authorNotificationId.'/'.$key;

        $url = $project->reviewUrl . '/version/' . $comment->previewUpload->version;

        $data = [
            'project' => $project,
            'url' => $url,
            'comment' => $comment,
            'parentComment' => $parentComment,
            'unsubscribeUrl' => $unsubscribeUrl
        ];

        $view = 'post.emails.review.comment-reply';

        $subject = $comment->author->name . ' replied to your comment on "' . $project->name . '"';

        return $this->sendTo($parentComment->author->email, $subject, $view, $data);
    }

    public function approveRevision(PreviewUpload $previewUpload, $author = null)
    {
        $project = $previewUpload->uploadable;
        $url = $previewUpload->uploadable->reviewUrl . '/version/' . $previewUpload->version;
        $gif = 'http://media.giphy.com/media/13zeE9qQNC5IKk/giphy.gif';

        $data = [
            'project' => $project,
            'url' => $url,
            'gif' => $gif,
            'author' => $author
        ];
        $view = 'post.emails.review.approve-revision';

        $subject = 'Your review project "' . $project->name . '" has been approved';

        return $this->sendTo($project->user->email, $subject, $view, $data);
    }

    public function commentDelete(ProjectComment $comment)
    {
        $project = $comment->previewUpload->uploadable;

        $url = $project->reviewUrl . '/version/' . $comment->previewUpload->version;

        $data = [
            'project' => $project,
            'url' => $url,
            'comment' => $comment
        ];

        $view = 'post.emails.review.comment-delete';

        $subject = $comment->author->name . ' deleted a comment on the "' . $project->name . '" review project';

        return $this->sendTo($project->user->email, $subject, $view, $data);
    }

    public function commentUpdate(ProjectComment $comment)
    {
        $project = $comment->previewUpload->uploadable;

        $url = $project->reviewUrl . '/version/' . $comment->previewUpload->version;

        $data = [
            'project' => $project,
            'url' => $url,
            'comment' => $comment
        ];

        $view = 'post.emails.review.comment-update';

        $subject = $comment->author->name . ' updated a comment on the "' . $project->name . '" review project';

        return $this->sendTo($project->user->email, $subject, $view, $data);
    }
}
