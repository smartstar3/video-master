<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use HavocInspired\Cashier\Customer;
use Config;

class SetStripeCreatedAt extends Command
{
    protected $signature = 'motionarray-onetime:set-stripe-created-at
        {--origin=database : whether to get the list of users from database or stripe}
        {--offset=0 : which offset to start from, for parallel processing}
        {--limit=1000000 : how many records to process, for parallel processing}
        ';

    protected $description = 'Update user.stripe_created_at';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $origin = $this->option("origin");
        $offset = $this->option("offset") * 1;
        $limit = $this->option("limit") * 1;
        if (!in_array($origin, ['stripe', 'database'])) {
            $this->error("Origin can only be Database or Stripe.");
            return -1;
        }
        if ($origin == 'stripe')
            $this->fromStripe();
        else
            $this->fromDatabase($offset, $limit);
    }

    /**
     * Go through our database, find users that have stripe_id but no stripe_created_at,
     * fetch their created at timestamp from Stripe one by one.
     *
     * Works best for small number of users
     *
     * @return void
     */
    public function fromDatabase($offset, $limit)
    {
        $count = 0;

        $query = User::withTrashed()->whereNotNull('stripe_id')->whereNull('stripe_created_at');
        $this->info($query->count() . " users with stripe_id and no stripe_created_at found.");

        $query->chunkById(100, function ($users) use (&$offset, &$count, $limit) {
            foreach ($users as $user) {
                if ($count >= $limit) return false;

                if ($offset > 0) {
                    $offset--;
                    continue;
                }
                $customer = Customer::retrieve($user->stripe_id, config('services.stripe.secret'));

                $user->stripe_created_at = \Carbon\Carbon::createFromTimestamp($customer->created);
                $user->save();
                $count++;

                $this->info("[{$count}] User {$user->email} ({$user->id}) updated to " . date('Y-m-d H:i:s', $user->stripe_created_at));
            }
        });

        $this->info("{$count} user stripe_created_at updated.");
    }

    /**
     * Go through all Stripe users, 50 by 50, update their countries in the DB
     *
     * Works best for bulk users
     * @return void
     */
    public function fromStripe()
    {
        \Stripe::setApiKey(Config::get('services.stripe.secret'));

        $query = User::withTrashed()->whereNotNull('stripe_id')->whereNull('stripe_created_at');

        $count = 0;
        $stripe_count = 0;
        $not_found_count = 0;

        $this->info($query->count() . " users with stripe_id and no stripe_created_at found.");

        do {
            $this->info("Fetching 50 more stripe users (total: {$stripe_count}).");
            $customers = \Stripe_Customer::all([
                "limit" => 50,
            ]);
            $stripe_count += count($customers["data"]);

            foreach ($customers["data"] as $customer) {
                $user = User::where('stripe_id', '=', $customer->id)->whereNull('stripe_created_at')->first();
                if (!$user) {
                    /// Too many, lets not output error, just count.
                    // $this->error("Stripe customer '{$customer->id}' not found in the database.");
                    $not_found_count++;
                    continue;
                }

                $user->stripe_created_at = $customer->created;
                $user->save();

                $count++;

                $this->info("[{$count}] User {$user->email} ({$user->id}) updated to " . date('Y-m-d H:i:s', $user->stripe_created_at));
            }

        } while ($customers["has_more"]);

        $this->info("{$count} user stripe_created_at updated.");
        if ($not_found_count) {
            $this->error("{$not_found_count} stripe customers not found in the database.");
        }
    }
}
