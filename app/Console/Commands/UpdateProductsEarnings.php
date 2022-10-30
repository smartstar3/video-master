<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App;
use Carbon\Carbon;
use Stripe_Charge;
use MotionArray\Models\PayoutTotal;
use DB;

class UpdateProductsEarnings extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:update-product-earnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currents month product Earnings.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $payoutTotal = PayoutTotal::where('month', $monthStart->month)
            ->where('year', $monthStart->year)
            ->first();

        if ($payoutTotal) {
            $this->updateProductEarnings($payoutTotal, $monthStart, $monthEnd);
        }
    }

    public function updateProductEarnings(PayoutTotal $payoutTotal, Carbon $monthStart, Carbon $monthEnd)
    {
        $EarningByWeight = $payoutTotal->amount / $payoutTotal->weight;

        $now = Carbon::now();

        DB::table('downloads')
            ->select(DB::raw('products.*, downloads.first_downloaded_at, SUM(downloads.weight) as total_weight'))
            ->where('downloads.free', '=', '0')
            ->where('downloads.first_downloaded_at', '>=', $monthStart)
            ->where('downloads.first_downloaded_at', '<=', $monthEnd)
            ->whereRaw('downloads.user_id NOT IN (SELECT user_id FROM users_excluded_from_payout_calculations)')
            ->join('products', function($join) {
                $join->on('downloads.product_id', '=', 'products.id');
                // Exclude downloads where the seller downloaded their own products
                $join->on('products.seller_id', '!=', 'downloads.user_id');
            })
            ->groupBy('downloads.product_id')
            ->orderBy('products.id')
            ->chunk(10000, function($products) use ($now, $EarningByWeight, $monthStart, $monthEnd) {

                foreach ($products as $product) {
                    $productEarnings = $product->total_weight * $EarningByWeight;

                    $find = [
                        'product_id' => $product->id,
                        'period_start' => $monthStart,
                        'period_end' => $monthEnd,
                    ];

                    $update = [
                        'category_id' => $product->category_id,
                        'seller_id' => $product->seller_id,
                        'earnings' => floor($productEarnings * 100),
                        'updated_at' => $now
                    ];

                    DB::table('t_product_earnings_by_month')
                        ->updateOrInsert($find, $update);
                }
            });

        echo("Updated product earnings data for: " . $monthStart->month . "/" . $monthEnd->year);
    }
}
