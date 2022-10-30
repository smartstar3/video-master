<?php namespace MotionArray\Http\Controllers\Post;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use MotionArray\Helpers\Portfolio\PortfolioHelper;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Repositories\PortfolioContentRepository;
use MotionArray\Repositories\PortfolioPageRepository;
use MotionArray\Repositories\ProjectRepository;

class PortfoliosController extends Controller
{
    protected $previewingTheme;

    protected $portfolioContent;

    protected $portfolioPage;

    protected $project;

    protected $userMailer;

    public function __construct(
        PortfolioContentRepository $portfolioContent,
        PortfolioPageRepository $portfolioPage,
        ProjectRepository $project,
        UserMailer $userMailer
    )
    {
        $this->portfolioContent = $portfolioContent;

        $this->portfolioPage = $portfolioPage;

        $this->project = $project;

        $this->userMailer = $userMailer;
    }

    /**
     * Portfolio Home View
     *
     * @param $portfolio (slug)
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show()
    {
        $site = Request::get('current_site');

        $page = null;

        $portfolio = null;

        $screenshot = Request::has('site-id');

        if ($site) {
            $portfolio = $site->portfolio;

            if ($portfolio) {
                $page = $this->portfolioPage->getHomePage($portfolio);

                if (!$page) {
                    $page = $this->portfolioPage->newHomePage($portfolio);
                }
            }
        }

        $theme = $this->previewingTheme;

        $contentHelper = $this->getPortfolioContent($page, $portfolio, true);

        return view("site.portfolio.home", compact('site', 'portfolio', 'page', 'contentHelper', 'screenshot', 'theme'));
    }

    /**
     * Portfolio project View
     *
     * @param $portfolio (slug)
     * @param $tdl
     * @param $project
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function project($slug)
    {
        $site = Request::get('current_site');

        $portfolio = null;

        if ($site) {
            $portfolio = $site->portfolio;
        }

        $project = $this->project->findBySlug($slug);

        if (!$project) {
            return Redirect::to('/');
        }

        $homePage = null;
        if ($portfolio) {
            $homePage = $this->portfolioPage->getHomePage($portfolio);
        }
        $page = $this->portfolioPage->findByProject($project);

        if (!$page) {
            $page = $this->portfolioPage->newByProject($project);
        }

        $theme = $this->previewingTheme;

        $contentHelper = $this->getPortfolioContent($page, $portfolio);

        return view("site.portfolio.project", compact('site', 'project', 'portfolio', 'page', 'homePage', 'contentHelper', 'theme'));
    }

    public function uploadImage()
    {
        $site = Request::get('current_site');

        $key = Request::get('key');

        $portfolio = $site->portfolio;

        $upload = $this->portfolioContent->uploadImage($portfolio, $key, null);

        if ($upload) {
            $response = [
                'success' => true,
                'id' => $upload->id,
                'url' => $upload->url
            ];
        } else {
            $response = [
                'success' => false
            ];
        }

        return Response::json($response);
    }

    public function copyImage()
    {
        $site = Request::get('current_site');

        $portfolio = $site->portfolio;

        $url = Request::get('url');

        $key = Request::get('key');

        $upload = $this->portfolioContent->uploadImageFromUrl($portfolio, $key, $url, null);

        if ($upload) {
            $response = [
                'success' => true,
                'id' => $upload->id,
                'url' => $upload->url
            ];
        } else {
            $response = [
                'success' => false
            ];
        }

        return Response::json($response);
    }

    /**
     * Sends Portfolio message to user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage()
    {
        $inputs = Request::all();

        $inputs['receive_email'] = Crypt::decrypt($inputs['receive_email']);

        $this->userMailer->portfolioMessage($inputs);

        return Response::json(['success' => true], 200);
    }

    /**
     * Returns portfolio page Content
     *
     * @param $page
     * @param $portfolio
     * @return mixed
     */
    protected function getPortfolioContent(PortfolioPage $page = null, Portfolio $portfolio = null, $useDefaultContent = false)
    {
        $theme = null;

        return PortfolioHelper::getContentHelper($page, $portfolio, $theme, $useDefaultContent);
    }
}
