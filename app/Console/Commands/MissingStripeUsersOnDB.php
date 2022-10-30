<?php namespace MotionArray\Console\Commands;

use HavocInspired\Cashier\Stripe\StripeSubscription;
use Illuminate\Console\Command;
use App, Config;
use MotionArray\Models\User;

class MissingStripeUsersOnDB extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:missing-stripe-users-on-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds any stripe user that doesnt exists on the DB';

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
        \Stripe::setApiKey(Config::get('services.stripe.secret'));

        $missingIds = [];

        $subscriptionsCount = StripeSubscription::all([
            "limit" => 1,
            "include[]" => "total_count"
        ]);

        $limit = 50;
        $has_more = true;
        $subscription = null;

        $pages = ceil($subscriptionsCount->total_count / $limit);

        $bar = $this->output->createProgressBar($pages);

        while ($has_more) {
            $subscriptions = StripeSubscription::all([
                'limit' => $limit,
                'starting_after' => $subscription
            ]);

            $customerIds = [];
            $subscriptionsIds = [];

            foreach ($subscriptions["data"] as $subscription) {
                $customerIds[] = $subscription->customer;
                $subscriptionsIds[] = $subscription->id;
            }

            $existingIds = User::whereIn('stripe_id', $customerIds)->pluck('stripe_id')->toArray();
            $existingSubscriptionIds = User::whereIn('stripe_subscription', $subscriptionsIds)->pluck('stripe_subscription')->toArray();

            $missingCustomerIds = array_diff($customerIds, $existingIds);
            $missingSubscriptionsIds = array_diff($subscriptionsIds, $existingSubscriptionIds);

            foreach ($missingCustomerIds as $missingCustomerId) {
                $this->info("User " . $missingCustomerId . " NOT FOUND \n");
            }

            foreach ($missingSubscriptionsIds as $missingSubscriptionsId) {
                $this->info("Subscription " . $missingSubscriptionsId . " NOT FOUND \n");
            }

            $has_more = $subscriptions["has_more"];

            $bar->advance();
        };

        $bar->finish();
    }
}
