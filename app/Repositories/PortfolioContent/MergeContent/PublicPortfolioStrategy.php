<?php namespace MotionArray\Repositories\PortfolioContent\MergeContent;

use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;

class PublicPortfolioStrategy extends BaseMergeStrategy implements MergeStrategyInterface
{
    public function mergePortfolioSettings(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null)
    {
        // Page(Published) > Portfolio(Published) > Theme > Code

        // Page + Portfolio general Settings
        $settings = $this->extendSettings(@$portfolio->settings, @$page->settings);

        $settings = $this->addSectionIds($settings);

        return $settings;
    }
}
