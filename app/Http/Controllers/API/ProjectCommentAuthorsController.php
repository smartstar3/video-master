<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Mailers\ReviewMailer;
use MotionArray\Repositories\PreviewUploadRepository;
use MotionArray\Repositories\ProjectCommentAuthorRepository;
use MotionArray\Repositories\ProjectInvitationRepository;
use MotionArray\Repositories\ProjectRepository;
use Request;
use Response;
use Auth;

class ProjectCommentAuthorsController extends BaseController
{
    protected $commentAuthor;

    protected $reviewMailer;

    protected $previewUpload;

    protected $projectInvitation;

    protected $project;

    public function __construct(
        ProjectCommentAuthorRepository $commentAuthor,
        ReviewMailer $reviewMailer,
        PreviewUploadRepository $previewUpload,
        ProjectInvitationRepository $projectInvitation,
        ProjectRepository $project
    )
    {
        $this->commentAuthor = $commentAuthor;

        $this->reviewMailer = $reviewMailer;

        $this->previewUpload = $previewUpload;

        $this->projectInvitation = $projectInvitation;

        $this->project = $project;
    }

    public function index($projectId, $version)
    {
        $includeInvitations = Request::get('include-invitations');

        $authors = $this->commentAuthor->findByProject($projectId);

        $clientEmails = $authors->pluck('email');

        if ($includeInvitations) {
            if(Auth::check()) {
                $invitationEmails = $this->projectInvitation->invitationEmailsByUser(Auth::user()->id);
            } else {
                $invitationEmails = $this->projectInvitation->invitationEmailsByProject($projectId);
            }

            $clientEmails = $clientEmails->merge($invitationEmails)->unique();
        }

        $clientEmails = $clientEmails->map(function ($clientEmail) {
            return ['email' => $clientEmail];
        });

        $response = Response::json($clientEmails);

        if (Request::has('callback')) {
            return $response->setCallback(Request::get('callback'));
        }

        return $response;
    }

    public function notify($projectId, $version)
    {
        $recipients = Request::get('recipients', []);

        $extra_recipients = Request::get('extra_recipients', []);

        $message = Request::get('message');

        $author = Request::get('author');

        $author = $this->commentAuthor->getAuthorDataFromSession($author);

        $previewUpload = $this->previewUpload->findByVersion($projectId, $version);

        if ($extra_recipients) {
            $readInvitations = $this->projectInvitation->findReadInvitations($extra_recipients);

            if ($readInvitations->count()) {
                $readInvitationsEmails = $readInvitations->pluck('email')->toArray();

                $recipients = array_merge($recipients, $readInvitationsEmails);

                $extra_recipients = array_diff($extra_recipients, $readInvitationsEmails);
            }

            foreach ($extra_recipients as $email) {
                $validator = \Validator::make(
                    [
                        'email' => $email,
                    ],
                    [
                        'email' => 'email',
                    ]
                );

                if ($validator->fails()) {
                    Response::json([
                        'success' => false,
                        'error' => '"'.$email.'" is not a valid email address. Please use a valid email address.'
                    ]);
                }

                $invitation = $this->projectInvitation->create($email, $projectId);

                $this->reviewMailer->invitation($invitation, $previewUpload, $message, $author);
            }
        }

        if ($recipients) {
            $this->reviewMailer->projectShared($recipients, $previewUpload, $message, $author);
        }

        return Response::json(['success' => true]);
    }

    public function approveRevision($projectId, $version)
    {
        $this->previewUpload->approveRevision($projectId, $version);

        $previewUpload = $this->previewUpload->findByVersion($projectId, $version);

        $author = $this->commentAuthor->getAuthorDataFromSession();

        $this->reviewMailer->approveRevision($previewUpload, $author);

        return Response::json(['success' => true]);
    }
}
