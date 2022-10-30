<?php namespace MotionArray\Console\Commands\OneTime;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\Plan;
use MotionArray\Models\User;
use MotionArray\Repositories\BillingActionRepository;
use Exception;
use MotionArray\Repositories\PlanRepository;
use MotionArray\Repositories\UserRepository;

class ScheduleUpgradesOldPlansToUnlimited extends Command
{
    protected $name = 'motionarray-onetime:schedule-upgrades-to-unlimited-for-old-plans';

    protected $description = 'Schedules an upgrade to unlimited monthly plan for monthly plans';

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
        // $monthlyUnlimited = Plan::where('billing_id', "=", "monthly_unlimited_2018")->first();

        // // Monthly plans for upgrade
        // $plans = ["monthly_lite_2017", "monthly_plus", "monthly_small", "monthly_lite"];
        // $monthlyPlanIds = Plan::where('billing_id', '!=', 'free')
        //     ->whereIn('billing_id', $plans)
        //     ->where('price', '<=', $monthlyUnlimited->price)
        //     ->pluck('id')->toArray();

        // $this->scheduleUpgrade($monthlyPlanIds, $monthlyUnlimited);


        $yearlyUnlimited = Plan::where('billing_id', '=', 'yearly_unlimited_2018')->first();

        // Annual plans for upgrade
        $plans = ['yearly_lite'];
        $yearlyPlanIds = Plan::where('billing_id', '!=', 'free')
            ->whereIn('billing_id', $plans)
            ->where('price', '<=', $yearlyUnlimited->price)
            ->pluck('id')->toArray();

        $this->scheduleUpgrade($yearlyPlanIds, $yearlyUnlimited);
    }

    /**
     * Upgrade from any of the plans in $planIds to $monthlyUnlimited
     *
     * @param $planIds
     * @param $monthlyUnlimited
     */
    public function scheduleUpgrade(Array $originPlanIds, Plan $targetPlan): void
    {
        $upgradingUsersQuery = User::whereIn('plan_id', $originPlanIds)->whereNotIn('id', function($query) use ($targetPlan) {
            $query->select('user_id')
            ->from('billing_actions')
            ->whereNull('deleted_at')
            ->whereIn('change_to_billing_id', $targetPlan);
        });

        $this->info($upgradingUsersQuery->count() . ' users left');

        $count = 0;

        $upgradingUsersQuery->chunk(10, function ($upgradingUsers) use ($targetPlan, &$count /*, $targetDate*/) {
            foreach ($upgradingUsers as $user) {
                try {
                    // NOTE: We are calling scheduleDowngrade below, but this is actually an upgrade.
                    // There is no infrastructure for upgrade and refactoring is not straightforward.
                    // FIXME: major refactor needed
                    $res = $this->billingAction->scheduleDowngrade($user, $targetPlan, /* actionableAt */ null, /* forced: */ true);
                    if ($res)
                    {
                        $this->info($user->email . ' upgrade scheduled.');
                        $count++;
                    }
                } catch (Exception $e) {
                    echo 'Error scheduling upgrade for user ' . $user->id . ', caught exception: ', $e->getMessage(), "\n";
                }
            }
        });

        $this->info($count . ' upgrades scheduled');
    }

}
