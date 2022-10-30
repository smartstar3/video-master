<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use MotionArray\Models\Country;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\Seller;
use MotionArray\Models\SellerPayout;
use MotionArray\Models\User;
use MotionArray\Services\SellerStats\SellerStatsService;

class SellerPayoutRepository
{
    protected $minimumPayout = 50;
    public $earning_countries = ['US']; // Countries we want to calculate earnings for separately.

    /**
     * Calculate seller earnings for a month per country and globally, and store in SellerPayout model/table
     *
     * @param User $seller
     * @param Carbon $startDate
     * @return SellerPayout global payout
     */
    public function update(User $seller, Carbon $startDate)
    {
        $startDate = $startDate->startOfMonth();

        $endDate = $startDate->copy()->endOfMonth();

        $sellerStats = app('MotionArray\Services\SellerStats\SellerStatsService');

        $sitePayout = PayoutTotal::where([
            'month' => $startDate->month,
            'year' => $startDate->year
        ])->first();

        // Calculate total earnings (month) for each seller and store in the seller_payouts table
        $earningsGlobal = $sellerStats->getSellerEarnings($seller, $startDate, $endDate, $sitePayout);

        // Store in the DB.
        $payout = SellerPayout::firstOrNew([
            'user_id' => $seller->id,
            'period_start_at' => $startDate,
            'country_id' => null // This is the global income.
        ]);

        $payout->amount = $earningsGlobal;
        $payout->save();

        // Generate seller payouts by country
        $earningsByCountry = $payoutByCountry = [];
        foreach ($this->earning_countries as $countryCode) {
            $country = Country::byCode($countryCode);
            $earningsByCountry[$countryCode] = $sellerStats->getSellerEarnings($seller, $startDate, $endDate, $sitePayout, $country);
            $payoutByCountry[$countryCode] = SellerPayout::firstOrNew([
                'user_id' => $seller->id,
                'period_start_at' => $startDate,
                'country_id' => $country->id
            ]);
            $countryPayout = &$payoutByCountry[$countryCode];
            $countryPayout->amount = $earningsByCountry[$countryCode];
            $countryPayout->country_id = $country->id;
            $countryPayout->save();
        }

        return $payout;
    }

    /**
     * Find Earnings for Period
     *
     * @param $seller
     * @param $startDate
     * @return mixed
     */
    public function getSellerPayoutForPeriod($seller, $startDate)
    {
        $payout = SellerPayout::where('user_id', '=', $seller->id)
            ->whereDate('period_start_at', '=', $startDate)
            ->first();

        if ($payout) {
            return $payout;
        }

        return null;
    }

    /**
     * @param User $seller
     * @param null $endDate
     * @return Collection
     */
    function getRetainedPayouts(User $seller, $endDate = null)
    {
        $query = SellerPayout::where('user_id', '=', $seller->id)
            ->whereNull('country_id')
            ->orderBy('period_start_at', 'ASC');

        if ($endDate) {
            $query->where('period_start_at', '<', $endDate);
        }

        $payouts = $query->get();

        // Keep track of the total of retained payouts.
        $retained = 0;
        $heldPayouts = new Collection();

        foreach ($payouts as $i => $payout) {
            $retained += $payout->amount;
            $heldPayouts->push($payout);

            if ($retained >= $this->getMinumumPayoutAmount()) {
                // Reset the retained total if it has gone above $50
                $retained = 0;
                $heldPayouts = new Collection();
            }
        }

        return $heldPayouts;
    }

    function getMinumumPayoutAmount()
    {
        return $this->minimumPayout;
    }

    /**
     * Calculate the total of payouts owed to seller.
     *
     * @param  User $seller The seller
     * @param  Datetime $end_date The date that payouts should be calculated up to.
     *
     * @return Float
     */
    public function getTotalRetainedPayouts($seller, $endDate)
    {
        $retainedPayouts = $this->getRetainedPayouts($seller, $endDate);

        $totalRetained = 0;

        foreach ($retainedPayouts as $retainedPayout) {
            $totalRetained += $retainedPayout->amount;
        }

        return $totalRetained;
    }

    private function getPayoutsQuery(Carbon $start_date, Carbon $end_date, string $provider)
    {
        return SellerPayout::whereBetween("period_start_at", [$start_date, $end_date])
            ->join('users', 'users.id', '=', 'seller_payouts.user_id')
            ->where('payout_method', '=', $provider);
    }

    /**
     * Get payouts to a seller, filtered by provider, between a time range
     * Called when generating CSV.
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param string $provider
     * @return SellerPayout
     */
    public function getPayoutsByProvider(Carbon $start_date, Carbon $end_date, string $provider)
    {
        $sellerPayoutQuery = $this->getPayoutsQuery($start_date, $end_date, $provider);
        $sellerPayoutQuery = $sellerPayoutQuery->whereNull('seller_payouts.country_id'); // country_id NULL means global payout

        return $sellerPayoutQuery->get();
    }

    /**
     * Get payouts for a seller, filtered by provider and country, between a time range
     * Called when generating CSV.
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param string $provider
     * @param Country $country the country to filter by
     * @return SellerPayout
     */
    public function getPayoutsByProviderAndCountry(Carbon $start_date, Carbon $end_date, string $provider, Country $country)
    {
        $sellerPayoutQuery = $this->getPayoutsQuery($start_date, $end_date, $provider);
        $sellerPayoutQuery->where('seller_payouts.country_id', '=', $country->id);

        return $sellerPayoutQuery->get();
    }
}
