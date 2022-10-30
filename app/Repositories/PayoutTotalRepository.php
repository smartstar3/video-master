<?php

namespace MotionArray\Repositories;

use MotionArray\Repositories\PayoutTotal\PayoutTotalRepositoryException;
use Stripe_Charge;
use MotionArray\Models\PayoutTotal;
use Carbon\Carbon;
use DB;

class PayoutTotalRepository
{
    /**
     * @param $startDate
     * @param $endDate
     * @return float|int
     */
    public function update($startDate, $endDate)
    {
        $totalWeight = $this->getTotalWeightForPeriod($startDate, $endDate);

        if ($totalWeight === 0) {
            throw new PayoutTotalRepositoryException("Payout weight cannot be 0.");
        }

        $totalPayout = $this->getMarketplaceEarningsForPeriod($startDate, $endDate);

        if ($totalPayout === 0) {
            throw new PayoutTotalRepositoryException("Payout total cannot be 0.");
        }

        // Store the total payout info in the database
        $payout = PayoutTotal::firstOrNew([
            'month' => $startDate->month,
            'year' => $startDate->year
        ]);

        $payout->weight = $totalWeight;

        $payout->amount = $totalPayout;

        $payout->save();

        return $payout;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return float|int
     */
    public function getMarketplaceEarningsForPeriod($startDate, $endDate)
    {
        $totalPayout = $this->getTotalStripeEarningsForPeriod($startDate, $endDate);

        // Calculate 50% of total revenue (to 2 decimal places)
        $totalPayout = round($totalPayout / 2, 2);

        return $totalPayout;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return float|int
     */
    public function getTotalStripeEarningsForPeriod($startDate, $endDate)
    {
        $transactionBaseCost = 30;

        $stripePercentFee = 2.55 / 100;

        $totalPayout = 0;

        $has_more = true;

        $last_id = null;

        $now = Carbon::now();

        if ($now->lt($endDate)) {
            $endDate = $now;
        }

        while ($has_more === true) {
            $charges = Stripe_Charge::all([
                'created' => [
                    'gte' => $startDate->timestamp,
                    'lte' => $endDate->timestamp
                ],

                'limit' => 100,
                'starting_after' => $last_id
            ]);

            foreach ($charges["data"] as $charge) {
                if ($charge->created < $startDate->timestamp) {
                    break 2;
                }

                if ($charge->paid === true) {
                    $transactionPercentCost = $charge->amount * $stripePercentFee;

                    $totalPayout += ($charge->amount - $transactionPercentCost - $transactionBaseCost);
                }

                if ($charge->refunded === true) {
                    $transactionPercentCost = $charge->amount_refunded * $stripePercentFee;

                    $totalPayout -= ($charge->amount_refunded - $transactionPercentCost);
                }

                $last_id = $charge->id;
            }

            echo 'Total: '. $totalPayout . ' Last payment: '. $last_id . ' (' . Carbon::createFromTimestamp($charge->created)->toDateTimeString() . ')' . "\n";

            $has_more = $charges["has_more"];
        }

        $totalPayout = round($totalPayout) / 100;

        return $totalPayout;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getTotalWeightForPeriod($startDate, $endDate)
    {
        $totalWeight = DB::table('downloads')
            ->join('products', function ($join) {
                $join->on('downloads.product_id', '=', 'products.id');
                // Exclude downloads where the seller downloaded their own products
                $join->on('products.seller_id', '!=', 'downloads.user_id');
            })
            ->where('downloads.free', '=', 0)
            ->where('downloads.first_downloaded_at', '>=', $startDate)
            ->where('downloads.first_downloaded_at', '<', $endDate)
            ->whereRaw('downloads.user_id NOT IN (SELECT user_id FROM users_excluded_from_payout_calculations)')
            ->sum('downloads.weight');

        return $totalWeight;
    }
}
