<?php namespace MotionArray\Repositories;

use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Repositories\EloquentBaseRepository;

class PlanRepository extends EloquentBaseRepository
{
    public function getFreePlan()
    {
        return Plan::where('billing_id', '=', Plans::FREE)->first();
    }

    public function isDowngradingPlans(Plan $current_plan, Plan $new_plan)
    {
        return $new_plan->price < $current_plan->price;
    }

    public function getActivePlans()
    {
        return Plan::active()->orderBy('order')->get();
    }
}
