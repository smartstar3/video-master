<?php namespace MotionArray\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Repositories\UserSiteRepository;
use Request;
use Response;

class PortfolioThemesController extends BaseController
{
    protected $userSite;

    protected $portfolio;

    protected $portfolioTheme;

    public function __construct(
        UserSiteRepository $userSite,
        PortfolioRepository $portfolio,
        PortfolioThemeRepository $portfolioTheme
    )
    {
        $this->userSite = $userSite;

        $this->portfolio = $portfolio;

        $this->portfolioTheme = $portfolioTheme;
    }

    public function index()
    {
        $user = Auth::user();

        $themes = $this->portfolioTheme->findByUser($user);

        $activeId = null;

        if ($user) {
            $portfolio = $this->portfolio->findByUser($user);

            $activeId = $portfolio ? $portfolio->portfolio_theme_id : null;
        }

        $themes = $themes->map(function ($theme) use ($activeId) {
            $theme->active = $theme->id == $activeId;

            return $theme;
        });

        return Response::json(['success' => true, 'items' => $themes]);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($themeId)
    {
        if (!Auth::check()) {
            return Response::json(['success' => false]);
        }

        $user = Auth::user();

        $theme = $this->portfolioTheme->findById($themeId);

        $userSite = $this->userSite->findOrCreateByUser($user);

        $portfolio = $this->portfolio->findOrCreateBySite($userSite);

        $this->portfolioTheme->activate($portfolio, $theme);

        return Response::json(['success' => true]);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicate($themeId)
    {
        if (!Auth::check()) {
            return Response::json(['success' => false]);
        }

        $user = Auth::user();

        $theme = $this->portfolioTheme->findById($themeId);

        $duplicate = $this->portfolioTheme->duplicate($theme, $user);

        return Response::json(['success' => !!$duplicate, 'response' => $duplicate]);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($themeId)
    {
        $user = Auth::user();
        $theme = $this->portfolioTheme->findById($themeId);

        if ($theme->user_id == null && !$user->isAdmin()) {
            return Response::json(['success' => false]);
        }

        $success = $theme->delete();

        return Response::json(['success' => $success]);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeSiteTheme($themeId)
    {
        $theme = $this->portfolioTheme->findById($themeId);

        $this->portfolioTheme->makeSiteTheme($theme);

        return Response::json(['success' => true]);
    }

    /**
     * @param $themeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename($themeId)
    {
        $name = Request::get('name');

        $theme = $this->portfolioTheme->findById($themeId);

        $theme = $this->portfolioTheme->rename($theme, $name);

        return Response::json(['success' => !!$theme, 'response' => $theme]);
    }
}
