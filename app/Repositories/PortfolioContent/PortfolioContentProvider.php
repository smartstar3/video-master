<?php namespace MotionArray\Repositories\PortfolioContent;


use MotionArray\Models\Portfolio;
use MotionArray\Models\PortfolioPage;
use MotionArray\Models\PortfolioTheme;
use MotionArray\Repositories\PortfolioContent\MergeContent\MergeStrategyInterface;

class PortfolioContentProvider
{
    protected $mergeStrategy;

    public function make(PortfolioPage $page = null, Portfolio $portfolio = null, PortfolioTheme $theme = null, $useDefaultContent = false)
    {
        if ($page && !$portfolio) {
            $portfolio = $page->portfolio;
        }

        if ($portfolio && !$theme) {
            $theme = $portfolio->portfolioTheme;
        }

        return $this->mergeStrategy->setUseDefaultContent($useDefaultContent)->mergePortfolioSettings($page, $portfolio, $theme);
    }

    public function setMergeStrategy(MergeStrategyInterface $mergeStrategy)
    {
        $this->mergeStrategy = $mergeStrategy;

        return $this;
    }
}
