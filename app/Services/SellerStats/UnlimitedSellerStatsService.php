<?php

namespace MotionArray\Services\SellerStats;

use Carbon\Carbon;
use MotionArray\Models\Category;
use MotionArray\Models\Country;
use MotionArray\Models\Download;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\User;
use DB;

class UnlimitedSellerStatsService extends BaseSellerStatsService implements SellerStatsInterface
{
    public function siteStats(Carbon $startDate, Carbon $endDate, $cache = null)
    {
        $categories = Category::orderBy('order', 'ASC')->get();

        $stats = [];

        $totalPayout = PayoutTotal::getTotalPayoutForMonth($startDate->month, $startDate->year);

        $categoryWeights = $this->download->getWeightForPeriodByCategory($startDate, $endDate);

        $totalWeight = $this->download->totalWeight($startDate, $endDate);

        foreach ($categories as $category) {
            $totalCategoryWeight = $categoryWeights->where('category_id', $category->id)->first();

            $percentage = 0;

            $earnings = 0;

            if ($totalCategoryWeight && $totalCategoryWeight->weight) {
                $earnings = ($totalPayout / $totalWeight) * $totalCategoryWeight->weight;

                if ($totalPayout) {
                    $percentage = ($earnings * 100) / $totalPayout;
                }
            }

            $stats['categories'][$category->slug] = [
                'earnings' => $earnings,
                'percentage' => floor($percentage * 100) / 100
            ];
        }

        $stats['earnings'] = $totalPayout;

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
        if ($sitePayout->updated_at->lt($endDate)) {
            $endDate = $sitePayout->updated_at;
        }

        $query = Download::where('first_downloaded_at', '>=', $startDate)
            ->where('first_downloaded_at', '<=', $endDate) // This was previously $sitePayout->updated_at, causing a bug
            ->where('free', '=', 0)
            // Exclude downloads where the seller downloaded their own products
            ->where('user_id', '!=', $seller->id)
            ->whereRaw('downloads.user_id NOT IN (SELECT user_id FROM users_excluded_from_payout_calculations)')
            ->whereIn('product_id', function ($query) use ($seller) {
                $query->select('id')
                    ->from('products')
                    ->where('owned_by_ma', '=', 0)
                    ->where('seller_id', '=', $seller->id);
            });

        // If $downloadCountry is set, only match downloads from a certain country
        if ($downloadCountry !== null) {
            $query->whereIn('user_id', function ($query) use ($downloadCountry) {
                $query->select('id')
                    ->from('users')
                    ->where('country_id', '=', $downloadCountry->id);
            });
        }

        $sellerWeight = $query->sum('weight');

        if ($sitePayout->weight == 0) { // Prevent division by zero
            return 0;
        }

        return ($sitePayout->amount / $sitePayout->weight) * $sellerWeight;
    }

    public function sellerStats(User $seller, Carbon $startDate, Carbon $endDate, $cache = null)
    {
        // Get the seller payout
        $sellerPayoutRepository = app('MotionArray\Repositories\SellerPayoutRepository');

        $sellerPayout = $sellerPayoutRepository->getSellerPayoutForPeriod($seller, $startDate);


        $sitePayout = PayoutTotal::getTotalPayoutForMonth($startDate->month, $startDate->year);

        $categories = Category::orderBy('order', 'ASC')->get();

        // Fetch the Stripe payout
        $stats = [];
        $stats['seller_id'] = $seller->id;
        $stats['categories'] = [];

        $totalEarnings = 0;

        $categoryWeights = $this->download->getWeightForPeriodByCategory($startDate, $endDate);

        $sellerCategoryWeights = $this->download->getWeightForPeriodByCategory($startDate, $endDate, $seller);

        $totalWeight = $this->download->totalWeight($startDate, $endDate);

        foreach ($categories as $category) {

            $sellerCategoryWeight = $sellerCategoryWeights->where('category_id', $category->id)->first();

            $earnings = 0;

            if ($sellerCategoryWeight) {
                $earnings = ($sitePayout / $totalWeight) * $sellerCategoryWeight->weight;
            }

            $stats['categories'][$category->slug] = [
                'name' => $category->name,
                'short_name' => $category->short_name,
                'slug' => $category->slug,
                'earnings' => $earnings,
                'percentage' => 0
            ];

            $totalEarnings += $earnings;
        }

        if ($sellerPayout && $sellerPayout->adjusted_payout) {
            $stats['adjusted_total'] = $sellerPayout->adjusted_payout;
        }

        $stats['downloads_percentage'] = 0;
        $stats['earnings'] = $totalEarnings;

        return $stats;
    }
}
