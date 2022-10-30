<?php namespace MotionArray\Repositories;

use MotionArray\Repositories\UserSiteRepository;
use MotionArray\Models\ProjectAuthorNotification;
use MotionArray\Models\Project;
use MotionArray\Models\Review;
use Config;

class ProjectAuthorNotificationRepository
{
    protected $userSite;

    public function __construct(UserSiteRepository $userSite)
    {
        $this->userSite = $userSite;
    }

    public function getByIds($projectId, $authorId)
    {
        return ProjectAuthorNotification::where([
            'project_id' => $projectId,
            'author_id' => $authorId
        ])->first();
    }

    public function getOrCreate(array $notificationData)
    {
        return ProjectAuthorNotification::firstOrCreate($notificationData);
    }

    public function update($notificationData, $emailNotification)
    {
        $authorNotifications = $this->getOrCreate($notificationData);

        $authorNotifications->email_notifications = (bool)$emailNotification;

        $authorNotifications->save();

        return true;
    }

    public function isActive($projectId, $authorId)
    {
        $authorNotification = $this->getByIds($projectId, $authorId);

        if ($authorNotification) {
            return $authorNotification->email_notifications;
        } else {
            $project = Project::find($projectId);

            if (!$project) {
                return null;
            }

            $userSite = $this->userSite->findByUser($project->user);

            $emailSetting = $userSite->review->email_notification ?? null;

            $emailNotification = true;

            if($emailSetting == Review::EMAIL_NOTIFICATION_NEVER) {
                $emailNotification = false;
            }

            $notificationData = [
                'project_id' => $projectId,
                'author_id' => $authorId
            ];

            $notification = $this->getOrCreate($notificationData);

            $notification->email_notifications = $emailNotification;

            $notification->save();

            return $emailNotification;
        }
    }

    public function setUnsubscribe($projectAuthorNotificationId, $key, $val)
    {
        $cmpKey = sha1($projectAuthorNotificationId . Config::get('reviews.unsubscribe-secure'));

        if ($cmpKey == $key) {
            $authorNotification = ProjectAuthorNotification::find($projectAuthorNotificationId);

            if ($authorNotification) {

                $authorNotification->email_notifications = $val;
                $authorNotification->save();

                return true;
            }
        }

        return false;
    }

    public function unsubscribe($projectAuthorNotificationId, $key)
    {
        return $this->setUnsubscribe($projectAuthorNotificationId, $key, false);
    }

    public function resubscribe($projectAuthorNotificationId, $key)
    {
        return $this->setUnsubscribe($projectAuthorNotificationId, $key, true);
    }
}
