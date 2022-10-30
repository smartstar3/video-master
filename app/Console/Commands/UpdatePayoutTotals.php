<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App;
use Carbon\Carbon;
use DB;
use Artisan;
use MotionArray\Repositories\PayoutTotalRepository;

class UpdatePayoutTotals extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:update-payout-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the payout total saved in the DB.';

    /**
     * @var PayoutTotalRepository
     */
    protected $payoutTotal;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PayoutTotalRepository $payoutTotal)
    {
        $this->payoutTotal = $payoutTotal;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Fetch total revenue from Stripe.
        \Stripe::setApiKey(Config::get('services.stripe.secret'));

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $payout = $this->payoutTotal->update($startDate, $endDate);

        $this->info("Updated payout data for: " . $startDate->month . "/" . $startDate->year . "   -   $" . $payout->amount);

        Artisan::call('motionarray:update-product-earnings');
    }
}
