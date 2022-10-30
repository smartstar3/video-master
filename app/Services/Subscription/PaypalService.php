<?php

namespace MotionArray\Services\Subscription;

use Carbon\Carbon;
use Exception;
use MotionArray\Events\UserEvent\Subscription\SubscriptionInitialPaymentFailed;
use MotionArray\Events\UserEvent\Subscription\SubscriptionInitialPaymentSucceeded;
use MotionArray\Events\UserEvent\Subscription\SubscriptionStarted;
use MotionArray\Services\Subscription\Exceptions\Paypal\InvalidPaypalCheckoutTokenException;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalCreateSubscriptionFailedException;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalInstantPaymentFailedException;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\User;
use MotionArray\Repositories\SubscriptionRepository;
use MotionArray\Support\UserEvents\UserEventLogger;
use Psr\Http\Message\StreamInterface;
use Srmklive\PayPal\Services\ExpressCheckout;

class PaypalService
{
    /**
     * @var ExpressCheckout
     */
    public $provider;
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;
    /**
     * @var UserEventLogger
     */
    private $userEventLogger;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        ExpressCheckout $expressCheckout,
        UserEventLogger $userEventLogger
    )
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->provider = $expressCheckout;
        $this->userEventLogger = $userEventLogger;
    }

    public function createPaypalSubscriptionPaymentUrl(Plan $plan, bool $isNewCustomer = true): string
    {
        $data = [];

        $data['items'][] = [
            'name' => $plan->name,
            //@TODO: use moneyphp for divide
            'price' => $plan->price / 100,
            'qty' => 1,
        ];

        $payload = encrypt([
            'plan_id' => $plan->id,
            'type' => $isNewCustomer ? 'new' : 'upgrade',
        ]);

        $redirectUrl = "/sign-up/paypal-handler?payload=$payload";

        $data['subscription_desc'] = $plan->name;
        $data['invoice_id'] = str_random();
        $data['invoice_description'] = $plan->name;
        $data['return_url'] = url($redirectUrl);
        $data['cancel_url'] = url('/pricing');
        //@TODO: use moneyphp for divide
        $data['total'] = $plan->price / 100;

        $options = [
            'BRANDNAME' => 'MotionArray',
            'LOGOIMG' => config('paypal.logo_url'),
            'CHANNELTYPE' => 'Merchant',
        ];

        $response = $this->provider->addOptions($options)->setExpressCheckout($data, true);

        return $response['paypal_link'];
    }

    public function getCheckoutDetails(string $token)
    {
        return $this->provider->getExpressCheckoutDetails($token);
    }

    public function isCheckoutSuccess(array $checkoutDetails): bool
    {
        if (!array_key_exists('ACK', $checkoutDetails)) {
            return false;
        }

        return 'SUCCESS' === strtoupper($checkoutDetails['ACK']);
    }

    /**
     * @param Plan $plan
     * @param string $token
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function createPaypalSubscription(Plan $plan, string $token): ?array
    {
        $response = $this->createSubscription($plan, $token);

        if (empty($response['PROFILESTATUS'])) {
            return null;
        }

        $subscriptionStartedSuccessfully = in_array($response['PROFILESTATUS'], ['ActiveProfile', 'PendingProfile']);
        if ($subscriptionStartedSuccessfully) {
            return $response;
        }

        return null;
    }

    /**
     * @param string $profileId Paypal Subscription(Recurring Payment) Id
     *
     * @return array|StreamInterface
     *
     * @throws Exception
     */
    public function getRecurringPaymentsProfileDetails(string $profileId)
    {
        return $this->provider->getRecurringPaymentsProfileDetails($profileId);
    }

    /**
     * @param string $profileId Paypal Subscription(Recurring Payment) Id
     *
     * @return array|StreamInterface
     *
     * @throws Exception
     */
    public function cancelRecurringPayment(string $profileId)
    {
        return $this->provider->cancelRecurringPaymentsProfile($profileId);
    }

    /**
     * @param string $profileId Paypal Subscription(Recurring Payment) Id
     * @param array  $data
     *
     * @return array|StreamInterface
     *
     * @throws Exception
     */
    public function updateRecurringPayment(string $profileId, array $data)
    {
        return $this->provider->updateRecurringPaymentsProfile($data, $profileId);
    }

    /**
     * @param Plan $plan
     * @param string $token
     *
     * @return array|StreamInterface
     *
     * @throws Exception
     */
    private function createSubscription(Plan $plan, string $token)
    {
        /**
         * We're capturing money with an instant payment. See: instantPayment() method.
         * So, we're creating subscription profile that starts 1 month/year later.
         */
        if ($plan->isMonthly()) {
            $profileStartDate = Carbon::now()->addMonth()->toIso8601ZuluString();
        } else if($plan->isYearly()) {
            $profileStartDate = Carbon::now()->addYear()->toIso8601ZuluString();
        }

        $data = [
            'PROFILESTARTDATE' => $profileStartDate,
            'DESC' => $plan->name,
            'BILLINGPERIOD' => $plan->isMonthly() ? 'Month' : 'Year',
            'BILLINGFREQUENCY' => 1,
            //@TODO: use moneyphp for divide
            'AMT' => $plan->price / 100,
            'CURRENCYCODE' => config('paypal.currency'),
        ];

        return $this->provider->createRecurringPaymentsProfile($data, $token);
    }

    /**
     * @param Plan   $plan
     * @param string $token
     * @param string $paymentGatewayCustomerId
     *
     * @return null|array
     *
     * @throws Exception
     */
    public function instantPayment(User $user, Plan $plan, string $token, string $paymentGatewayCustomerId): ?array
    {
        $data = [];
        $data['items'][] = [
            'name' => $plan->name,
            //@TODO: use moneyphp for divide
            'price' => $plan->price / 100,
            'qty' => 1,
        ];
        $data['invoice_id'] = str_random();
        $data['invoice_description'] = $plan->name;
        //@TODO: use moneyphp for divide
        $data['total'] = $plan->price / 100;

        $result = $this->provider->doExpressCheckoutPayment($data, $token, $paymentGatewayCustomerId);

        if ($this->isCheckoutSuccess($result)) {
            $this->userEventLogger->log(
                new SubscriptionInitialPaymentSucceeded(
                    $user->id,
                    $user->plan_id,
                    $plan->id,
                    PaymentGateways::PAYPAL_ID,
                    $result['PAYMENTINFO_0_TRANSACTIONID']
                )
            );

            return $result;
        }

        $this->userEventLogger->log(
            new SubscriptionInitialPaymentFailed(
                $user->id,
                $user->plan_id,
                $plan->id,
                PaymentGateways::PAYPAL_ID
            )
        );

        return null;
    }

    /**
     * @param User $user
     * @param Plan $plan
     * @param string $token
     * @param string $paymentGatewayCustomerId
     *
     * @return bool
     *
     * @throws InvalidPaypalCheckoutTokenException
     * @throws PaypalCreateSubscriptionFailedException
     * @throws PaypalInstantPaymentFailedException
     * @throws \Throwable
     */
    public function subscribe(User $user, Plan $plan, string $token, string $paymentGatewayCustomerId): bool
    {
        $checkoutDetails = $this->getCheckoutDetails($token);
        if (!$this->isCheckoutSuccess($checkoutDetails)) {
            throw new InvalidPaypalCheckoutTokenException();
        }

        $instantPaymentResponse = $this->instantPayment($user, $plan, $token, $paymentGatewayCustomerId);
        if ($instantPaymentResponse === null) {
            throw new PaypalInstantPaymentFailedException();
        }

        $paypalSubscription = $this->createPaypalSubscription($plan, $token);
        if ($paypalSubscription === null) {
            throw new PaypalCreateSubscriptionFailedException();
        }

        \DB::transaction(function() use ($user, $plan, $paymentGatewayCustomerId, $checkoutDetails, $paypalSubscription, $instantPaymentResponse) {
            $paypalEmail = $checkoutDetails['EMAIL'];
            $paymentGatewaySubscriptionId = $paypalSubscription['PROFILEID'];

            $recurringPaymentProfileDetails = $this->getRecurringPaymentsProfileDetails($paymentGatewaySubscriptionId);

            $subscriptionStartsAt = Carbon::createFromTimeString($recurringPaymentProfileDetails['TIMESTAMP']);
            $subscriptionEndsAt = Carbon::createFromTimeString($recurringPaymentProfileDetails['NEXTBILLINGDATE']);

            $subscription = $this->subscriptionRepository->createSubscription(
                $user,
                $plan,
                PaymentGateways::PAYPAL_ID,
                $paymentGatewayCustomerId,
                $paymentGatewaySubscriptionId,
                $paypalEmail,
                SubscriptionStatuses::STATUS_ACTIVE_ID,
                $subscriptionStartsAt,
                $subscriptionEndsAt
            );

            $subscriptionPayment = $this->subscriptionRepository->createSubscriptionPayment(
                $subscription,
                $instantPaymentResponse['PAYMENTINFO_0_TRANSACTIONID'],
                //@TODO: use moneyphp for calculations
                $instantPaymentResponse['PAYMENTINFO_0_AMT'] * 100,
                //@TODO: use moneyphp for calculations
                $instantPaymentResponse['PAYMENTINFO_0_FEEAMT'] * 100,
                SubscriptionPaymentStatuses::STATUS_SUCCESS_ID
            );

            $this->subscriptionRepository->activateSubscription($user, $plan);

            $this->userEventLogger->log(
                new SubscriptionStarted(
                    $user->id,
                    $user->plan_id,
                    $plan->id,
                    PaymentGateways::PAYPAL_ID,
                    $subscription->id,
                    $subscriptionPayment->id
                )
            );
        });

        return true;
    }
}
