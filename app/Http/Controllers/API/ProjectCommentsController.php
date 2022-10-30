<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Mailers\ReviewMailer;
use MotionArray\Models\ProjectComment;
use MotionArray\Repositories\PreviewUploadRepository;
use MotionArray\Repositories\ProjectCommentRepository;
use MotionArray\Repositories\ReviewRepository;
use Request;
use Response;

class ProjectCommentsController extends BaseController
{
    protected $comment;

    protected $reviewMailer;

    protected $previewUpload;

    protected $reviewRepo;

    public function __construct(
        ProjectCommentRepository $comment,
        PreviewUploadRepository $previewUpload,
        ReviewRepository $reviewRepo,
        ReviewMailer $reviewMailer
    )
    {
        $this->comment = $comment;

        $this->previewUpload = $previewUpload;

        $this->reviewMailer = $reviewMailer;

        $this->reviewRepo = $reviewRepo;
    }

    public function index($projectId, $version)
    {
        $comments = $this->comment->findByProjectVersion($projectId, $version);

        return Response::json($comments);
    }

    public function create($projectId, $version)
    {
        $previewUpload = $this->previewUpload->findByVersion($projectId, $version);

        $data = Request::all();

        $data['preview_upload_id'] = $previewUpload->id;

        $comment = $this->comment->create($projectId, $data);

        if (!$comment->failed()) {
            $comment = $comment->fresh('author');

            $comment->author;

            $this->reviewRepo->sendUserCommentNotification($previewUpload, $comment);

            //If this is a reply we may also notify the parent
            if ($comment->parent_id != null && $comment->parent_id > 0) {
                $this->reviewRepo->sendCommentReplyNotification($comment);
            }
        }

        return Response::json($comment);
    }

    public function update($projectId, $version, $commentId)
    {
        $data = Request::only([
            'body'
        ]);

        $comment = $this->comment->update($commentId, $data);

        $this->reviewRepo->sendCommentUpdateNotification($comment);

        return Response::json($comment);
    }

    public function toggleCheckedState($projectId, $version, $commentId)
    {
        $comment = ProjectComment::find($commentId);

        $comment->done = !$comment->done;

        $comment->save();

        return Response::json($comment);
    }

    public function destroy($projectId, $version, $commentId)
    {
        $comment = ProjectComment::find($commentId);

        $response = $comment->delete();

        $this->reviewRepo->sendCommentDeleteNotification($comment);

        $response = Response::json($response);

        if (Request::has('callback')) {
            return $response->setCallback(Request::get('callback'));
        }

        return $response;
    }
}
