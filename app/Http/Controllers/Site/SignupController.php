<?php namespace MotionArray\Http\Controllers\Site;

use Bugsnag\Report;
use MotionArray\Services\Subscription\Exceptions\Paypal\InvalidPaypalCheckoutTokenException;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalCreateSubscriptionFailedException;
use MotionArray\Services\Subscription\Exceptions\Paypal\PaypalInstantPaymentFailedException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use MotionArray\Facades\Flash;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Models\User;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\Plan;
use MotionArray\Services\Subscription\PaypalService;
use MotionArray\Support\UserEvents\UserEventLogger;
use Stripe_Error;
use Stripe_Coupon;

/**
 * Class SignupController
 *
 * @package MotionArray\Http\Controllers\Site
 */
class SignupController extends BaseController
{

    protected $product;
    protected $redirectTo = "/";
    protected $paginationRange = 10;
    /**
     * @var PaypalService
     */
    private $paypalService;
    /**
     * @var FeedbackMailer
     */
    private $feedbackMailer;
    /**
     * @var UserEventLogger
     */
    private $userEventLogger;

    /**
     * SignupController constructor.
     *
     * @param ProductRepository $product
     */
    public function __construct(
        ProductRepository $product,
        PaypalService $paypalService,
        FeedbackMailer $feedbackMailer,
        UserEventLogger $userEventLogger
    ) {
        $this->product = $product;
        $this->paypalService = $paypalService;
        $this->feedbackMailer = $feedbackMailer;
        $this->userEventLogger = $userEventLogger;
    }

    /**
     * New User Checkout
     *
     * @param Request $request
     * @return Factory|RedirectResponse|View
     */
    public function checkout(Request $request)
    {
        if (auth()->check()) {
            return redirect("account/upgrade");
        }

        Flash::clear();

        $billing_id = $request->input("plan");
        $code = $request->input("discount");

        $plan = Plan::where('billing_id', "=", $billing_id)->first();

        $coupon = null;
        if ($code) {
            try {
                $coupon = Stripe_Coupon::retrieve($code, config('services.stripe.secret'))->__toArray();
            } catch (Stripe_Error $e) {
                $coupon = null;
            }
        }

        if ($plan) {
            return view("site.signup.checkout", compact("plan", "billing_id", "coupon", "sale"));
        }

        return redirect("/pricing");
    }

    /**
     * Thank you page
     *
     * @param Request $request
     *
     * @return Factory|RedirectResponse|View
     */
    public function thankYou(Request $request)
    {
        if (!auth()->check()) {
            return redirect('pricing');
        }

        $prevUrl = Session::get('redirect_to_product_url');
        $prevCollectionUrl = Cookie::get('last_freemium_collection_url');

        if ($request->input('s') === 'upgrade') {
            $view = 'site.signup.thank-you-upgrade';
        } else {
            $view = 'site.signup.thank-you';
        }

        return view($view)
            ->with([
                'prevUrl' => $prevUrl,
                'prevCollectionUrl' => $prevCollectionUrl,
            ]);
    }

    /**
     * Thank you producer page
     *
     * @return Factory|View
     */
    public function thankYouProducer()
    {
        if (auth()->guest()) {
            return redirect(url('/become-a-producer'));
        }

        if (!auth()->user()->isSeller()) {
            return redirect(url('/sign-up/thank-you/paid'));
        }

        return view("site.signup.thank-you-producer");
    }

    /**
     * The old sign up page is disabled, so if anything links to it, redirect to pricing page with new sign up form
     */
    public function redirectToPricing()
    {
        return redirect('/pricing', 301);
    }

    /**
     * URL: /sign-up/paypal-handler
     *
     * Paypal sends a POST request to this endpoint. And after logic is done,
     * we redirect user thank-you page.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Redirector
     *
     * @throws \Throwable
     */
    public function paypalPostBackHandler(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $payload = decrypt($request->get('payload'));
        $isUpgradeFromFree = $payload['type'] === 'upgrade';

        $plan = Plan::findOrFail($payload['plan_id']);
        try {
            $result = $this->paypalService->subscribe($user, $plan, $request->get('token'), $request->get('PayerID'));

            if ($result) {
                if ($isUpgradeFromFree === true) {
                    $this->feedbackMailer->freeCustomerUpgraded($user);
                } else {
                    $this->feedbackMailer->newPayingCustomer($user);
                }
            } else {
                \Flash::danger('Error with subscription. Please contact with support!', 'locked');

                $redirectUrl = url('/account/upgrade?plan='.$plan->billing_id);

                return redirect($redirectUrl);
            }
        } catch (InvalidPaypalCheckoutTokenException $exception) {
            \Flash::danger('Error with subscription. Please try again!', 'locked');

            return redirect(url('/account/upgrade?plan='.$plan->billing_id));
        } catch (PaypalInstantPaymentFailedException $exception) {
            \Flash::danger('Error with subscription. Please try again!', 'locked');

            return redirect(url('/account/upgrade?plan='.$plan->billing_id));
        } catch (PaypalCreateSubscriptionFailedException $exception) {
            \Bugsnag::notifyException($exception, function(Report $report) use ($payload) {
                $report->setMetaData([
                    'payload' => $payload,
                ]);
            });
            \Flash::danger('Error with subscription. Please contact with support!', 'locked');

            return redirect(url('/account/upgrade?plan='.$plan->billing_id));
        }

        return redirect('/sign-up/thank-you/paid?s='.($isUpgradeFromFree ? 'upgrade' : ''));
    }
}
