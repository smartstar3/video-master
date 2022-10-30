<?php namespace MotionArray\Mailers;

use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\User;
use App;

class FeedbackMailer extends Mailer
{
    public $recipient = ['admin@motionarray.com'];

    public $devRecipients = [];

    public function __construct()
    {
        if (App::environment() != 'production') {
            $this->recipient = array_merge($this->recipient, $this->devRecipients);
        }
    }

    /**
     * @param User $user
     * @param $reason
     * @param $feedback
     */
    public function deleteAccount(User $user, $reason, $feedback)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.delete-account";
        $data = [
            "user" => $user,
            "reason" => $reason,
            "feedback" => $feedback
        ];
        $subject = "A user has deleted their account";

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    /**
     * @param User $user
     * @param Plan $oldPlan
     * @param Plan $newPlan
     */
    public function downgradeSubscription(User $user, Plan $oldPlan, Plan $newPlan)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.subscription-downgrade";
        $data = [
            "user" => $user,
            "newPlan" => $newPlan,
            "oldPlan" => $oldPlan
        ];
        $subject = "A user has Downgraded their account to " . $newPlan->name;

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    /**
     * @param User $user
     * @param Plan $oldPlan
     * @param Plan $newPlan
     */
    public function upgradeSubscription(User $user, Plan $oldPlan, Plan $newPlan)
    {
        $view = "site.emails.feedback.subscription-upgrade";
        $data = [
            "user" => $user,
            "newPlan" => $newPlan,
            "oldPlan" => $oldPlan
        ];
        $subject = "A user has Upgraded their account to " . $newPlan->name;

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    /**
     * A user changed to free plan
     *
     * @param User $user
     * @param $reason
     * @param $feedback
     */
    public function cancelSubscription(User $user, $reason, $feedback)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.subscription-cancel";
        $data = [
            "user" => $user,
            "reason" => $reason,
            "feedback" => $feedback
        ];
        $subject = "A user has Downgraded to a Free plan - Subscription cancelled";

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    public function newPayingCustomer(User $user)
    {
        $view = 'site.emails.feedback.new-paying-customer';

        //@TODO: We will update `activeSubscription` when we start using `subscriptions` table for Stripe too.
        // Currently Paypal's data has been stored in `subscriptions` table but Stripe's data stored in `users` table.
        // If there is `activeSubscription`, use it's data. If not (that means user is using Stripe), use `users` table data.
        $data = [
            'user' => $user,
            'activeSubscription' => [
                'payment_gateway_name' => $user->activeSubscription->paymentGateway->name ?? PaymentGateways::STRIPE,
                'payment_gateway_customer_id' => $user->activeSubscription->payment_gateway_customer_id ?? $user->stripe_id,
                'payment_gateway_subscription_id' => $user->activeSubscription->payment_gateway_subscription_id ?? $user->stripe_subscription,
            ],
        ];
        $subject = "A new paying customer has signed up!";

        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    public function freeCustomerUpgraded(User $user)
    {
        //@TODO: We will update `activeSubscription` when we start using `subscriptions` table for Stripe too.
        // Currently Paypal's data has been stored in `subscriptions` table but Stripe's data stored in `users` table.
        // If there is `activeSubscription`, use it's data. If not (that means user is using Stripe), use `users` table data.
        $view = "site.emails.feedback.free-customer-upgrading";
        $data = [
            "user" => $user,
            'activeSubscription' => [
                'payment_gateway_name' => $user->activeSubscription->paymentGateway->name ?? PaymentGateways::STRIPE,
                'payment_gateway_customer_id' => $user->activeSubscription->payment_gateway_customer_id ?? $user->stripe_id,
                'payment_gateway_subscription_id' => $user->activeSubscription->payment_gateway_subscription_id ?? $user->stripe_subscription,
            ],
        ];
        $subject = "A free customer has upgraded!";

        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    /**
     * @param $inputs
     */
    public function contactMessage($inputs)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.contact-message";
        $data = [
            "subject" => $inputs["subject"],
            "name" => $inputs["name"],
            "email" => $inputs["email"],
            "body" => $inputs["message"]
        ];
        $from = [
            "name" => $inputs["name"],
            "email" => 'contact@motionarray.com',
            "replyTo" => $inputs["email"]
        ];
        $subject = ucfirst($inputs["subject"]);

        $recipients = array_merge(['hello@motionarray.com'], $this->devRecipients);

        /**
         * Send mail
         */
        return $this->sendTo($recipients, $subject, $view, $data, $from);
    }

    /**
     * @param User $user
     */
    public function invoicePaymentFailed(User $user)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.invoice-payment-failed";
        $data = [
            "user" => $user
        ];
        $subject = "A customer has been downgraded due to payment failure!";

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    /**
     * @param User $user
     */
    public function freeloaderDueToExpire(User $user)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.freeloader-due-to-expire";
        $data = [
            "user" => $user
        ];
        $subject = "A customer with freeloader access is due to expire in 7 days!";

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }

    public function missingPackages(Array $data)
    {
        if (!isset($data['missing'])) {
            return;
        }

        $view = "site.emails.feedback.missing-packages";

        if (count($data['missing']) || count($data['fixes'])) {
            $subject = "Missing packages Found!!!";
        } else {
            $subject = "No missing packages";
        }

        $recipients = array_unique(array_merge($this->devRecipients, $this->recipient));

        return $this->sendTo($recipients, $subject, $view, $data);
    }

    public function downloadsLimited(User $user)
    {
        /**
         * Email variables
         */
        $view = "site.emails.feedback.downloads-limited";
        $data = [
            "user" => $user
        ];
        $subject = "A customer has been limited for downloading";

        /**
         * Send mail
         */
        return $this->sendTo($this->recipient, $subject, $view, $data);
    }
}
