<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Mailers\ReviewMailer;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\Project;
use MotionArray\Models\ProjectComment;
use MotionArray\Models\Review;
use MotionArray\Models\User;
use MotionArray\Repositories\UserSiteRepository;
use MotionArray\Repositories\UserSiteAppRepository;
use MotionArray\Repositories\ProjectAuthorNotificationRepository;
use MotionArray\Repositories\ProjectCommentAuthorRepository;
use MotionArray\Services\Slim;
use AWS;

/**
 * Class ReviewRepository
 *
 * @package MotionArray\Repositories\Review
 */
class ReviewRepository extends UserSiteAppRepository
{
    /**
     * @var UserSiteRepository
     */
    private $userSite;

    private $reviewMailer;

    private $authorNotification;

    private $commentAuthor;

    /**
     * ReviewRepository constructor.
     *
     * @param Review $review
     * @param UserSiteRepository $userSite
     */
    public function __construct(Review $review,
                                UserSiteRepository $userSite,
                                ReviewMailer $reviewMailer,
                                ProjectAuthorNotificationRepository $authorNotification,
                                ProjectCommentAuthorRepository $projectCommentAuthor)
    {
        $this->model = $review;
        $this->userSite = $userSite;
        $this->reviewMailer = $reviewMailer;
        $this->authorNotification = $authorNotification;
        $this->commentAuthor = $projectCommentAuthor;
    }


    /**
     * Create Review
     *
     * @param User $user
     * @return Review
     */
    public function createReview(User $user)
    {
        $userSite = $this->userSite->findByUser($user);

        return $this->model->create(['user_site_id' => $userSite->id]);
    }

    /**
     * Update Review Settings
     *
     * @param Review $review
     * @param array $data
     * @return Review
     */
    public function updateSettings(Review $review, Array $data)
    {
        $settings = $review->settings ? $review->settings : [];


        foreach ($data as $field) {
            if ($field['handle'] == 'image') {
                $url = $this->replaceImage($review, $field['name'], $field['value']);

                $settings[$field['name']] = $url;
            }
        }

        $review->settings = $settings;

        $review->save();

        return $review;
    }

    /**
     * Replace Review Image
     *
     * @param Review $review
     * @param $key
     * @param $value
     * @return mixed
     */
    public function replaceImage(Review $review, $key, $value)
    {
        $this->deleteImage($review, $key);

        return $this->uploadImage($review, $key, $value);
    }

    /**
     * Upload Review Image
     *
     * @param Review $review
     * @param $key
     * @param $value
     * @return mixed
     */
    public function uploadImage(Review $review, $key, $value)
    {
        $filename = 'r' . $review->id . '-' . $key . '-' . time();

        $bucket = Project::previewsBucket();

        $url = Slim::uploadToAmazon($bucket, $filename, $value);

        return $url;
    }


    /**
     * Delete Review Image
     *
     * @param Review $review
     * @param $key
     */
    public function deleteImage(Review $review, $key)
    {
        $file = ($review->settings && array_key_exists($key, $review->settings)) ? $review->settings[$key] : null;

        if (!$file) {
            return;
        }

        $s3 = AWS::get('s3');

        $bucket = Project::previewsBucket();

        $bucketUrl = Project::bucketUrl();

        if ($file) {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => str_replace($bucketUrl, '', $file)
            ]);
        }
    }

    public function updateProjectsNotification(Review $review, $email_notification)
    {
        $projects = $review->reviewProjects()->get();

        foreach ($projects as $project) {
            $authorData = [
                'name' => $project->user->firstname . ' ' . $project->user->lastname,
                'email' => $project->user->email
            ];

            $author = $this->commentAuthor->getOrCreate($authorData);

            $notificationData = [
                'project_id' => $project->id,
                'author_id' => $author->id
            ];

            if ($email_notification == Review::EMAIL_NOTIFICATION_INSTANT) {

                $this->authorNotification->update($notificationData, true);

            } else if ($email_notification == Review::EMAIL_NOTIFICATION_NEVER) {

                $this->authorNotification->update($notificationData, false);

            }
        }
    }

    public function updateReviewNotification($projectId, $projectEmailNotification)
    {
        if ($projectEmailNotification) {
            $project = Project::find($projectId);

            $userSite = $this->userSite->findByUser($project->user);

            $review = $userSite->review;

            $emailSetting = $review ? $review->email_notification : null;

            if ($emailSetting == Review::EMAIL_NOTIFICATION_NEVER) {
                $settings = $review->settings ? $review->settings : [];

                $settings['email_notification'] = Review::EMAIL_NOTIFICATION_30_MINS;

                $review->settings = $settings;
                $review->save();
            }
        }
    }

    public function isActiveEmailNotification(PreviewUpload $previewUpload, ProjectComment $comment)
    {
        //Get the users email setting
        $project = $previewUpload->uploadable;

        if ($project) {

            // Check if this project is set notification
            $authorData = [
                'name' => $project->user->firstname . ' ' . $project->user->lastname,
                'email' => $project->user->email
            ];

            $author = $this->commentAuthor->getOrCreate($authorData, false);

            if (!$this->authorNotification->isActive($project->id, $author->id)) {
                return false;
            }

            $userSite = $this->userSite->findByUser($project->user);

            $emailSetting = $userSite->review ? $userSite->review->email_notification : null;

            $email_cutoff_time = Carbon::now()->subMinutes(30);

            if ($emailSetting == Review::EMAIL_NOTIFICATION_NEVER) {
                return false;
            } elseif ($emailSetting == Review::EMAIL_NOTIFICATION_30_MINS || $emailSetting == null) {

                //Get the last comment made on this project *for this user*
                $latestComment = $previewUpload->comments()
                    ->where('id', '!=', $comment->id)
                    ->where('author_id', '=', $comment->author->id)
                    ->latest()
                    ->first();

                $latestUpdatedComment = $previewUpload->comments()
                    ->where('id', '!=', $comment->id)
                    ->where('author_id', '=', $comment->author->id)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                $latestDeletedComment = $previewUpload->comments()
                    ->where('id', '!=', $comment->id)
                    ->where('author_id', '=', $comment->author->id)
                    ->onlyTrashed()
                    ->orderBy('deleted_at', 'desc')
                    ->first();

                if ((!$latestComment || $latestComment->updated_at->lt($email_cutoff_time))
                    && (!$latestUpdatedComment || $latestUpdatedComment->updated_at->lt($email_cutoff_time))
                    && (!$latestDeletedComment || $latestDeletedComment->deleted_at->lt($email_cutoff_time))) {
                    return true;
                } else {
                    return false;
                }

            } elseif ($emailSetting == Review::EMAIL_NOTIFICATION_INSTANT) {
                return true;
            }
        }

        return false;
    }

    public function sendCommentReplyNotification(ProjectComment $comment)
    {
        //Get the parent comment
        $parentComment = $comment->parent;

        $author = $parentComment->author;

        $previewUpload = $parentComment->previewUpload;

        $project = $previewUpload->uploadable;

        // check if author of parent comment is project's owner.
        if ($project->user->email == $author->email
            && $project->user->firstname . ' ' . $project->user->lastname == $author->name) {

            if (!$this->isActiveEmailNotification($previewUpload, $comment)) {
                return false;
            }

        }

        //Check if the parent author has enabled notifications and they aren't replying to themselves
        if ($this->authorNotification->isActive($project->id, $author->id) && $parentComment->author->id != $comment->author->id) {

            $authorNotification = $this->authorNotification->getByIds($project->id, $author->id);

            return $this->reviewMailer->commentReply($comment, $parentComment, $authorNotification->id);
        }

        return false;
    }

    public function sendUserCommentNotification($previewUpload, $comment)
    {
        if (!$this->isActiveEmailNotification($previewUpload, $comment)) {
            return false;
        }

        if (!$comment->belongsToProjectOwner()) {
            return $this->reviewMailer->userComment($previewUpload, $comment);
        }
    }

    public function sendCommentDeleteNotification(ProjectComment $comment)
    {
        $previewUpload = $comment->previewUpload;

        if (!$this->isActiveEmailNotification($previewUpload, $comment)) {
            return false;
        }

        if (!$comment->belongsToProjectOwner()) {
            return $this->reviewMailer->commentDelete($comment);
        }
    }

    public function sendCommentUpdateNotification(ProjectComment $comment)
    {
        $previewUpload = $comment->previewUpload;

        if (!$this->isActiveEmailNotification($previewUpload, $comment)) {
            return false;
        }

        if (!$comment->belongsToProjectOwner()) {
            return $this->reviewMailer->commentUpdate($comment);
        }
    }
}
