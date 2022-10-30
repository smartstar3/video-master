<?php

namespace MotionArray\Http\Controllers\Webhooks;

use Bugsnag\Report;
use Carbon\Carbon;
use HavocInspired\Cashier\WebhookController as CashierWebhookController;
use Illuminate\Support\Facades\Log;
use MotionArray\Models\Subscription;
use MotionArray\Repositories\UserRepository;
use MotionArray\Events\UserEvent\UserDowngradedByPaymentFailure;
use Illuminate\Support\Facades\Response;
use MotionArray\Models\User;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Mailers\UserMailer;
use Exception;
use MotionArray\Repositories\UserSubscriptionRepository;
use MotionArray\Support\UserEvents\UserEventLogger;
use Stripe_Event;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * @var UserSubscriptionRepository
     */
    protected $userSubscription;

    /**
     * @var UserEventLogger
     */
    protected $userEventLogger;

    public function __construct(
        UserRepository $user,
        UserSubscriptionRepository $userSubscription,
        UserEventLogger $userEventLogger
    ) {
        $this->user = $user;
        $this->userSubscription = $userSubscription;
        $this->userEventLogger = $userEventLogger;
    }

    /**
     * Handle a failed payment from a Stripe subscription.
     *
     * @param Stripe_Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentSucceeded(Stripe_Event $event)
    {
        $invoice = $event->data->object;

        $stripeId = $invoice->customer;

        $user = User::where('stripe_id', '=', $stripeId)->first();

        if (!$user) {
            return $this->respondUserNotFound();
        }

        try {
            // Default
            $user->subscription_expired = 0;
            $user->save();

            if (isset($invoice->amount_paid)) {
                if ($invoice->amount_paid == 0) {
                    Log::info('Amount paid equals zero in ' . $invoice->id);

                    return Response::json(['message' => 'Amount paid is zero, no payment created']);
                }

                return Response::json(['message' => 'Webhook handled']);
            }

        } catch (Exception $e) {
            Log::error($e);

            $message = $e->getMessage();

            if (is_object($message)) {
                $message = $message->toArray();
            }

            return Response::json(['error' => $message], 422);
        }
    }

    protected function handleCustomerSubscriptionDeleted(Stripe_Event $event)
    {
        $stripeId = $event->data->object->customer;

        $user = User::where('stripe_id', '=', $stripeId)->first();

        if (!$user) {
            return $this->respondUserNotFound();
        }

        // Cancel account ?
        $this->userSubscription->cancelSubscription($user);

        return Response::json(['message' => 'Subscription canceled']);
    }

    /**
     * Handle a failed payment from a Stripe subscription.
     *
     * @param Stripe_Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    protected function handleInvoicePaymentFailed(Stripe_Event $event)
    {
        $stripeId = $event->data->object->customer;

        $user = User::where('stripe_id', '=', $stripeId)->first();

        if (!$user) {
            return $this->respondUserNotFound();
        }

        if ($this->tooManyFailedPayments($event)) {

            $user->downgrades()->create([
                'downgrade_reason' => 'failed payments',
                'downgrade_feedback' => ''
            ]);

            $this->userSubscription->cancelSubscription($user);

            /**
             * Send downgrade feedback email
             */
            $feedbackMailer = new FeedbackMailer;
            $feedbackMailer->invoicePaymentFailed($user);

            /**
             * Send account downgraded email to customer
             */
            $userMailer = new UserMailer;
            $userMailer->accountDowngraded($user);

            $this->userEventLogger->log(new UserDowngradedByPaymentFailure($user->id));

            return Response::json(['message' => 'Customer downgraded']);
        } else {
            /**
             * Set subscription expired
             */
            $user->subscription_expired = 1;
            $user->save();

            /**
             * Send failed payment email to customer
             * (on third payment attempt only)
             */
            if ($event->data->object->attempt_count == 3) {
                $userMailer = new UserMailer;
                $userMailer->invoicePaymentFailed($user);

                return Response::json(['message' => 'Customer notified']);
            }
        }

        $remainingAttempts = 3 - $event->data->object->attempt_count;

        return Response::json(['message' => 'Webhook handled. ' . $remainingAttempts . ' remaining attempts']);
    }

    public function handleCustomerCreated(Stripe_Event $event)
    {
        $email = $event->data->object->email;
        $stripeId = $event->data->object->id;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->respondUserNotFound();
        }

        $created = $event->data->object->created;
        $createdAt = Carbon::createFromTimestamp($created);
        $user->stripe_created_at = $createdAt;
        $user->stripe_id = $stripeId;
        $user->save();

        // user had stripe_id before and now it has been changed.
        if ($user->isDirty('stripe_id') && $user->getOriginal('stripe_id')) {
            \Bugsnag::notifyError('Multiple Stripe Account', 'User cannot have more than 1 stripe account. This may be multiple charge. ', function(Report $report) use ($user) {
                $report->setUser($user->toArray())
                    ->setSeverity('info');
            });
        }

        return Response::json(['message' => 'User #'.$user->id.' stripe_created_at saved. raw:' . $created . ', carbon:'. $createdAt]);
    }

    /**
     * Determine if the invoice has too many failed attempts.
     *
     * @param  Stripe_Event $event
     *
     * @return bool
     */
    protected function tooManyFailedPayments(Stripe_Event $event)
    {
        return $event->data->object->attempt_count > 3;
    }

    protected function respondUserNotFound()
    {
        // returning 200 because we don't want to Stripe retry event again.
        // If user not exists on our website, that means it doesnt exists :)
        return Response::json(['error' => 'User not found'], 200);
    }
}
