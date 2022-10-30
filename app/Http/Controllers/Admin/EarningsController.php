<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\PayoutTotal;
use MotionArray\Repositories\DownloadRepository;
use MotionArray\Repositories\UserRepository;
use View;
use Carbon\Carbon;

class EarningsController extends BaseController
{
    protected $user;

    protected $download;

    public function __construct(UserRepository $user, DownloadRepository $download)
    {
        $this->user = $user;

        $this->download = $download;
    }

    public function index()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $sitePayout = PayoutTotal::getTotalPayoutForMonth($startDate->month, $startDate->year);

        $categoryWeights = $this->download->getWeightForPeriodByCategory($startDate, $endDate);

        $totalWeight = $this->download->totalWeight($startDate, $endDate);

        $sellers = $this->user->getSellers();

        $sellers = $sellers->map(function($seller) use ($totalWeight, $sitePayout, $startDate, $endDate) {
            $sellerCategoryWeights = $this->download->getWeightForPeriodByCategory($startDate, $endDate, $seller);

            $weight = $sellerCategoryWeights->sum('weight');

            $seller->weight = $weight;

            $seller->earnings = $weight ? ($sitePayout/$totalWeight) * $weight : 0;

            return $seller;
        });

        $sellers = $sellers->sortByDesc('weight');

        return View::make('admin.earnings.index', compact("sellers"));
    }
}
