<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Models\Country;
use MotionArray\Repositories\SubmissionRepository;
use Exception;
use HavocInspired\Cashier\Customer;
use Config;

class UpdateCountryIdFromStripe extends Command
{
    protected $signature = 'motionarray-onetime:update-country-ids-from-stripe
        {--origin=database : whether to get the list of users from database or stripe}
        {--offset=0 : which offset to start from, for parallel processing}
        {--limit=1000000 : how many records to process, for parallel processing}
        ';

    protected $description = 'Update country ids for users based on their Stripe credit card';

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
        if (!in_array($origin,['stripe','database']))
        {
            $this->error("Origin can only be Database or Stripe.");
            return -1;
        }
        if ($origin == 'stripe')
            $this->fromStripe();
        else
            $this->fromDatabase($offset, $limit);
    }
    /**
     * Go through our database, find users that have stripe_id but no country_id,
     * fetch their countries from Stripe one by one.
     *
     * Works best for small number of users
     *
     * @return void
     */
    public function fromDatabase($offset, $limit)
    {
        $count = 0;

        $users = User::withTrashed()->whereNotNull('stripe_id')->whereNull('country_id');
        $this->info($users->count(). " users with stripe_id and no country_id found.");

        $users = User::withTrashed()->whereNotNull('stripe_id')->whereNull('country_id')->chunkById(100, function ($users) use(&$offset, &$count, $limit) {
            foreach ($users as $user) {
                if ($count>=$limit) return false;

                if ($offset > 0)
                {
                    $offset --;
                    continue;
                }
                $customer = Customer::retrieve($user->stripe_id, config('services.stripe.secret'));
                if (!@$customer->cards)
                {
                    $this->error("Stripe user '{$user->stripe_id}' has no stored card.");
                    continue;
                }
                $card = $customer->cards->retrieve($customer->default_card);
                $country_code = $card->country;
                try {
                    $country_id = Country::byCode($country_code)->id;
                }
                catch (\Exception $e) {
                    $country_id = null;
                }
                if (!$country_id)
                {
                    $this->error("Country '{$country_code}' not found.");
                    continue;
                }

                $user->country_id = $country_id;
                $user->save();

                $count ++;
            }
        });


        $this->info("{$count} user countries updated.");
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

        $users = User::whereNotNull('stripe_id')->whereNull('country_id');

        $count = 0;
        $stripe_count = 0;
        $not_found_count = 0;

        $this->info($users->count(). " users with stripe_id and no country_id found.");

        do {
            $this->info("Fetching 50 more stripe users (total: {$stripe_count}).");
            $customers = \Stripe_Customer::all([
                "limit" => 50,
            ]);
            $stripe_count += count($customers["data"]);

            foreach ($customers["data"] as $customer) {
                $user = User::where('stripe_id', '=', $customer->id)->whereNull('country_id')->first();
                if (!$user) {
                    /// Too many, lets not output error, just count.
                    // $this->error("Stripe customer '{$customer->id}' not found in the database.");
                    $not_found_count++;
                    continue;
                }

                $card = $customer->cards->retrieve($customer->default_card);

                if (!$card) {
                    $this->error("Stripe user '{$user->stripe_id}' has no stored card.");
                    continue;
                }

                $country_code = $card->country;

                $country_id = Country::byCode($country_code)->id;

                if (!$country_id) {
                    $this->error("Country '{$country_code}' not found.");
                    continue;
                }

                $user->country_id = $country_id;
                $user->save();

                $count++;
            }

        } while ($customers["has_more"]);

        $this->info("{$count} user countries updated.");
        if ($not_found_count)
            $this->error("{$not_found_count} stripe customers not found in the database.");
    }
}
