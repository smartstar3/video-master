<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\BillingAction;
use MotionArray\Models\Plan;
use MotionArray\Models\User;

class BillingActionRepository
{
    /**
     * @param User $user
     * @return mixed
     */
    public function getDowngradeByUser(User $user)
    {
        return $user->billingActions()->downgrades()->first();
    }

    /**
     * @param User $user
     * @param Plan $plan
     * @param Carbon|null $actionableAt
     * @param bool $forced
     * @return BillingAction
     * @throws \Exception
     */
    public function scheduleDowngrade(User $user, Plan $plan, Carbon $actionableAt = null, $forced = false)
    {
        if ($plan->active) {
            $forced = true;
        }

        $this->deleteScheduledDowngradesForUser($user, $forced);

        if (!$actionableAt) {
            $subscription = $user->subscription();

            if ($subscription) {
                $actionableAt = $subscription->getSubscriptionEndDate();
            }
        }

        if ($actionableAt) {
            $billingAction = new BillingAction();
            $billingAction->user_id = $user->id;
            $billingAction->actionable_at = $actionableAt;
            $billingAction->action = "downgrade";
            $billingAction->forced = $forced;
            $billingAction->change_to_plan_id = $plan->id;
            $billingAction->change_to_billing_id = $plan->billing_id;
            $billingAction->save();

            $user->period_end_at = null;
            $user->save();

            return $billingAction;
        }
    }

    /**
     * @param User $user
     * @return bool|null
     * @throws \Exception
     */
    public function deleteScheduledDowngradesForUser(User $user, $includeForced = false)
    {
        $query = BillingAction::where("user_id", "=", $user->id)
            ->where("action", "=", "downgrade");

        if (!$includeForced) {
            $query->where('forced', '=', false);
        }

        return $query->delete();

//        $billingAction = $query->first();

//        if ($billingAction)
//        {
//            return $billingAction->delete();
//        }
    }
}
