<?php

namespace MotionArray\Repositories;

use HavocInspired\Cashier\Invoice;
use HavocInspired\Cashier\PaypalGateway;
use HavocInspired\Cashier\StripeGateway;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalUpgradeException;
use Stripe_Error;

class UserSubscriptionRepository
{
    /**
     * @var \MotionArray\Repositories\UserRepository
     */
    protected $user;
    /**
     * @var \MotionArray\Repositories\BillingActionRepository
     */
    protected $billingAction;
    /**
     * @var FeedbackMailer
     */
    protected $feedbackMailer;
    /**
     * @var \MotionArray\Repositories\ProjectRepository
     */
    protected $project;

    public function __construct(
        UserRepository $userRepository,
        BillingActionRepository $billingActionRepository,
        FeedbackMailer $feedbackMailer,
        ProjectRepository $projectRepository
    ) {
        $this->user = $userRepository;
        $this->billingAction = $billingActionRepository;
        $this->feedbackMailer = $feedbackMailer;
        $this->project = $projectRepository;
    }

    /**
     * Subscribes user
     *
     * @param User $user
     * @param Plan $plan
     * @param $token
     * @param $couponId
     *
     * @return User
     */
    public function create(User $user, Plan $plan, $token, $couponId = null)
    {
        if ($user->everSubscribed()) {
            // Resubscribe to a plan
            $this->updateCard($user, $token);

            $user->subscription($plan->billing_id)->noProrate()->billingCycleAnchor("now")->swapAndInvoice();
        } else {

            // If user changes card, should have accepted tos
            $this->user->acceptTos($user);

            $paymentGateway = $user->subscription($plan->billing_id);

            if ($couponId) {
                $paymentGateway = $paymentGateway->withCoupon($couponId);
            }

            $paymentGateway->create($token, [
                'email' => $user->email,
                'description' => $user->billing_firstname . ' ' . $user->billing_lastname
            ]);
        }

        $user->plan_id = $plan->id;
        $user->subscription_expired = 0; // Ensure the account isn't disabled
        $user->portfolio_trial_ends_at = null;
        $user->save();

        /**
         * Reactivate any previous downloads and projects
         */
        foreach ($user->downloads()->get() as $download) {
            $download->active = 1;
            $download->save();
        }

        $this->project->restoreProjects($user);

        return $user;
    }

    /**
     * Create a new subscription with given plan, with trial date.
     *
     * @param User $user
     * @param Plan $plan
     * @param \DateTimeInterface $trialEndsAt
     */
    public function createSubscriptionWithTrial(User $user, Plan $plan, \DateTimeInterface $trialEndsAt)
    {
        /** @var StripeGateway $subscription */
        $subscription = $user->subscription($plan->billing_id);
        $subscription->noProrate()->trialFor($trialEndsAt)->billingCycleAnchor('now')->swapAndInvoice();

        $user->plan_id = $plan->id;
        $user->save();
    }

    /**
     * @param User $user
     * @param $token
     * @return mixed
     */
    public function updateCard($user, $token)
    {
        // If user changes card, should have accepted tos
        $this->user->acceptTos($user);

        return $user->subscription()->updateCard($token, $user);
    }

    /**
     * Pull current subscription from Stripe, update on DB
     *
     * @param User $user
     */
    public function refresh(User $user)
    {
        $stripe = $user->subscription();

        $customer = $stripe->getStripeCustomer();

        $subscription = $customer->subscription;

        if ($subscription) {

        } else {
            $this->cancelSubscription($user);
        }
    }

    /**
     * Save downgrade reason and Send feedback email
     *
     * @param User $user
     * @param $downgradeReason
     * @param $downgradeFeedback
     */
    public function downgradeReason(User $user, $downgradeReason, $downgradeFeedback)
    {
        if ($user->subscribed() && $downgradeReason) {
            $user->downgrades()->create([
                'downgrade_reason' => $downgradeReason,
                'downgrade_feedback' => $downgradeFeedback
            ]);

            $this->feedbackMailer->cancelSubscription($user, $downgradeReason, $downgradeFeedback);
        }
    }

    /**
     * @param User $user
     */
    public function updateDescription(User $user)
    {
        // If this user has a stripe account
        if ($user->stripe_id) {
            // Attempt to update the customer's stripe record.
            try {
                $stripe_record = $user->subscription()->getStripeCustomer();
                $stripe_record->email = $user->email;
                $stripe_record->description = $user->firstname . ' ' . $user->lastname; // Update name.
                $stripe_record->save();
            } catch (Stripe_Error $e) {
                // Log error
                \Log::error("There was an error updating a customer's stripe record: " . $e->getMessage());
            }
        }
    }

    /**
     * @param User $user
     * @param Plan $plan
     * @return bool
     * @throws \Exception
     */
    public function upgrade(User $user, Plan $plan)
    {
        // We don't support upgrades for Paypal, yet. Ticket: MA-1643
        if ($user->subscription() instanceof PaypalGateway) {
            throw new PaypalUpgradeException('There was an error upgrading your plan. Please contact our support team!');
        }

        $oldPlan = $user->plan;

        // delete existing invoices
        $user->subscription()->voidUnpaidInvoices();

        if ($oldPlan->id === $plan->id) {
            return false;
        }

        /**
         * Swap subscription plan
         */
        $stripe = $user->subscription($plan->billing_id);
        $stripe->billingCycleAnchor("now")->swapAndInvoice();

        $this->billingAction->deleteScheduledDowngradesForUser($user, true);

        /**
         * Update user plan relationship
         */
        $user->plan_id = $plan->id;
        $user->period_end_at = null;
        $user->subscription_expired = 0; // Ensure the account isn't disabled

        return $user->save();
    }

    /**
     * @param User $user
     * @param Plan $plan
     * @return User
     * @throws \Exception
     */
    public function downgradeToPlan(User $user, Plan $plan)
    {
        if ($plan->isFree()) {
            throw new \Exception('Free plan is not accepted in downgradeToPlan function, please use cancelSubscription');
        }

        $user->plan_id = $plan->id;
        $user->period_end_at = null;
        $user->save();

        $this->billingAction->deleteScheduledDowngradesForUser($user, true);

        try {
            $user->subscription($plan->billing_id)->noProrate()->billingCycleAnchor("now")->swapAndInvoice();
        } catch (\Exception $e) {
            $user->subscription($plan->billing_id)->noProrate()->swap();
        }

        return $user;
    }

    /**
     * This method will cancel user's subscription (on Stripe,Paypal) at the moment.
     * Also changes user's subscription to FREE plan.
     *
     * @param User $user
     *
     * @return User
     *
     * @throws \MotionArray\Services\Subscription\Exceptions\Paypal\PaypalCancelationException
     */
    public function cancelSubscription(User $user)
    {
        if ($user->subscribed()) {
            /**
             * Cancel subscription
             */
            $user->subscription()->cancelNow();
        }

        /**
         * Delete any previous download requests
         */
        $this->billingAction->deleteScheduledDowngradesForUser($user, true);

        /**
         * Downgrade account
         */
        $user->plan_id = Plans::FREE_ID;
        $user->stripe_plan = null;
        $user->last_four = null;
        $user->subscription_ends_at = null;
        $user->period_end_at = null;
        $user->subscription_expired = 0; // Ensure the account isn't disabled
        $user->access_starts_at = null;
        $user->access_expires_at = null;
        $user->stripe_active = false;
        $user->save();

        return $user;
    }

    /**
     * Downgrade using scheduled downgrade
     *
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function downgradeNow(User $user)
    {
        $billingAction = $user->billingActions()->downgrades()->first();

        if ($billingAction) {
            if ($billingAction->change_to_billing_id == 'free') {
                $this->cancelSubscription($user);
            } else {
                $plan = $billingAction->plan;

                $this->downgradeToPlan($user, $plan);
            }
        }

        return $billingAction;
    }

    /**
     * Refund invoice's charge - if exists.
     *
     * @param User $user
     * @param $invoiceId
     * @param int|null $refundAmount Send null to refund whole amount
     *
     * @return \Stripe_Refund|null
     */
    public function refundInvoice(User $user, $invoiceId, $refundAmount = null)
    {
        /** @var Invoice $latestInvoice */
        $invoice = $user->subscription()->findInvoice($invoiceId);
        if ($invoice->charge) {
            return $user->subscription()->refundCharge($invoice->charge, $refundAmount, $reason = 'yearly_to_monthly_transition');
        }

        return null;
    }
}
