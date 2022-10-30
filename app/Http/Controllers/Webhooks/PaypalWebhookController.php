<?php

namespace MotionArray\Http\Controllers\Webhooks;

use Bugsnag\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MotionArray\Events\UserEvent\Subscription\SubscriptionResumedAfterPaymentHold;
use MotionArray\Events\UserEvent\Subscription\SubscriptionSuspendedByPaymentOnHold;
use MotionArray\Events\UserEvent\UserDowngradedByPaymentFailure;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\Subscription;
use MotionArray\Models\SubscriptionPayment;
use MotionArray\Models\SubscriptionStatus;
use MotionArray\Models\User;
use MotionArray\Repositories\BillingActionRepository;
use MotionArray\Services\Subscription\PaypalService;
use MotionArray\Repositories\SubscriptionRepository;
use MotionArray\Services\Subscription\PaypalStatusParser;
use MotionArray\Support\UserEvents\UserEventLogger;

class PaypalWebhookController extends Controller
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;
    /**
     * @var PaypalService
     */
    private $paypalService;
    /**
     * @var BillingActionRepository
     */
    private $billingActionRepository;
    /**
     * @var UserEventLogger
     */
    private $userEventLogger;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        PaypalService $paypalService,
        BillingActionRepository $billingActionRepository,
        UserEventLogger $userEventLogger
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->paypalService = $paypalService;
        $this->billingActionRepository = $billingActionRepository;
        $this->userEventLogger = $userEventLogger;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function handleWebhook(Request $request)
    {
        $subscription = null;
        $webhookType = $request->request->get('txn_type');
        $recurringPaymentId = $request->get('recurring_payment_id');
        $paymentStatus = $request->get('payment_status');

        if ($recurringPaymentId) {
            try {
                /** @var Subscription $subscription */
                $subscription = Subscription::where('payment_gateway_subscription_id', $recurringPaymentId)->firstOrFail();

                $user = $subscription->user;
            } catch (ModelNotFoundException $exception) {
                \Bugsnag::notifyException($exception, function (Report $report) use ($request) {
                    $report->setSeverity('info');
                    $report->setMetaData([
                        'webhook_payload' => $request->all()
                    ]);
                });
            }
        }

        if ($webhookType === 'cart') {
            if ($paymentStatus === 'Pending') {
                $this->handlePaymentPending($request);
            } else if ($paymentStatus === 'Completed') {
                $this->handlePaymentCompleted($request);
            }
        } else if ($webhookType === 'recurring_payment' && $subscription) {
            //Recurring payment received
            $this->handleRecurringPayment($subscription, $user, $request);
        } else if ($webhookType === 'recurring_payment_profile_created') {
            //Recurring payment profile created
        } else if ($webhookType === 'recurring_payment_profile_cancel' && $subscription) {
            //Recurring payment profile canceled
            $this->handleRecurringPaymentProfileCancel($subscription);
        } else if ($webhookType === 'recurring_payment_suspended') {
            //Recurring payment profile canceled
        } else if ($webhookType === 'recurring_payment_expired') {
            /** Recurring payment expired */
        } else if ($webhookType === 'recurring_payment_failed') {
            /**
             * Recurring payment failed
             * This transaction type is sent if:
             * The attempt to collect a recurring payment fails
             * The "max failed payments" setting in the customer's recurring payment profile is 0
             * In this case, PayPal tries to collect the recurring payment an unlimited number of times without ever suspending the customer's recurring payments profile.
             */
        } else if ($webhookType === 'recurring_payment_skipped' && $subscription) {
            //Recurring payment skipped; it will be retried up to 3 times, 5 days apart
            $this->handleRecurringPaymentSkipped($subscription);
        } else if ($webhookType === 'subscr_cancel') {
            //Subscription canceled
        } else if ($webhookType === 'subscr_eot') {
            //Subscription expired
        } else if ($webhookType === 'subscr_failed') {
            //Subscription payment failed
        } else if ($webhookType === 'subscr_payment') {
            //Subscription payment received
        } else if ($webhookType === 'new_case') {
            //when a new case created, like case_type: chargeback, complaint, dispute
            $this->handleDisputeCase($request);
        }

        // if payment's status has been updated; we get an ipn without `txn_type`
        if (!$webhookType) {
            // If payment has been refunded by us OR refunded due to a dispute,
            // We're saving amount to our database.
            if ($paymentStatus === 'Refunded') {
                $this->handlePaymentRefund($request);
            }
        }

        return \Response::json(['message' => 'Webhook handled']);
    }

    /**
     * @param Subscription $subscription
     *
     * @throws \Throwable
     */
    private function handleRecurringPaymentSkipped(Subscription $subscription)
    {
        $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_SUSPENDED_ID);
    }

    private function handleRecurringPayment(Subscription $subscription, User $user, Request $request)
    {
        $paymentProfile = $this->paypalService->getRecurringPaymentsProfileDetails($subscription->payment_gateway_subscription_id);
        $nextBillingDate = Carbon::createFromTimeString($paymentProfile['NEXTBILLINGDATE']);

        $this->subscriptionRepository->updateSubscriptionEndDate($subscription, $user, $nextBillingDate);

        //E.g.: Id of end the url https://www.sandbox.paypal.com/activity/payment/9CL3579695623762G
        $paymentId = $request->get('txn_id');
        //E.g. 29.00
        $amount = $request->get('amount');
        //E.g. 1.13
        $paymentFee = $request->get('payment_fee');
        $paymentStatus = app(PaypalStatusParser::class)->parsePaymentStatus($request->get('payment_status'));

        if ($paymentStatus === SubscriptionPaymentStatuses::STATUS_SUCCESS_ID) {
            //if payment is succeeded, we're marking subscription as `active` too. so user can continue using subscription.
            $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_ACTIVE_ID);
        } else if ($paymentStatus === SubscriptionPaymentStatuses::STATUS_PENDING_ID) {
            $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_PENDING_ID);
        }

        if ($amount > 0) {
            $this->subscriptionRepository->createSubscriptionPayment(
                $subscription,
                $paymentId,
                //@TODO: use moneyphp for calculations
                $amount * 100,
                //@TODO: use moneyphp for calculations
                $paymentFee * 100,
                $paymentStatus
            );
        }
    }

    private function handleRecurringPaymentProfileCancel(Subscription $subscription)
    {
        if ($subscription->subscription_status_id === SubscriptionStatuses::STATUS_SUSPENDED_ID) {
            $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_CANCELED_ID);
        } else if ($subscription->subscription_status_id === SubscriptionStatuses::STATUS_ACTIVE_ID) {
            $plan = Plan::find(Plans::FREE_ID);
            $this->billingActionRepository->scheduleDowngrade($subscription->user, $plan, $subscription->end_at);
        }
    }

    private function handlePaymentRefund(Request $request)
    {
        //transaction id of refund
        $txnId = $request->get('txn_id');
        //transaction id of refunded/original payment
        $parentTxnId = $request->get('parent_txn_id');
        //this value is negative. like: "-29.00"
        $amount = $request->get('mc_gross') * 100;
        //this value is negative. like: "-0.84"
        $fee = $request->get('mc_fee') * 100;

        $subscriptionPayment = SubscriptionPayment::where('gateway_payment_id', $parentTxnId)
            ->first();

        if ($subscriptionPayment) {
            $subscription = $subscriptionPayment->subscription;

            $this->subscriptionRepository->createSubscriptionPayment(
                $subscription,
                $txnId,
                $amount,
                $fee,
                SubscriptionPaymentStatuses::STATUS_REFUNDED_ID
            );
        }
    }

    /**
     * @param Request $request
     *
     * @throws \Throwable
     */
    private function handleDisputeCase(Request $request)
    {
        $txnId = $request->get('txn_id');
        try {
            $subscriptionPayment = SubscriptionPayment::where('gateway_payment_id', $txnId)
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            \Bugsnag::notifyException($exception, function (Report $report) use ($request) {
                $report->setSeverity('info');
                $report->setMetaData([
                    'webhook_payload' => $request->all()
                ]);
            });

            return;
        }

        $subscription = $subscriptionPayment->subscription;

        $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_CANCELED_ID);
    }

    /**
     * @param Request $request
     *
     * @throws \Throwable
     */
    private function handlePaymentPending(Request $request)
    {
        //transaction id of refund
        $txnId = $request->get('txn_id');

        /** @var SubscriptionPayment $subscriptionPayment */
        $subscriptionPayment = SubscriptionPayment::where('gateway_payment_id', $txnId)
            ->first();

        $subscription = $subscriptionPayment->subscription;

        $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_PENDING_ID);
        $this->subscriptionRepository->updateSubscriptionPaymentStatus($subscriptionPayment, SubscriptionPaymentStatuses::STATUS_PENDING_ID);

        $this->userEventLogger->log(new SubscriptionSuspendedByPaymentOnHold($subscription->user->id, $subscription->id, $subscriptionPayment->id));
    }

    /**
     * @param Request $request
     *
     * @throws \Throwable
     */
    private function handlePaymentCompleted(Request $request)
    {
        //transaction id of refund
        $txnId = $request->get('txn_id');
        /** @var SubscriptionPayment $subscriptionPayment */
        $subscriptionPayment = SubscriptionPayment::where('gateway_payment_id', $txnId)
            ->first();

        $subscription = $subscriptionPayment->subscription;
        $this->subscriptionRepository->updateSubscriptionPaymentStatus($subscriptionPayment, SubscriptionPaymentStatuses::STATUS_SUCCESS_ID);

        // if subscription's status was PENDING, we're changing it back to ACTIVE
        if ($subscription->subscription_status_id === SubscriptionStatuses::STATUS_PENDING_ID) {
            $this->subscriptionRepository->updateSubscriptionStatus($subscription, SubscriptionStatuses::STATUS_ACTIVE_ID);
            $this->userEventLogger->log(new SubscriptionResumedAfterPaymentHold($subscription->user_id, $subscription->id, $subscriptionPayment->id));
        }
    }
}
