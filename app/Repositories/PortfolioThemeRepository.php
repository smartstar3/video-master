<?php namespace MotionArray\Repositories;

use Illuminate\Support\Facades\Auth;
use MotionArray\Models\PortfolioTheme;
use MotionArray\Models\Project;
use MotionArray\Models\Portfolio;
use MotionArray\Models\User;
use MotionArray\Repositories\EloquentBaseRepository;
use App;

class PortfolioThemeRepository extends EloquentBaseRepository
{
    /**
     * PortfolioThemeRepository constructor.
     * @param PortfolioTheme $portfolioTheme
     */
    public function __construct(PortfolioTheme $portfolioTheme)
    {
        $this->model = $portfolioTheme;
    }

    /**
     * Returns MotionArray themes
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findSiteThemes()
    {
        return PortfolioTheme::whereNull('user_id')->get();
    }

    /**
     * @param User|null $user
     * @param bool $includeSiteThemes
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByUser(User $user = null, $includeSiteThemes = true)
    {
        if (!$user) {
            $user = Auth::user();
        }

        $query = PortfolioTheme::query();

        if ($user) {
            $query = $query->where('user_id', $user->id);
        }

        if ($includeSiteThemes) {
            $query = $query->orWhereNull('user_id');
        }

        return $query->get();
    }

    /**
     * Returns Default Theme
     *
     * @return mixed
     * @throws \Exception
     */
    public function getDefaultTheme()
    {
        $siteThemes = $this->findSiteThemes();

        return $siteThemes->first();
    }

    /**
     * Duplicates Portfolio Theme
     *
     * @param PortfolioTheme $theme
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Model|PortfolioTheme
     */
    public function duplicate(PortfolioTheme $theme, User $user)
    {
        $theme = $theme->replicate();

        $theme->user_id = $user->id;

        $theme->parent_theme_id = null;

        $theme->save();

        return $theme;
    }

    /**
     * "Extends" a theme
     * Duplicates a theme and keeps the parent_theme_id
     *
     * @param PortfolioTheme $theme
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Model|PortfolioTheme
     */
    public function extendTheme(PortfolioTheme $theme, User $user = null)
    {
        $duplicate = $this->duplicate($theme, $user);

        $duplicate->parent_theme_id = $theme->id;

        $duplicate->save();

        return $duplicate;
    }

    /**
     * Activates Theme on given portfolio
     *
     * @param Portfolio $portfolio
     * @param PortfolioTheme|null $theme
     *
     * @return bool
     * @throws \Exception
     */
    public function activate(Portfolio $portfolio, PortfolioTheme $theme)
    {
        if ($theme->user_id && $theme->user_id != $portfolio->site->user_id) {
            throw new \Exception('provided $userId doesnt match the themes user_id');
        }

        $pageRepository = app()->make('MotionArray\Repositories\PortfolioPageRepository');
        $portfolioRepository = app()->make('MotionArray\Repositories\PortfolioRepository');

        $homePage = null;
        $portfolio->portfolio_theme_id = $theme->id;

        if ($portfolio->unpublished_settings) {
            $settings = $portfolioRepository->applyStyles($portfolio->unpublished_settings, $theme);

            $homePage = $pageRepository->getHomePage($portfolio);

            if ($homePage) {
                $settings = $portfolioRepository->applyMenuStyles($settings, $theme, $homePage);
            }

            $portfolio->unpublished_settings = $settings;
        }

        foreach ($portfolio->pages as $page) {
            $pageSettings = $page->unpublished_settings;

            if ($page->isHome()) {
                $pageSettings = $pageRepository->applyTheme($pageSettings, $theme);
            } else {
                $pageSettings = $pageRepository->applyStyles($pageSettings, $theme);
            }

            $page->unpublished_settings = $pageRepository->stripHtmlTagAttributes($pageSettings);

            $page->save();
        }

        // Apply New Theme style on pages and Portfolio

        return $portfolio->save();
    }

    public function activateDefaultTheme(Portfolio $portfolio)
    {
        $theme = $this->getDefaultTheme();

        return $this->activate($portfolio, $theme);
    }

    /**
     * @param $theme
     * @param $name
     * @return bool|static
     */
    public function rename(PortfolioTheme $theme, $name)
    {
        $theme->name = $name;

        $theme->save();

        return $theme;
    }

    /**
     * Make theme public for all users
     *
     * @param PortfolioTheme $theme
     */
    public function makeSiteTheme(PortfolioTheme $theme)
    {
        $theme->user_id = null;

        $theme->save();
    }

    /**
     * Save portfolio settings on theme
     *
     * @param Portfolio $portfolio
     * @return PortfolioTheme
     * @throws \Exception
     */
    public function publish(Portfolio $portfolio)
    {
        // Update/create theme with Home page settings
        $theme = $portfolio->portfolioTheme;

        $user = $portfolio->site->user;

        if (!$theme) {
            throw new \Exception('Choosing a theme is required before publishing');
        }

        // If is MA theme
        // Extend theme
        if ($theme->isSiteTheme()) {
            $theme = $this->extendTheme($theme, $user);

            $portfolio->update(['portfolio_theme_id' => $theme->id]);
        }

        return $this->updateTheme($theme, $portfolio);
    }

    /**
     * @param PortfolioTheme $theme
     * @param Portfolio $portfolio
     * @return PortfolioTheme
     */
    public function updateTheme(PortfolioTheme $theme, Portfolio $portfolio)
    {
        $pageRepository = App::make('MotionArray\Repositories\PortfolioPageRepository');

        $portfolioHomePage = $pageRepository->getHomePage($portfolio);

        if ($portfolioHomePage) {
            $settings = array_merge((array)$portfolio->unpublished_settings, (array)$portfolioHomePage->unpublished_settings);
        } else {
            $settings = $portfolio->unpublished_settings;
        }

        $theme->settings = $settings;

        $theme->save();

        return $theme;
    }
}


