<?php

namespace MotionArray\Services\SellerStats;

use MotionArray\Models\SellerPayout;
use MotionArray\Models\Category;
use MotionArray\Models\Country;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\User;
use Carbon\Carbon;

class CreditsSellerStatsService extends BaseSellerStatsService implements SellerStatsInterface
{
    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param null $cache
     * @return array
     */
    public function siteStats(Carbon $startDate, Carbon $endDate, $cache = null)
    {
        $categories = Category::orderBy('order', 'ASC')->get();

        $stats = [];

        $totalPayout = PayoutTotal::getTotalPayoutForMonth($startDate->month, $startDate->year);

        $totalDownloadsByCategory = $this->download->globalDownloadsByCategory($startDate, $endDate, $cache);

        $totalDownloads = $totalDownloadsByCategory->sum('count');

        foreach ($categories as $category) {
            $categoryDownloads = $totalDownloadsByCategory->where('category_id', $category->id)->first();

            $downloadsCount = $categoryDownloads->count ?? 0;

            $percentage = 0;

            $earnings = 0;

            if ($totalDownloads) {
                $percentage = ($downloadsCount * 100) / $totalDownloads;
                $earnings = ($downloadsCount * $totalPayout) / $totalDownloads;
            }

            $stats['categories'][$category->slug] = [
                'earnings' => $earnings,
                'downloads' => $downloadsCount,
                'percentage' => floor($percentage * 100) / 100
            ];
        }

        $stats['earnings'] = $totalPayout;
        $stats['downloads'] = $totalDownloads;

        return $stats;
    }

    /**
     * Calculate income for a particular seller
     *
     * @param User $seller
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Country $downloadCountry only calculate income from a particular country
     * @return float
     */
    public function getSellerEarnings(User $seller, Carbon $startDate, Carbon $endDate, PayoutTotal $sitePayout, Country $downloadCountry = null)
    {
        // Note: country based measurements are not tested, and are technically not needed. Just added for signature compatibility.
        if ($downloadCountry) {
            $totalSales = $this->download->getCountrySalesCount($startDate, $endDate, $downloadCountry);
            $userSales = $this->download->getSalesCountBySellerAndCountry($seller, $startDate, $endDate, $downloadCountry);
        } else {
            $totalSales = $this->download->getGlobalSalesCount($startDate, $endDate);
            $userSales = $this->download->getSalesCountBySeller($seller, $startDate, $endDate);
        }

        if (!$totalSales) {
            return 0;
        }

        return ($sitePayout->amount / $totalSales) * $userSales;
    }

    /**
     * @param User $seller
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param null $cache
     * @return array
     */
    public function sellerStats(User $seller, Carbon $startDate, Carbon $endDate, $cache = null)
    {
        // Fetch the Stripe payout
        $sitePayout = PayoutTotal::getTotalPayoutForMonth($startDate->month, $startDate->year);
        $categories = Category::orderBy('order', 'ASC')->get();

        $stats = [];
        $stats['start_date'] = $startDate;
        $stats['end_date'] = $endDate;
        $stats['seller_id'] = $seller->id;
        $stats['categories'] = [];

        $totalDownloads = 0;

        $totalEarnings = 0;

        $payout = SellerPayout::where('user_id', '=', $seller->id)
            ->where('period_start_at', '=', $startDate)
            ->first();

        if ($payout) {
            $totalEarnings = $payout->amount;
        }

        $sellerDownloadsByCategory = $this->getSellerDownloadsByCategory($seller, $startDate, $endDate, $cache);

        $totalDownloadsByCategory = $this->download->globalDownloadsByCategory($startDate, $endDate, $cache);

        $total_downloads = $totalDownloadsByCategory->sum('count');

        foreach ($categories as $category) {
            $sellerDownloads = $sellerDownloadsByCategory->where('category_id', $category->id)->first();

            $categoryDownloads = $totalDownloadsByCategory->where('category_id', $category->id)->first();

            $categoryPayout = $category->calculatePayoutTotal($sitePayout, $total_downloads, $categoryDownloads);
            $earnings = self::sellerCategoryPayout($categoryPayout, $categoryDownloads, $sellerDownloads);

            $stats['categories'][$category->slug] = [
                'name' => $category->name,
                'short_name' => $category->short_name,
                'slug' => $category->slug,
                'percentage' => 0,
                'downloads' => $sellerDownloads ? $sellerDownloads->count : 0,
                'earnings' => $earnings,
            ];

            $totalDownloads = $totalDownloads + ($sellerDownloads ? $sellerDownloads->count : 0);
//            $totalEarnings = $totalEarnings + $earnings;
        }

        $stats['downloads'] = $totalDownloads;
        $stats['earnings'] = $totalEarnings;

        return $stats;
    }

    public static function sellerCategoryPayout($categoryPayout, $category_downloads, $seller_downloads)
    {
        if ($category_downloads) {
            $seller_percentage = $seller_downloads ? (((100 / $category_downloads->count) * $seller_downloads->count) / 100) : 0;

            return $categoryPayout * $seller_percentage;
        }
    }
}
