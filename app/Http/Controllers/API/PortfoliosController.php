<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Models\Portfolio;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\PortfolioContentRepository;
use MotionArray\Repositories\PortfolioPageRepository;
use MotionArray\Repositories\ProjectRepository;
use Auth;
use Request;
use Response;

class PortfoliosController extends BaseController
{
    protected $portfolio;

    protected $portfolioContent;

    protected $portfolioPage;

    protected $project;

    public function __construct(
        PortfolioRepository $portfolio,
        PortfolioContentRepository $portfolioContent,
        PortfolioPageRepository $portfolioPage,
        ProjectRepository $project
    )
    {
        $this->portfolio = $portfolio;

        $this->portfolioContent = $portfolioContent;

        $this->portfolioPage = $portfolioPage;

        $this->project = $project;
    }

    public function updateColorPicker($portfolioId)
    {
        $customColors = Request::input('colors');

        $portfolio = $this->portfolio->findById($portfolioId);

        $portfolio = $this->portfolioContent->updateColorPicker($portfolio, $customColors);

        return Response::json(['success' => true, 'response' => $portfolio]);
    }

    /**
     * @param $portfolioId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateContent($portfolioId, $projectId = null)
    {
        $content = Request::input('content');
        $settings = Request::input('settings');

        $portfolio = $this->portfolio->findById($portfolioId);

        if ($projectId) {
            $project = $this->project->findById($projectId);

            $page = $this->portfolioPage->findOrCreateByProject($project);
        } else {
            $page = $this->portfolioPage->findOrCreateHomePage($portfolio);
        }

        $response = $this->portfolioContent->updateContent($page, $portfolio, $content, $settings);

        if (isset($response['project'])) {
            $projectChanges = $response['project']['content'];

            $this->project->update($project->id, $projectChanges);
        }

        return Response::json(['success' => true, 'response' => $response]);
    }

    /**
     * @param $portfolioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish($portfolioId)
    {
        $portfolio = $this->portfolio->findById($portfolioId);

        $success = $this->portfolio->publish($portfolio);

        return Response::json(['success' => $success]);
    }

    public function unpublish($portfolioId)
    {
        $portfolio = $this->portfolio->findById($portfolioId);

        $success = $this->portfolio->unpublish($portfolio);

        return Response::json(['success' => $success]);
    }

    /**
     * Resets Portfolio
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset()
    {
        if (!Auth::check()) {
            return Response::json(['success' => false]);
        }

        $portfolio = $this->portfolio->findByUser(Auth::user());

        $portfolio = $this->portfolio->reset($portfolio);

        return Response::json(['success' => true, 'response' => $portfolio]);
    }

    public function getSettings($portfolioId, $path)
    {
        $portfolio = Portfolio::whereId($portfolioId)->first();
        $settings = $portfolio->unpublished_settings;
        $settings = array_get($settings, $path);

        return $settings;
    }

    public function statusCheck()
    {
        return Response::json(['status' => 'ok']);
    }
}
