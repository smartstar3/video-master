<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\UserSite;
use MotionArray\Repositories\PortfolioThemeRepository;
use MotionArray\Repositories\UserSiteAppRepository;
use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioTheme;
use MotionArray\Models\PortfolioPage;
use App;

class PortfolioRepository extends UserSiteAppRepository
{
    protected $portfolioTheme;

    public function __construct(
        Portfolio $portfolio,
        PortfolioThemeRepository $portfolioTheme
    )
    {
        $this->model = $portfolio;

        $this->portfolioTheme = $portfolioTheme;
    }

    public function createDefault(UserSite $userSite)
    {
        $portfolio = parent::createDefault($userSite);

        $this->portfolioTheme->activateDefaultTheme($portfolio);

        return $portfolio->fresh();
    }

    /**
     * Removes extended theme and changes in portfolio
     *
     * @param Portfolio $portfolio
     * @return Portfolio $portfolio
     */
    public function reset(Portfolio $portfolio)
    {
        $theme = $portfolio->portfolioTheme;

        $parentTheme = $theme->parentTheme;


        $portfolio->pages()->delete();

        $portfolio->unpublished_settings = null;

        $portfolio->settings = null;

        $portfolio->save();

        if ($parentTheme) {
            $this->portfolioTheme->activate($portfolio, $parentTheme);

            $theme->delete();
        }

        return $portfolio;
    }

    /**
     * Publish Portfolio
     * @param Portfolio $portfolio
     * @return bool
     */
    public function publish(Portfolio $portfolio)
    {
        $pageRepository = App::make('MotionArray\Repositories\PortfolioPageRepository');

        // Publish Portfolio
        $portfolio->public = true;

        $portfolio->last_published_at = Carbon::now();

        // Copy saved settings to public settings
        $portfolio->settings = $portfolio->unpublished_settings;

        $portfolio->save();

        // Pages
        $pageRepository->publish($portfolio);

        return true;
    }

    /**
     * Unpublish Portfolio
     * @param Portfolio $portfolio
     * @return bool
     */
    public function unpublish(Portfolio $portfolio)
    {
        $portfolio->public = false;

        $portfolio->last_published_at = null;

        return $portfolio->save();
    }

    public function applyStyles($portfolioSettings, PortfolioTheme $portfolioTheme)
    {
        $themeSettings = $portfolioTheme->settings;

        foreach ($portfolioSettings as $i => &$setting) {
            if (isset($themeSettings[$i])) {
                $styles = [];
                if (isset($themeSettings[$i]['styles'])) {
                    $styles = $themeSettings[$i]['styles'];
                }
                $setting['styles'] = $styles;

                $classes = [];
                if (isset($themeSettings[$i]['classes'])) {
                    $classes = $themeSettings[$i]['classes'];
                }
                $setting['classes'] = $classes;
            }
        }

        return $portfolioSettings;
    }

    public function applyMenuStyles($settings, PortfolioTheme $theme, PortfolioPage $homePage = null)
    {
        if ($homePage) {
            $settings = array_merge($settings, $homePage->unpublished_settings);
        }

        $portfolioMenu = $settings['header']['content']['menu'];

        $themeSettings = $theme->settings;
        $themeMenu = $themeSettings['header']['content']['menu'];

        if (is_array($portfolioMenu) && isset($settings['sections'])) {
            $portfolioMenu = array_map(function ($menuItem) use ($settings) {
                $section = array_first($settings['sections'], function ($key, $section) use ($menuItem) {
                    if (isset($menuItem['id']) && isset($section['id'])) {
                        return $section['id'] == $menuItem['id'];
                    }
                    return false;
                });

                $menuItem['type'] = $section['type'];

                return $menuItem;
            }, $portfolioMenu);

            $portfolioMenu = array_filter($portfolioMenu, function ($portfolioMenuItem) {
                return !isset($portfolioMenuItem['origin']) || $portfolioMenuItem['origin'] != 'theme';
            });
        }

        if (is_array($themeMenu)) {
            $themeMenu = array_map(function ($menuItem) use ($themeSettings) {
                $section = array_first($themeSettings['sections'], function ($key, $section) use ($menuItem) {
                    return $section['id'] == $menuItem['id'];
                });

                $menuItem['type'] = $section['type'];

                return $menuItem;
            }, $themeMenu);
        }

        if (is_array($portfolioMenu) && is_array($themeMenu)) {
            foreach ($themeMenu as $i => $themeMenuItem) {
                foreach ($portfolioMenu as &$portfolioMenuItem) {

                    // Apply styles
                    if ($themeMenuItem['type'] == $portfolioMenuItem['type']) {

                        if (isset($themeMenuItem['title'])) {
                            // If it exists on portfolio, extend styles
                            // otherwise just add
                            if (!isset($portfolioMenuItem['title'])) {
                                $portfolioMenuItem['title'] = $themeMenuItem['title'];
                            } else {
                                $portfolioMenuTitle = strip_tags($portfolioMenuItem['title']);

                                if (strip_tags($themeMenuItem['title'], '<i><em>') != $themeMenuItem['title']) {
                                    $portfolioMenuTitle = '<strong>' . $portfolioMenuTitle . '</strong>';
                                }

                                if (strip_tags($themeMenuItem['title'], '<strong><b>') != $themeMenuItem['title']) {
                                    $portfolioMenuTitle = '<em>' . $portfolioMenuTitle . '</em>';
                                }

                                $portfolioMenuItem['title'] = $portfolioMenuTitle;
                            }
                        }

                        break;
                    }
                }
            }


            $menu = [];

            foreach ($themeMenu as $themeMenuItem) {
                $added = false;

                foreach ($portfolioMenu as $i => $item) {

                    if ($themeMenuItem['type'] == $item['type']) {

                        $item['menu_active'] = $themeMenuItem['menu_active'];
                        $item['section_active'] = $themeMenuItem['section_active'];
                        $menu[] = $item;

                        $added = true;

                        unset($portfolioMenu[$i]);

                        break;
                    }
                }

                if (!$added) {
                    $themeMenuItem['origin'] = 'theme';

                    $menu[] = $themeMenuItem;
                }
            }
        }

        $settings['header']['content']['menu'] = $menu;

        return $settings;
    }
}
