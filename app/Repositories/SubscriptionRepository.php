<?php

namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\Download;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\Subscription;
use MotionArray\Models\SubscriptionPayment;
use MotionArray\Models\User;
use MotionArray\Services\Subscription\Exceptions\InvalidSubscriptionParameterException;

class SubscriptionRepository
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function createSubscription(
        User $user,
        Plan $plan,
        int $paymentGatewayId,
        string $paymentGatewayCustomerId,
        string $paymentGatewaySubscriptionId,
        ?string $paymentGatewayEmail,
        int $subscriptionStatusId,
        Carbon $startAt,
        Carbon $endAt
    ): Subscription
    {
        if ($endAt->lessThan($startAt)) {
            throw new InvalidSubscriptionParameterException('End at cannot be less than start at.');
        }

        /** @var Subscription $subscription */
        $subscription = $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'payment_gateway_id' => $paymentGatewayId,
            'payment_gateway_customer_id' => $paymentGatewayCustomerId,
            'payment_gateway_subscription_id' => $paymentGatewaySubscriptionId,
            'payment_gateway_email' => $paymentGatewayEmail,
            'subscription_status_id' => $subscriptionStatusId,
        ]);

        return $subscription;
    }

    /**
     * When a subscription had started, we change some fields in users table.
     * Also activating downloads and projects.
     *
     * @param User $user
     * @param Plan $plan
     *
     * @throws \Throwable
     */
    public function activateSubscription(User $user, Plan $plan)
    {
        \DB::transaction(function () use ($user, $plan) {

            //stripe_active is legacy column. That means "subscription_active".
            $user->stripe_active = true;
            $user->stripe_plan = $plan->billing_id;
            $user->plan_id = $plan->id;
            // Ensure the account isn't disabled
            $user->subscription_expired = 0;
            $user->portfolio_trial_ends_at = null;
            $user->save();

            /**
             * Reactivate any previous downloads and projects
             */
            /** @var Download $download */
            foreach ($user->downloads()->get() as $download) {
                $download->active = 1;
                $download->save();
            }

            $this->projectRepository->restoreProjects($user);
        });
    }

    public function createSubscriptionPayment(
        Subscription $subscription,
        $gatewayPaymentId,
        $amount,
        $fee,
        int $statusId
    ): SubscriptionPayment
    {
        return $subscription->payments()->create([
            'amount' => $amount,
            'fee' => $fee,
            'attempted_at' => now(),
            'gateway_payment_id' => $gatewayPaymentId,
            'subscription_payment_status_id' => $statusId,
        ]);
    }

    /**
     * Used for update subscriptions's end_at value.
     * Stripe&Paypal sends us Webhooks, and we update subscription based on this webhook request.
     *
     * @param Subscription $subscription
     * @param User $user
     * @param \DateTime $endAt
     *
     * @throws \Throwable
     */
    public function updateSubscriptionEndDate(Subscription $subscription, User $user, \DateTime $endAt)
    {
        \DB::transaction(function () use ($subscription, $user, $endAt) {
            if (now()->lessThan($endAt)) {
                $user->subscription_expired = 0;
                $user->save();
            }

            $subscription->end_at = $endAt;
            $subscription->save();
        });
    }

    /**
     * Update subscription's status.
     * Also update `users.stripe_active` column too based on status id.
     *
     * @param Subscription $subscription
     * @param int $statusId
     *
     * @throws \Throwable
     */
    public function updateSubscriptionStatus(Subscription $subscription, int $statusId)
    {
        \DB::transaction(function () use ($subscription, $statusId) {

            $subscription->subscription_status_id = $statusId;
            $subscription->save();

            $active = $statusId === SubscriptionStatuses::STATUS_ACTIVE_ID;
            $subscription->user->stripe_active = $active;
            $subscription->user->save();
        });
    }

    public function updateSubscriptionPaymentStatus(SubscriptionPayment $subscriptionPayment, int $subscriptionPaymentStatusId) {
        $subscriptionPayment->subscription_payment_status_id = $subscriptionPaymentStatusId;
        $subscriptionPayment->save();
    }
}
