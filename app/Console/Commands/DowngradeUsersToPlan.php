<?php namespace MotionArray\Console\Commands;

use Bugsnag\Report;
use Illuminate\Console\Command;
use MotionArray\Models\BillingAction;
use Carbon\Carbon;
use MotionArray\Repositories\UserSubscriptionRepository;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalCancelationException;

class DowngradeUsersToPlan extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:downgrade-users-to-plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downgrade all users with scheduled downgrades';

    protected $userSubscription;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserSubscriptionRepository $userSubscriptionRepository)
    {
        $this->userSubscription = $userSubscriptionRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $startDate = Carbon::now()->subDays(1);
        $endDate = Carbon::now()->addHours(2);

        $billingActions = BillingAction::downgrades()
            ->where('actionable_at', '<', $endDate)
            ->where('actionable_at', '>', $startDate)
            ->get();

        $this->info('There are '.$billingActions->count().' downgrades between '.$startDate.' - '.$endDate);

        foreach ($billingActions as $billingAction) {
            $user = $billingAction->user;

            $plan = $billingAction->plan;

            try {
                if ($billingAction->change_to_billing_id === 'free') {
                    $this->userSubscription->cancelSubscription($user);
                } else {
                    $this->userSubscription->downgradeToPlan($user, $plan);
                }
            } catch (PaypalCancelationException $exception) {
                // Command should continue working for other users. That's why we're catching error and notify Bugsnag.
                \Bugsnag::notifyException($exception, function(Report $report) use ($user, $plan) {
                    $report->setUser($user->toArray())
                        ->setMetaData([
                            'plan_id' => $plan->id,
                        ]);
                });
            }
        }

        return $billingActions;
    }
}
