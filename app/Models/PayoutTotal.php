<?php namespace MotionArray\Models;

use Carbon\Carbon;
use Stripe;
use Stripe_Transfer;
use Stripe_Invoice;

class PayoutTotal extends BaseModel
{
    protected $table = 'payout_totals';

    protected $fillable = ['month', 'year', 'amount'];

    protected $hidden = ['weight'];

    /**
     * Fetches the total payout to sellers for the given month.
     */
    public static function getTotalPayoutForMonth($month, $year)
    {
        // Retrieve total payout data from database.
        $payout = PayoutTotal::where('month', '=', $month)
            ->where('year', '=', $year);

        return $payout->count() ? $payout->first()->amount : 0;
    }

}
