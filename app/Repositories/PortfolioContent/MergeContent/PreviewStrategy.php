<?php namespace MotionArray\Repositories\PortfolioContent\MergeContent;

use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;

class PreviewStrategy extends BaseMergeStrategy implements MergeStrategyInterface
{
    public function mergePortfolioSettings(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null)
    {
        // Theme(Styles) > Page(Unpublished) > Portfolio(Unpublished) > Code

        // Page + Portfolio general Settings
        $settings = $this->extendSettings($portfolio->unpublished_settings, @$page->unpublished_settings);

        $settings = $this->prepareThemeContent($theme, $settings, $portfolio, $page);

        $settings = $this->addSectionIds($settings);

        return $settings;
    }
}
