<?php namespace MotionArray\Http\Controllers\Post;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\ProjectInvitationRepository;
use MotionArray\Repositories\ProjectCommentAuthorRepository;
use MotionArray\Repositories\ProjectAuthorNotificationRepository;
use MotionArray\Repositories\ReviewRepository;
use Redirect;
use Response;
use View;
use Flash;
use Auth;

class ReviewsController extends Controller
{
    protected $project;

    protected $productRepo;

    protected $projectInvitation;

    protected $commentAuthor;

    protected $authorNotification;

    protected $review;

    public function __construct(
        ProjectRepository $project,
        ProductRepository $productRepository,
        ProjectInvitationRepository $projectInvitation,
        ProjectCommentAuthorRepository $projectCommentAuthor,
        ProjectAuthorNotificationRepository $authorNotification,
        ReviewRepository $review
    )
    {
        $this->productRepo = $productRepository;

        $this->project = $project;

        $this->projectInvitation = $projectInvitation;

        $this->commentAuthor = $projectCommentAuthor;

        $this->authorNotification = $authorNotification;

        $this->review = $review;
    }

    public function project($permalink, $version = null)
    {
        $projectData = $this->getProjectData($permalink, $version);

        if(!$projectData) {
            // TODO: redirect to 404 page of review
            return Redirect::to('/');
        }
        else if ($projectData['project']->isUnlocked()) {
            return View::make("site.review.project", $projectData);
        } else {
            return View::make("site.review.auth", $projectData);
        }
    }

    public function unlock($permalink)
    {
        $password = Request::get('password');

        $project = $this->project->findByPermalink($permalink);

        $success = $project->unlock($password);

        $redirect = Redirect::to('/review/' . $project->permalink);

        if (!$success) {
            Flash::danger('Invalid password');
        }

        return $redirect;
    }

    public function invitation($token, $version)
    {
        $invitation = $this->projectInvitation->findByToken($token);
        $invitation->used = true;
        $invitation->save();

        $project = $invitation->project;

        if($project) {
            $permalink = $invitation->project->permalink;

            return Redirect::action('Post\ReviewsController@project', ['permalink' => $permalink, 'version' => $version]);
        }

        // TODO: redirect to 404 page of review
        return Redirect::to('/');
    }

    public function checkNotifications($projectId)
    {
        $user = Auth::user();

        $authorData = [
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email
        ];

        $author = $this->commentAuthor->getOrCreate($authorData);

        $emailNotifications = $this->authorNotification->isActive($projectId, $author->id);

        return Response::json(['email_notifications' => $emailNotifications], 200);
    }

    public function emailNotifications($projectId)
    {
        $user = Auth::user();

        $emailNotification = Request::get('email_notifications');

        $authorData = [
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email
        ];

        $author = $this->commentAuthor->getOrCreate($authorData);

        $notificationData = [
            'project_id' => $projectId,
            'author_id' => $author->id
        ];

        if ($this->authorNotification->update($notificationData, (bool)$emailNotification)) {
            $this->review->updateReviewNotification($projectId, $emailNotification);
        }

        return Response::json(['success' => true], 200);
    }

    public function unsubscribe($permalink, $notificationId, $key)
    {
        $unsubscribed = false;

        if ($this->authorNotification->unsubscribe($notificationId, $key)) {
            $unsubscribed = true;
        }

        $projectData = $this->getProjectData($permalink);

        $projectData['unsubscribed'] = $unsubscribed;

        $projectData['resubscribe_link'] = $projectData['project']->reviewUrl . "/resubscribe/$notificationId/$key";

        return View::make("site.review.unsubscribe", $projectData);
    }

    public function resubscribe($permalink, $notificationId, $key)
    {
        $resubscribed = false;

        if ($this->authorNotification->resubscribe($notificationId, $key)) {
            $resubscribed = true;
        }

        $projectData = $this->getProjectData($permalink);

        $projectData['resubscribed'] = $resubscribed;

        $projectData['unsubscribe_link'] = $projectData['project']->reviewUrl . "/unsubscribe/$notificationId/$key";

        return View::make("site.review.unsubscribe", $projectData);
    }

    private function getProjectData($permalink, $version = null)
    {
        $site = Request::get('current_site');

        $project = $this->project->findByPermalink($permalink);

        if (!$site || !$project || !$project->previewUploads->count()) {
            return false;
        }

        if (!$version) {
            $preview = $project->activePreview;

            $version = $preview->version;
        } else {
            $preview = $project->getVersionNumber($version);
        }

        return compact('project', 'site', 'preview', 'version');
    }
}
