<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Repositories\PluginTokenRepository;
use MotionArray\Repositories\StatRepository;
use View;

class StatsController extends BaseController
{
    protected $stats;

    protected $pluginToken;

    public function __construct(
        StatRepository $stats,
        PluginTokenRepository $pluginTokenRepository
    )
    {
        $this->stats = $stats;

        $this->pluginToken = $pluginTokenRepository;
    }

    public function plugins()
    {
        $usageCount = $this->pluginToken->getUsageCount();

        $users = $this->pluginToken->getPluginUsers();

        return View::make('admin.stats.plugins', compact('usageCount', 'users'));
    }

    public function portfolios()
    {
        if (!auth()->user()->hasRole(1)) {
            return Redirect::to('/mabackend');
        }

        $createdPortfoliosByDate = $this->stats->getPortfoliosCreatedByDate();

        $publishedPortfolios = $this->stats->getPublishedPortfolios();

        return view('admin.stats.portfolios', compact('createdPortfoliosByDate', 'publishedPortfolios'));
    }

    public function reviews()
    {
        if (!auth()->user()->hasRole(1)) {
            return Redirect::to('/mabackend');
        }

        $reviewsCountByUser = $this->stats->getReviewsCountByUser();

        $createdReviewsByDate = $this->stats->getReviewsCreatedByDate();

        return view('admin.stats.reviews', compact('createdReviewsByDate', 'reviewsCountByUser'));
    }
}
