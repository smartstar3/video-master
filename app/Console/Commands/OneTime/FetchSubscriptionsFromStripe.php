<?php

namespace MotionArray\Console\Commands\OneTime;

use Carbon\Carbon;
use Config;
use HavocInspired\Cashier\Stripe\StripeSubscription;
use Illuminate\Console\Command;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\User;
use MotionArray\Services\Subscription\StripeStatusParser;
use Stripe_Invoice;

class FetchSubscriptionsFromStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray-onetime:fetch-subscriptions-from-stripe
        {--offset=0 : which offset to start from, for parallel processing}
        {--limit=1000000 : how many records to process, for parallel processing}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a `subscriptions` table record for each active subscription on Stripe.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $offset = $this->option('offset') * 1;
        $limit = $this->option('limit') * 1;

        $this->fetchSubscriptionsBasedOnDatabase($offset, $limit);
    }

    private function fetchSubscriptionsBasedOnDatabase(int $offset, int $limit)
    {
        \Stripe::setApiKey(Config::get('services.stripe.secret'));
        \Stripe::setApiVersion('2019-05-16');

        $plans = Plan::pluck('id', 'billing_id')->toArray();
        $count = 0;
        $query = User::withTrashed()->whereNotNull('stripe_id')->doesntHave('subscriptions');
        $this->info($query->count().' users with stripe_id and no record on subscriptions table');
        $query->chunkById(100, function ($users) use (&$offset, &$count, $limit, $plans) {
            /** @var User $user */
            foreach ($users as $user) {
                if ($count >= $limit) {
                    return false;
                }

                if ($offset > 0) {
                    --$offset;
                    continue;
                }

                $invoices = Stripe_Invoice::all([
                    'customer' => $user->stripe_id,
                    'status' => 'paid',
                ], config('services.stripe.secret'))->data;
                $invoicesCollection = collect([]);
                /** @var Stripe_Invoice $invoice */
                foreach ($invoices as $invoice) {
                    $invoicesCollection->push($invoice->__toArray());
                }
                $charges = \Stripe_Charge::all([
                    'customer' => $user->stripe_id,
//                    'status' => 'paid',
                ], config('services.stripe.secret'))->data;
                $chargesCollection = collect([]);
                /** @var \Stripe_Charge $charge */
                foreach ($charges as $charge) {
                    $chargesCollection->push($charge->__toArray());
                }

                $subscriptions = StripeSubscription::all([
                    'customer' => $user->stripe_id,
                    'status' => 'all',
                ])->data;

                $paymentsCount = 0;
                /** @var \Stripe_Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    $subscriptionArray = $subscription->__toArray();
                    $stripePlan = $subscriptionArray['plan']->__toArray();

                    $userSubscription = $user->subscriptions()->create([
                        'plan_id' => $plans[$stripePlan['id']],
                        'payment_gateway_id' => PaymentGateways::STRIPE_ID,
                        'payment_gateway_customer_id' => $subscriptionArray['customer'],
                        'payment_gateway_subscription_id' => $subscriptionArray['id'],
//                        'start_at' => Carbon::createFromTimestamp($subscriptionArray['created']),
                        'start_at' => Carbon::createFromTimestamp($subscriptionArray['start']),
                        'end_at' => $subscriptionArray['current_period_end'] ? Carbon::createFromTimestamp($subscriptionArray['current_period_end']) : null,
                        'subscription_status_id' => app(StripeStatusParser::class)->parseSubscriptionStatus($subscriptionArray['status']),
                    ]);

                    $invoices = $invoicesCollection->where('subscription', $subscriptionArray['id']);
                    foreach ($invoices as $invoice) {
                        $charges = $chargesCollection->where('invoice', $invoice['id']);

                        foreach ($charges as $charge) {
                            ++$paymentsCount;
                            $userSubscription->payments()->create([
                                'attempted_at' => $charge['created'],
                                'gateway_payment_id' => $charge['id'],
                                'amount' => $charge['amount'],
                                'fee' => $charge['application_fee'],
                                'subscription_payment_status_id' => app(StripeStatusParser::class)->parsePaymentStatus($charge['status']),
                            ]);
                        }
                    }
                }

                ++$count;
                $this->info("[{$count}] User {$user->email} ({$user->id}): (".count($subscriptions).") Subscriptions / ({{$paymentsCount}}) Payments");
            }
        });

        $this->info("{$count} user's subscriptions updated.");
    }
}
