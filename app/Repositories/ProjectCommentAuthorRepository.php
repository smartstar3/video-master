<?php namespace MotionArray\Repositories;

use MotionArray\Models\Project;
use MotionArray\Models\ProjectCommentAuthor;
use Session;
use Config;

class ProjectCommentAuthorRepository
{
    protected $sessionKey = 'ProjectCommentAuthor';

    public function findByProject($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return [];
        }

        $previewUploads = $project->previewUploads()->get();

        $authorIds = array_flatten($previewUploads->map(function ($previewUpload) {
            return $previewUpload->comments()
                ->pluck('author_id');
        })->toArray());

        return ProjectCommentAuthor::whereIn('id', $authorIds)->get();
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

        $authorIds = $previewUpload
            ->comments()
            ->pluck('author_id');

        return ProjectCommentAuthor::whereIn('id', $authorIds)->get();
    }

    public function getOrCreate(Array $authorData, $isFromBrowser = true)
    {
        $author = $this->getAuthorSession();

        if ((!$author && count($authorData) > 0) || (!$isFromBrowser && count($authorData) > 0) ) {

            $author = ProjectCommentAuthor::firstOrCreate(array_except($authorData, ['email_notifications', 'thumbnail']));

            if($isFromBrowser) {
                $this->setAuthorSession($author);
            }
        }

        return $author;
    }

    public function getAuthorSession()
    {
        return Session::get($this->sessionKey);
    }

    public function setAuthorSession($author)
    {
        Session::put($this->sessionKey, $author);
    }

    public function getAuthorDataFromSession($authorData = null)
    {
        $authorData = $authorData ? $authorData : [];

        $author = $this->getOrCreate($authorData);

        if ($author) {
            $author = json_decode(json_encode($author), true);

            $author = array_only($author, ['email', 'name']);
        }

        return $author;
    }
}
