<?php namespace MotionArray\Repositories\PortfolioContent\MergeContent;

use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;

class ThemePreviewStrategy extends BaseMergeStrategy implements MergeStrategyInterface
{
    public function mergePortfolioSettings(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null)
    {
        if ($page && !$page->isHome()) {
            // Theme(Styles) > Page(Published) > Portfolio(Published) > Code

            // Page + Portfolio general Settings
            $settings = $this->extendSettings(@$portfolio->unpublished_settings, @$page->unpublished_settings);

            $settings = $this->prepareThemeContent($theme, $settings, $portfolio, $page);

            // + Theme Styles
            $settings = $this->applyStyles($settings, $theme);

            // Replace Header and footer
            $settings['header'] = $theme->settings['header'];
            $settings['footer'] = $theme->settings['footer'];
        } else {
            $settings = $theme->settings;
        }

        $settings = $this->addSectionIds($settings);

        return $settings;
    }
}
