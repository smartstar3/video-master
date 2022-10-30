<?php namespace MotionArray\Console\Commands\OneTime;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\Plan;
use MotionArray\Models\User;
use MotionArray\Repositories\BillingActionRepository;
use Exception;
use MotionArray\Repositories\PlanRepository;
use MotionArray\Repositories\UserRepository;

class ScheduleDowngradesForOldPlans extends Command
{
    protected $name = 'motionarray-onetime:schedule-downgrades-for-old-plans';

    protected $description = 'Schedules a forced downgrade to unlimited monthly plan';

    protected $user;
    protected $plan;
    protected $billingAction;

    public function __construct(UserRepository $user, PlanRepository $plan, BillingActionRepository $billingAction)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->billingAction = $billingAction;

        parent::__construct();
    }

    public function handle()
    {
        /**
         * Downgrade yearly plans
         **/
        $yearlyUnlimited = Plan::where('billing_id', '=', 'yearly_unlimited_2018')->first();

        $yearlyPlanIds = Plan::where('billing_id', '!=', 'free')
            ->where('cycle', '=', 'yearly')
            ->where('active', '=', 0)
            ->where('price', '>=', $yearlyUnlimited->price)
            ->pluck('id')->toArray();

        $this->scheduleForcedDowngrade($yearlyPlanIds, $yearlyUnlimited);

        /**
         * Downgrade monthly plans
         **/
        $monthlyUnlimited = Plan::where('billing_id', '=', 'monthly_unlimited_2018')->first();

        $monthlyPlanIds = Plan::where('billing_id', '!=', 'free')
            ->where('active', '=', 0)
            ->where('cycle', '=', 'monthly')
            ->where('price', '>=', $monthlyUnlimited->price)
            ->pluck('id')->toArray();

        $this->scheduleForcedDowngrade($monthlyPlanIds, $monthlyUnlimited);
    }

    /**
     * @param $planIds
     * @param $monthlyUnlimited
     */
    public function scheduleForcedDowngrade(Array $originPlanIds, Plan $targetPlan): void
    {
        $downgradingUsersQuery = User::whereIn('plan_id', $originPlanIds)->whereNotIn('id', function($query) {
            $query->select('user_id')
                ->from('billing_actions')
                ->whereNull('deleted_at')
                ->where('forced', '=', 1)
                ->where(function($query) {
                    $query->where('change_to_billing_id', '=', 'yearly_unlimited_2018')
                        ->orWhere('change_to_billing_id', '=', 'monthly_unlimited_2018')
                        ->orWhere('change_to_billing_id', '=', 'free');
                });
        });

        $this->info($downgradingUsersQuery->count() . ' users left');

        $december = Carbon::create(2018, 12, 01)->startOfMonth()->addHours(3);

        $downgradingUsersQuery->chunk(10, function ($downgradingUsers) use ($targetPlan, $december) {
            foreach ($downgradingUsers as $user) {
                try {
                    $subscription = $user->subscription();

                    if ($subscription) {
                        $actionableAt = $subscription->getSubscriptionEndDate();
                    }

                    if ($december->lte($actionableAt)) {
                        $this->billingAction->scheduleDowngrade($user, $targetPlan, $actionableAt, true);
                    }
                } catch (Exception $e) {
                    echo 'Error scheduling downgrading for user ' . $user->id . ', Caught exception: ', $e->getMessage(), "\n";
                }
            }
        });

        $this->info(' downgrades scheduled');
    }

}
