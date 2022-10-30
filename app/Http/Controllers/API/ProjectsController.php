<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Request;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\ProjectRepository;
use Response;

class ProjectsController extends BaseController
{
    protected $project;

    protected $portfolio;

    public function __construct(ProjectRepository $project, PortfolioRepository $portfolio)
    {
        $this->project = $project;

        $this->portfolio = $portfolio;
    }

    public function index()
    {
        $with = request('with');
        $public = request('public');

        $projects = $this->project->findByUser(null, function ($query) use ($public) {
            if ($public == 'true') {
                $query->where('is_public', 1);
            }
            return $query->with(['reviewSettings', 'previewUploads', 'activePreview.videoFiles']);
        });

        foreach ($projects as &$project) {
            $project->name = $project->plain_name;
            $project->description = $project->plain_description;
            $project->placeholder = $project->getPlaceholder();
            $project->setHidden(['portfolio']);

            if ($with == 'player') {
                $project->player = $project->present()->preview("minimal", "low", null, 600);

                $project->setHidden(['portfolio', 'aws', 'meta', 'previews', 'preview_uploads', 'active_preview', 'user']);
            }
        }

        return Response::json($projects);
    }

    public function show($id)
    {
        return $this->project->findById($id);
    }

    public function uploadImage($projectId)
    {
        $key = Request::get('key');

        $project = $this->project->findById($projectId);

        $url = $this->project->replaceImage($project, $key);

        $success = $this->project->updateReviewSettings($project, [$key => $url]);

        return Response::json([
            'success' => $success
        ]);
    }

    public function removeImage($projectId)
    {
        $key = Request::get('key');

        $project = $this->project->findById($projectId);

        $this->project->deleteImage($project, $key);

        $success = $this->project->updateReviewSettings($project, [$key => null]);

        return Response::json([
            'success' => $success
        ]);
    }
}
