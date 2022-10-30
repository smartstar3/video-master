<?php namespace MotionArray\Presenters;

use Illuminate\Support\Facades\Auth;
use MotionArray\Models\BillingAction;
use MotionArray\Models\Plan;
use Carbon\Carbon;

class UserPresenter extends Presenter
{
    public function seller()
    {
        $imgixOptions['s3Bucket'] = 'https://' . config('aws.files_bucket') . '.s3.amazonaws.com/';
        $imgixOptions['imgixDomain'] = config('imgix.files_url');

        $profileImageUrl = imgixUrl($this->entity->profile_image_url, '?fit=crop&w=222&h=222', $imgixOptions);
        $headerImageUrl = imgixUrl($this->entity->header_image_url, '?fit=crop&w=3000&h=1000', $imgixOptions);

        if (empty($profileImageUrl)) {
            $profileImageUrl = '/apple-touch-icon-152x152-precomposed.png';
        }
        if (empty($headerImageUrl)) {
            $headerImageUrl = 'https://s3.amazonaws.com/ma-content/backgrounds/Background-V3.jpg';
        }

        $name = null;
        if (!empty($this->entity->seller_display_name)) {
            $name = $this->entity->seller_display_name;
        } elseif (!empty($this->entity->company_name)) {
            $name = $this->entity->company_name;
        } else {
            $name = ucwords(strtolower($this->entity->firstname . " " . $this->entity->lastname));
        }

        return [
            'id' => $this->entity->id,
            'name' => $name,
            'slug' => $this->entity->slug,
            'profile_info' => $this->entity->profile_info,
            'profile_image' => $profileImageUrl,
            'header_image' => $headerImageUrl,
        ];
    }

    public function name()
    {
        if ($this->company_name) {
            return $this->company_name;
        }

        return ucwords(strtolower($this->entity->firstname . " " . $this->entity->lastname));
    }

    public function joined()
    {
        if ($this->entity->hasRole(5)) {
            return "unknown date";
        }

        return $this->entity->created_at->format($this->defaultDateFormat);
    }

    public function signUpDate()
    {
        if ($this->entity->created_at) {
            return $this->entity->created_at->format($this->defaultDateFormat);
        }
    }

    public function gracePeriodEndDate()
    {
        if ($this->entity->subscription_ends_at) {
            $gracePeriodEnd = $this->entity->subscription_ends_at;
        } else {
            $periodRenewalDate = $this->entity->getPeriodRenewalDate();

            $gracePeriodEnd = $periodRenewalDate->addWeek();
        }

        if ($gracePeriodEnd) {
            return $gracePeriodEnd->format($this->defaultDateFormat);
        }
    }

    public function downgradeSubscriptionDate()
    {
        $downgrade_action = BillingAction::where("user_id", "=", $this->entity->id)
            ->where("action", "=", "downgrade")
            ->first();

        if ($downgrade_action) {
            return $downgrade_action->actionable_at->format($this->defaultDateFormat);
        }
    }

    public function downgradingToPlanName()
    {
        $downgrade_action = BillingAction::where("user_id", "=", $this->entity->id)
            ->where("action", "=", "downgrade")
            ->first();

        if ($downgrade_action) {
            $new_plan = Plan::find($downgrade_action->change_to_plan_id);
            $cycle = $new_plan->cycle == "monthly" ? "per month" : "per year";

            $message = $new_plan->name . " plan ";

            if (!$new_plan->isFree()) {
                $message .= "@ $" . $this->formatPrice($new_plan->price) . " " . $cycle;
            }

            return $message;
        }
    }

    public function accountSellerStats()
    {
        if ($this->entity->isSeller()) {
            $start_date = Carbon::now()->startOfMonth();

            $end_date = Carbon::now()->endOfMonth();

            $userRepo = \App::make('MotionArray\Repositories\UserRepository');

//            $earnings = $userRepo->getTotalEarningsForPeriod(Auth::user(), $start_date, $end_date);

            $approvedProducts = Auth::user()->products()->published()
                //->whereBetween('published_at', [$start_date, $end_date])
                ->count();

            $message = 'Thank you for being a Motion Array producer. ';

            if ($approvedProducts) {
                $message .= 'You have ' . $approvedProducts . ' approved files. ';
            }

//            $message .= ($approvedProducts ? 'and this' : 'This') . ' month you have made $' . number_format($earnings, 2) . '.';

            return $message;
        }
    }

    public function portfolioExpirationWarning()
    {
        $remaining = $this->entity->PortfolioTrialRemaining;

        if ($remaining && $remaining['days'] < 8) {

            if ($remaining['hours'] > 20 && $remaining['days'] > 0) {
                $remaining['days']++;
            }

            $remainingTime = $remaining['days'] ? ($remaining['days'] . ' days') : ($remaining['hours'] . ' hours');

            return 'You have ' . $remainingTime . ' to <a href="/account/upgrade" target="_blank">upgrade</a>, or your Review videos and Portfolio videos will be deleted';
        }
    }

    public function plan()
    {
        $plan = $this->entity->plan;

        $formatted_plan = "";
        if ($plan) {
            $period = "";
            $formatted_plan = $plan->name;

            if ($plan->cycle) {
                $formatted_plan .= " (" . $plan->cycle . ") plan";

                if ($plan->cycle == "monthly") {
                    $period = "mo";
                }

                if ($plan->cycle == "yearly") {
                    $period = "year";
                }
            }

            if ($plan->price) {
                $formatted_plan .= " @ $" . $plan->present()->price . " /" . $period;
            }
        }

        return $formatted_plan;
    }
}
