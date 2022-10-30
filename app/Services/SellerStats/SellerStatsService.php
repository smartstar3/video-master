<?php namespace MotionArray\Services\SellerStats;

use Carbon\Carbon;
use MotionArray\Models\Category;
use MotionArray\Models\Country;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\User;
use MotionArray\Repositories\SellerPayoutRepository;

/**
 * Used to dispatch to CreditSellerStatsService and UnlimitedSellerStatsService
 * Not to be confused with their base class, BaseSellerStatsService
 */
class SellerStatsService
{
    protected $unlimitedStats;

    // Old Stats
    protected $creditsStats;

    protected $sellerPayout;

    public function __construct(
        UnlimitedSellerStatsService $unlimitedStats,
        CreditsSellerStatsService $creditsStats,
        SellerPayoutRepository $sellerPayout
    )
    {
        $this->unlimitedStats = $unlimitedStats;

        $this->creditsStats = $creditsStats;

        $this->sellerPayout = $sellerPayout;
    }

    public function getOldStatsEndDate()
    {
        return Carbon::create(2018, 12, 01)->startOfMonth();
    }

    /**
     * Calculate income for a particular seller
     * Uses UnlimitedSellerStats for unlimited dates, and CreditsSellerStats for dates prior to moving to unlimited (Dec 2018).
     *
     * @param User $seller
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Country $downloadCountry only calculate income from a particular country
     * @return float
     */
    public function getSellerEarnings(User $seller, Carbon $startDate, Carbon $endDate, PayoutTotal $sitePayout, Country $downloadCountry = null)
    {
        $oldStatsEndDate = $this->getOldStatsEndDate();

        $earnings = 0;

        // If daterange includes old stats
        if ($startDate->lt($oldStatsEndDate)) {
            $earnings += $this->creditsStats->getSellerEarnings($seller, $startDate, $endDate, $sitePayout, $downloadCountry);
        }

        // If daterange includes new stats
        if ($endDate->gte($oldStatsEndDate)) {
            $earnings += $this->unlimitedStats->getSellerEarnings($seller, $startDate, $endDate, $sitePayout, $downloadCountry);
        }

        return $earnings;
    }

    public function siteStats(Carbon $startDate, Carbon $endDate, $cache)
    {
        $oldStatsEndDate = $this->getOldStatsEndDate();

        $creditStats = null;

        $unlimitedStats = null;

        if ($startDate->lt($oldStatsEndDate)) {
            $creditStats = $this->creditsStats->siteStats($startDate, $endDate, $cache);
        }

        // If daterange includes new stats
        if ($endDate->gte($oldStatsEndDate)) {
            $unlimitedStats = $this->unlimitedStats->siteStats($startDate, $endDate, $cache);
        }

        $categories = Category::orderBy('order')->get();

        $response = [
            'categories' => [],

            'earnings' => $unlimitedStats['earnings'],
            'earnings_formatted' => formatMoney($unlimitedStats['earnings']),

            'old_earnings' => $creditStats['earnings'],
            'old_earnings_formatted' => formatMoney($creditStats['earnings']),

            'old_downloads' => $creditStats['downloads']
        ];

        foreach ($categories as $category) {
            $earnings = null;
            $downloadsPercentage = null;
            $oldEarnings = null;
            $oldDownloadsCount = null;
            $oldPercentage = null;

            if ($creditStats) {
                $oldEarnings = $creditStats['categories'][$category->slug]['earnings'];
                $oldDownloadsCount = $creditStats['categories'][$category->slug]['downloads'];
                $oldPercentage = $creditStats['categories'][$category->slug]['percentage'];
            }

            if ($unlimitedStats) {
                $earnings = $unlimitedStats['categories'][$category->slug]['earnings'];
                $downloadsPercentage = $unlimitedStats['categories'][$category->slug]['percentage'];
            }

            $response['categories'][] = [
                "id" => $category->id,
                "name" => $category->short_name,
                "slug" => $category->slug,

                "earnings" => $earnings,
                "earnings_formatted" => formatMoney($earnings),

                "old_earnings" => $oldEarnings,
                "old_earnings_formatted" => formatMoney($oldEarnings),

                "downloads_percentage" => $downloadsPercentage,
                "old_downloads_count" => $oldDownloadsCount,
                "old_percentage" => $oldPercentage
            ];
        }

        return $response;
    }

    public function sellerStats(User $seller, Carbon $startDate, Carbon $endDate, $cache = false)
    {
        $oldStatsEndDate = $this->getOldStatsEndDate();

        $totalOwed = $this->sellerPayout->getTotalRetainedPayouts($seller, $endDate->copy()->startOfMonth());

        $creditStats = null;

        $unlimitedStats = null;

        // If daterange includes old stats
        if ($startDate->lt($oldStatsEndDate)) {
            $creditStats = $this->creditsStats->sellerStats($seller, $startDate, $endDate, $cache);
        }

        // If daterange includes new stats
        if ($endDate->gte($oldStatsEndDate)) {
            $unlimitedStats = $this->unlimitedStats->sellerStats($seller, $startDate, $endDate, $cache);
        }

        $categories = Category::orderBy('order')->get();

        $earnings = $unlimitedStats['earnings'] + $creditStats['earnings'];

        $withHoldings = null;
        $total = $earnings + $totalOwed;

        // If theres an adjusted_total then replace the total and show the difference as withholdings
        if (isset($unlimitedStats['adjusted_total']) && $unlimitedStats['adjusted_total']) {
            $adjustedTotal = $unlimitedStats['adjusted_total'];

            $withHoldings = $total - $adjustedTotal;

            // if $withHoldings is negative, that means we owe that money to user. E.g. if withHolgins = -$5.20, we owe +$5.20 to seller. That's why we're inverting variable and adding it to totalOwed
            // if $withHoldings is positive it means the user owes motionarray money. (e.g. Tax)
            if ($withHoldings < 0) {
                $totalOwed += (-1 * $withHoldings);
                $withHoldings = null;
            }
            $total = $adjustedTotal;
        }

        $response = [
            'categories' => [],

            'earnings' => $unlimitedStats['earnings'],
            'earnings_formatted' => formatMoney($unlimitedStats['earnings']),

            'owed_earnings' => $totalOwed,
            'owed_earnings_formatted' => formatMoney($totalOwed),

            'old_earnings' => $creditStats['earnings'],
            'old_earnings_formatted' => formatMoney($creditStats['earnings']),

            'downloads_percentage' => $unlimitedStats['downloads_percentage'],
            'old_downloads' => $creditStats['downloads'],

            'withholdings' => $withHoldings,
            'withholdings_formatted' => formatMoney($withHoldings),

            'total' => $total,
            'total_formatted' => formatMoney($total)
        ];

        foreach ($categories as $category) {
            $oldEarnings = null;
            $oldDownloadsCount = null;
            $oldPercentage = null;
            $earnings = null;
            $downloadsPercentage = null;

            if ($creditStats) {
                $oldEarnings = $creditStats['categories'][$category->slug]['earnings'];
                $oldDownloadsCount = $creditStats['categories'][$category->slug]['downloads'];
                $oldPercentage = $creditStats['categories'][$category->slug]['percentage'];
            }

            if ($unlimitedStats) {
                $earnings = $unlimitedStats['categories'][$category->slug]['earnings'];
                $downloadsPercentage = $unlimitedStats['categories'][$category->slug]['percentage'];
            }

            $response['categories'][] = [
                "id" => $category->id,
                "name" => $category->short_name,
                "slug" => $category->slug,
                "earnings" => $earnings,
                "earnings_formatted" => formatMoney($earnings),
                "downloads_percentage" => $downloadsPercentage,
                "old_earnings" => $oldEarnings,
                "old_earnings_formatted" => formatMoney($oldEarnings),
                "old_downloads_count" => $oldDownloadsCount,
                "old_percentage" => $oldPercentage
            ];
        }

        return $response;
    }
}
