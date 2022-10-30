<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use App;
use Carbon\Carbon;
use MotionArray\Models\User;
use MotionArray\Repositories\SellerPayoutRepository;

class UpdateSellerPayoutTotals extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:update-seller-payout-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the payout total saved in the DB for all seller.';

    protected $sellerPayout;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SellerPayoutRepository $sellerPayout)
    {
        $this->sellerPayout = $sellerPayout;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Calculates the total seller payouts for the previous month and stores them
     * in the database.
     *
     * @return mixed
     */
    public function handle()
    {
        // Calculate stats for previous month.
        $startDate = Carbon::now()->subMonth()->startOfMonth();

        User::withTrashed()->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->where('user_role.role_id', '=', 3)
            ->select('users.*')
            ->chunk(50, function ($sellers) use ($startDate) {
                // Loop sellers
                foreach ($sellers as $seller) {
                    $sellerPayout = $this->sellerPayout->update($seller, $startDate);

                    $this->info("- " . $seller->company_name . " ($" . $sellerPayout->amount . ")");
                }
            });
    }
}
