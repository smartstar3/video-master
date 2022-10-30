<?php namespace MotionArray\Http\Controllers\Shared;

use App;
use MotionArray\Models\Project;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Services\Submission\SubmissionService;
use Response;
use Request;
use Auth;

class ProjectsController extends BaseController
{
    /**
     * @var PortfolioRepository
     */
    protected $portfolio;

    /**
     * @var ProjectRepository
     */
    protected $project;

    /**
     * @var SubmissionService
     */
    protected $submissionService;

    public function __construct(
        PortfolioRepository $portfolio,
        ProjectRepository $project,
        SubmissionService $submissionService
    )
    {
        $this->portfolio = $portfolio;
        $this->project = $project;
        $this->submissionService = $submissionService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $user = Auth::user();

        //Check the user can upload
        if (!$user->canUpload()) {
            $response['state'] = 'error';
            $response['errors'] = 'You need to upgrade to a paid plan to upload';

            http_response_code(400);

            return Response::json($response, 400);
        }

        $inputs = array_except(Request::all(), '_method');
        $inputs['user_id'] = $user->id;

        $project = $this->project->make($inputs);

        if ($project) {
            // Get the AWS data
            $response = $this->submissionService->prepareProductJson($project);

            return Response::json($response);
        }

        $response['state'] = 'error';
        $response['errors'] = json_decode($this->project->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $submission_id
     *
     * @return Response
     */
    public function update($id)
    {
        $response = [];
        $inputs = array_except(Request::all(), '_method');

        $project = $this->project->update($id, $inputs);

        if ($project) {
            $prepareProject = $this->submissionService->prepareProductJson($project);

            return Response::json($prepareProject);
        }

        $response['state'] = 'error';
        $response['errors'] = json_decode($this->project->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if ($project) {
            $this->project->delete($project);

            return Response::json("Project $id deleted successfully.", 200);
        }

        return App::abort('404');
    }
}
