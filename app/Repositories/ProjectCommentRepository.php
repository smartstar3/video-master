<?php namespace MotionArray\Repositories;

use MotionArray\Models\Project;
use MotionArray\Models\ProjectComment;
use Motionarray\Models\PreviewUpload;
use MotionArray\Repositories\ProjectCommentAuthorRepository;
use MotionArray\Repositories\ProjectAuthorNotificationRepository;

class ProjectCommentRepository
{
    public $errors = [];

    protected $author;

    protected $authorNotification;

    public function __construct(ProjectCommentAuthorRepository $author,
                                ProjectAuthorNotificationRepository $authorNotification)
    {
        $this->author = $author;
        $this->authorNotification = $authorNotification;
    }

    public function findByProjectVersion($projectId, $version)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return [];
        }

        $previewUpload = $project->getVersionNumber($version);

        if (!$previewUpload) {
            return [];
        }

        $comments = $previewUpload
            ->comments()
            ->with('author')
            ->orderBy('time', 'ASC')
            ->get();

        $comments = $comments->toArray();

        $getChildrenRecursive = function ($comments, $parentId) use (&$getChildrenRecursive) {
            $children = array_filter($comments, function ($comment) use ($parentId) {
                return $comment['parent_id'] == $parentId;
            });

            foreach ($children as &$child) {
                $child['replies'] = $getChildrenRecursive($comments, $child['id']);
            }

            return array_values($children);
        };

        $nestedComments = $getChildrenRecursive($comments, 0);

        return $nestedComments;
    }

    public function create($projectId, $data)
    {
        $author = $this->author->getAuthorSession();

        if (!$author && isset($data['author'])) {
            $author = $this->author->getOrCreate($data['author']);
        }

        if (!$author) {
            return;
        }

        $notificationData = [
            'project_id' => $projectId,
            'author_id' => $author->id
        ];

        $authorNotification = $this->authorNotification->getOrCreate($notificationData);

        if (array_key_exists('email_notifications', $data['author'])) {
            $authorNotification->email_notifications = (bool) $data['author']['email_notifications'];
            $authorNotification->save();
        }

        $comment = new ProjectComment(array_except($data, ['author']));

        $comment->author()->associate($author);

        $comment->save();

        return $comment;
    }

    public function update($commentId, $data)
    {
        $comment = ProjectComment::find($commentId);

        $comment->update($data);

        return $comment;
    }

    public function delete($commentId)
    {
        return ProjectComment::find($commentId)->delete();
    }
}
