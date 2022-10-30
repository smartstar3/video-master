<?php namespace MotionArray\Http\Controllers\Post;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use MotionArray\Helpers\Portfolio\PortfolioHelper;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Repositories\PortfolioContentRepository;
use MotionArray\Repositories\PortfolioPageRepository;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Repositories\ProjectRepository;

class PortfoliosPreviewController extends PortfoliosController
{
    protected $portfolioTheme;

    public function __construct(
        PortfolioThemeRepository $portfolioTheme,
        PortfolioContentRepository $portfolioContent,
        PortfolioPageRepository $portfolioPage,
        ProjectRepository $project,
        UserMailer $userMailer
    )
    {
        $this->portfolioTheme = $portfolioTheme;

        parent::__construct($portfolioContent, $portfolioPage, $project, $userMailer);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Contracts\View\View
     */
    public function show()
    {
        $themeId = request()->id;

        $this->previewingTheme = $this->portfolioTheme->findById($themeId);

        if (!$this->previewingTheme) {
            return Redirect::to('/account/uploads/portfolio');
        }

        $isOwner = $this->previewingTheme->user_id == auth()->id();
        if (!$this->previewingTheme->isSiteTheme() && !$isOwner) {
            return Redirect::to('/account/uploads/portfolio');
        }

        return parent::show();
    }

    /**
     * @param $themeId
     * @param $projectSlug
     * @return \Illuminate\Contracts\View\View
     */
    public function project($projectSlug)
    {
        $themeId = request()->id;

        $projectSlug = request()->slug;

        $this->previewingTheme = $this->portfolioTheme->findById($themeId);

        if (!$this->previewingTheme) {
            return Redirect::to('/account/uploads/portfolio');
        }

        $isOwner = $this->previewingTheme->user_id == auth()->id();
        if (!$this->previewingTheme->isSiteTheme() && !$isOwner) {
            return Redirect::to('/account/uploads/portfolio');
        }

        return parent::project($projectSlug);
    }

    /**
     * Returns portfolio Content
     *
     * @param $page
     * @param $portfolio
     * @return mixed
     */
    protected function getPortfolioContent(PortfolioPage $page = null, Portfolio $portfolio = null, $useDefaultContent = false)
    {
        $theme = $this->previewingTheme;

        return PortfolioHelper::getContentHelper($page, $portfolio, $theme, $useDefaultContent);
    }
}
