<?php namespace MotionArray\Repositories\PortfolioContent\MergeContent;

use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;

Interface MergeStrategyInterface
{
    public function mergePortfolioSettings(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null);
}
