<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Redirect;
use MotionArray\Helpers\Portfolio\PortfolioContent\PortfolioContentHelper;
use MotionArray\Helpers\Portfolio\PortfolioHelper;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\PortfolioPageRepository;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Repositories\ProjectRepository;

class PortfolioController extends BaseController
{
    protected $page;
    protected $project;
    protected $portfolioPage;
    protected $portfolioTheme;

    protected $redirectTo = "/";
    protected $paginationRange = 10;

    public function __construct(
        PageRepository $pageRepository,
        ProjectRepository $projectRepository,
        PortfolioPageRepository $portfolioPageRepository,
        PortfolioThemeRepository $portfolioThemeRepository
    )
    {
        $this->page = $pageRepository;
        $this->project = $projectRepository;
        $this->portfolioPage = $portfolioPageRepository;
        $this->portfolioTheme = $portfolioThemeRepository;
    }

    /**
     * Display portfolio Landing
     *
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $entry = $this->page->getPageByURI('portfolio');

        if (!$entry) {
            return app()->abort(404);
        }

        $portfolioThemes = $this->portfolioTheme->findSiteThemes();

        $portfolioThemes = $portfolioThemes->filter(function ($portfolioTheme) {
            return !$portfolioTheme->hidden;
        })->values();

        return view('site.portfolio-landing.portfolio-landing', compact('entry', 'portfolioThemes'));
    }

    public function example($themeId)
    {
        $portfolioThemeRepository = \App::make('MotionArray\Repositories\PortfolioThemeRepository');

        $theme = $portfolioThemeRepository->findById($themeId);

        if (!$theme || !$theme->isSiteTheme()) {
            throw new \Exception('Invalid theme id: ' . $themeId);
        }

        $contentHelper = new PortfolioContentHelper($theme->settings);

        return view("site.portfolio.home", [
            'site' => null,
            'portfolio' => null,
            'page' => null,
            'contentHelper' => $contentHelper,
        ]);
    }

    public function projectExample($themeId, $slug)
    {
        $portfolioThemeRepository = \App::make('MotionArray\Repositories\PortfolioThemeRepository');

        $theme = $portfolioThemeRepository->findById($themeId);

        if (!$theme || !$theme->isSiteTheme()) {
            throw new \Exception('Invalid theme id: ' . $themeId);
        }

        $project = $this->project->findBySlug($slug);

        if (!$project) {
            return Redirect::to('/');
        }

        $page = $this->portfolioPage->findByProject($project);

        if (!$page) {
            $page = $this->portfolioPage->newByProject($project);
        }

        $contentHelper = PortfolioHelper::getContentHelper($page, null, $theme, false);

        return view("site.portfolio.project", [
            'site' => null,
            'portfolio' => null,
            'page' => $page,
            'project' => $project,
            'contentHelper' => $contentHelper,
        ]);

    }
}
