<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use MotionArray\Events\UserEvent\UserChangedPlan;
use MotionArray\Facades\Flash;
use MotionArray\Facades\Recaptcha;
use MotionArray\Http\Controllers\Shared\SessionsController as SessionsController;
use Carbon\Carbon;
use MotionArray\Http\Requests\Seller\StoreSellerRequest;
use MotionArray\Http\Requests\Seller\UpgradeUserToSellerRequest;
use MotionArray\Http\Requests\User\UpdateSelfRequest;
use MotionArray\Mailers\UserMailer;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Models\Role;
use MotionArray\Models\Plan;
use MotionArray\Models\Download;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use MotionArray\Repositories\BillingActionRepository;
use MotionArray\Repositories\PlanRepository;
use MotionArray\Repositories\ProjectRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Repositories\UserSubscriptionRepository;
use MotionArray\Support\UserEvents\UserEventLogger;
use MotionArray\Support\ValidationRules\UserPasswordCorrect;
use MotionArray\Services\Subscription\PaypalService;
use Stripe_Error;

class UsersController extends SessionsController
{
    public $createView = "site.sessions.create";
    public $destroyRedirect = "account/login";
    public $accessLevel = [
        1, // Super Admin
        2, // Admin
        3, // Seller
        4, // Customer
        5, // Legacy Customer
        6  // Freeloaders
    ];

    protected $userMailer;
    protected $feedbackMailer;
    /** @var UserRepository  */
    protected $userRepo;
    protected $planRepo;
    protected $billingAction;
    protected $project;
    /** @var UserSubscriptionRepository */
    protected $userSubscription;
    /**
     * @var UserEventLogger
     */
    private $userEventLogger;
    /**
     * @var PaypalService
     */
    private $paypalService;

    /**
     * UsersController constructor.
     * @param UserMailer $userMailer
     * @param FeedbackMailer $feedbackMailer
     * @param UserRepository $userRepository
     * @param PlanRepository $planRepository
     * @param BillingActionRepository $billingAction
     * @param ProjectRepository $project
     * @param PaypalService $paypalService
     * @param UserSubscriptionRepository $userSubscription
     * @param UserEventLogger $userEventLogger
     */
    public function __construct(
        UserMailer $userMailer,
        FeedbackMailer $feedbackMailer,
        UserRepository $userRepository,
        PlanRepository $planRepository,
        BillingActionRepository $billingAction,
        ProjectRepository $project,
        PaypalService $paypalService,
        UserSubscriptionRepository $userSubscription,
        UserEventLogger $userEventLogger
    )
    {
        $this->userMailer = $userMailer;
        $this->feedbackMailer = $feedbackMailer;
        $this->userRepo = $userRepository;
        $this->planRepo = $planRepository;
        $this->billingAction = $billingAction;
        $this->project = $project;
        $this->userSubscription = $userSubscription;
        $this->paypalService = $paypalService;
        $this->userEventLogger = $userEventLogger;
    }

    /**
     * This action is used to validate inputs before calling the store action. We use this because we want to show
     * the captcha on registration only after validation passed. So we need two separate calls for just validation
     * and storing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeValidateDefault(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted'
            ],
            User::$messages
        )->validate();

        return new JsonResponse(['message' => 'Validation passed.'], 200);
    }

    /**
     * This action is used to validate inputs before calling the store action. We use this because we want to show
     * the captcha on registration only after validation passed. So we need two separate calls for just validation
     * and storing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeValidateWithoutName(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted'
            ],
            User::$messages
        )->validate();

        return new JsonResponse(['message' => 'Validation passed.'], 200);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeWithoutName(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted'
            ],
            User::$messages
        )->validate();

        $recaptchaResponse = Recaptcha::verify($request->recaptchaToken, \Request::ip());

        if (!$recaptchaResponse->isSuccess()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Captcha failed.'
                ], 422);
            } else {
                Flash::danger("Error with recaptcha, please contact us if this persists.", "locked");
                return redirect()->back()->withInput();
            }
        }

        return $this->store($request);
    }

    /**
     * This action is used to validate inputs before calling the store action.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeValidateEmailPassword(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted'
            ],
            User::$messages
        )->validate();

        return new JsonResponse(['message' => 'Validation passed.'], 200);
    }

    /**
     * Register user with only email and password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeEmailPassword(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted',
                'stripe_token' => 'required'
            ],
            User::$messages
        )->validate();

        return $this->store($request);
    }

    /**
     * Register user with only email and password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeFullNameValidate(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'full_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted',
            ],
            User::$messages
        )->validate();

        return new JsonResponse(['message' => 'Validation passed.'], 200);
    }

    /**
     * Register user with only email and password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeFullName(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'full_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted',
                'stripe_token' => 'required',
            ],
            User::$messages
        )->validate();

        $fullName = explode(' ', $request->full_name, 2);
        $firstName = trim($fullName[0] ?? null);
        $lastName = trim($fullName[1] ?? null);
        $request->merge(['firstname' => $firstName, 'lastname' => $lastName]);

        return $this->store($request);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeDefault(Request $request)
    {
        Validator::make(
            $request->post(),
            [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'terms' => 'accepted'
            ],
            User::$messages
        )->validate();

        $recaptchaResponse = Recaptcha::verify($request->recaptchaToken, \Request::ip());

        if (!$recaptchaResponse->isSuccess()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Captcha failed.'
                ], 422);
            } else {
                Flash::danger("Error with recaptcha, please contact us if this persists.", "locked");
                return redirect()->back()->withInput();
            }
        }

        return $this->store($request);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function store(Request $request)
    {
        $p = '?p=free';

        $inputs = $request->all();

        $couponId = $request->get('coupon_id');

        /**
         * Set user attributes
         */
        $user = new User;

        $user->plan_id = 5; //Free plan
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $redirectUrl = "/sign-up/thank-you" . $p;

        //Check we don't have an existing user with this email address who previously deleted their account
        $restorable_user = $this->userRepo->findRestorableUser($inputs["email"]);

        if ($restorable_user) {
            Flash::danger("We noticed you had an account previously<br/> we just sent you an email with a link to restore your account");

            $this->userMailer->restoreAccount($restorable_user);

            return redirect()->back();
        }

        $user->portfolio_trial_ends_at = Carbon::now()->addDays(User::PORTFOLIO_TRIAL_LENGTH);

        $user->save();

        if (!$user->errors) {

            /**
             * Set user role
             */
            $user->roles()->save(Role::find(4)); // Set as customer

            /**
             * Send email welcome email
             */
            $this->userMailer->welcome($user);

            /**
             * Log the user in
             */
            auth()->login($user);

            $user = auth()->user();

            /**
             * If user was created through the checkout
             */
            if ($request->checkout == "true") {
                /** @var Plan $plan */
                $plan = Plan::where("billing_id", $inputs["billing_id"])->firstOrFail();

                $paymentMethod = $request->get('payment_method', 'stripe');

                if ($paymentMethod === 'paypal') {
                    $isNewCustomer = true;
                    $redirectUrl = $this->paypalService->createPaypalSubscriptionPaymentUrl($plan, $isNewCustomer);
                } else {
                    try {
                        $this->userSubscription->create($user, $plan, $inputs["stripe_token"], $couponId);

                        $p = '/paid?p=' . $plan->billing_id . '&s=new';

                        $this->feedbackMailer->newPayingCustomer($user);

                        $redirectUrl = "/sign-up/thank-you" . $p;

                    } catch (Stripe_Error $e) {
                        Flash::danger("There was a problem with your card. The error was: <br/><strong>" . $e->getMessage() . "</strong><br/>We've set you up on a <strong>free</strong> account. You can try your card again by upgrading to your desired plan below.", "locked");

                        $redirectUrl = "/account/upgrade";
                    }
                }
            }

            /**
             * If pre-downloading a free item
             */
            if (isset($request->pre_download) && $request->pre_download) {
                $download = new Download;
                $download->user_id = $user->id;
                $download->product_id = $request->pre_download;
                $download->first_downloaded_at = Carbon::now();
                $download->save();

                $redirectUrl = "/account/downloads";
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'redirect' => $redirectUrl
                ], 201);
            }

            return redirect()->to($redirectUrl);
        }

        /**
         * There was a problem created the user
         */
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'errors' => $user->errors
            ], 500);
        } else {
            Flash::danger("There was a problem creating your account.");

            return redirect()->back()->withInput()->withErrors($user->errors);
        }
    }

    /**
     * This action is used to validate inputs before calling the storeProducer action. We use this because we want to
     * show the captcha on registration only after validation passed. So we need two separate calls for just validation
     * and storing.
     *
     * @param StoreSellerRequest $request
     * @return JsonResponse
     */
    public function storeProducerValidate(StoreSellerRequest $request)
    {
        return new JsonResponse(['message' => 'Validation passed.'], 200);
    }

    /**
     * Create a new account with the seller role.
     *
     * @param StoreSellerRequest $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeProducer(StoreSellerRequest $request)
    {
        $recaptchaResponse = Recaptcha::verify($request->recaptchaToken, \Request::ip());

        if (!$recaptchaResponse->isSuccess()) {
            Flash::danger("Error with recaptcha, please contact us if this persists.", "locked");

            return redirect()->back()->withInput();
        }

        $data = $request->all();

        $data['plan_id'] = 5; // Free Plan

        /**
         * Create the user.
         */
        $data = Arr::only($data, [
            'email',
            'firstname',
            'lastname',
            'company_name',
            'password',
            'terms',
            'recaptchaToken',
            'plan_id']);
        $user = $this->userRepo->make($data, [3]);

        /**
         * If user was created
         */
        if (!$user->errors) {

            $this->userMailer->welcome($user);

            Auth::login($user);

            // Redirect to the thank you page
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'redirect' => "/sign-up/thank-you/producer"
                ], 201);
            } else {
                return redirect()->to("/sign-up/thank-you/producer");
            }
        }


        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'errors' => $user->errors
            ], 500);
        } else {
            Flash::danger("There was a problem creating your account.");

            return redirect()->back()->withInput()->withErrors($user->errors);
        }
    }

    /**
     * Upgrade an existing customer to be a seller.
     * @param UpgradeUserToSellerRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upgradeProducer(UpgradeUserToSellerRequest $request)
    {
        $data = $request->all();

        $user = Auth::user();

        if ($user) {
            $this->userRepo->upgradeToSeller($user, $data);

            // Redirect to my account area with flash.
            Flash::success("You have been upgraded to a Producer. You can now submit products for review.");

            // Redirect to the submissions page.
            return Redirect::to("/account/submissions");
        }

        return App::abort('404');
    }

    /**
     * Update the seller record.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeller(Request $request)
    {
        $data = $request->all();

        // Don't update seller's name if it is filled already
        if (\Auth::user()->company_name) {
            unset($data['company_name']);
        }

        $user = $this->userRepo->update(Auth::id(), $data);

        if ($user->errors) {
            return Response::json($user->errors, 500);
        }

        return Response::json("Your details have been updated successfully.", 200);
    }

    /**
     * @param UpdateSelfRequest $request
     * @return mixed
     */
    public function update(UpdateSelfRequest $request)
    {
        $inputs = $request->all();
        $user = Auth::user();

        $user->firstname = $inputs["firstname"];
        $user->lastname = $inputs["lastname"];
        if (isset($inputs["company_name"])) {
            $user->company_name = $inputs["company_name"];
        }
        if (isset($inputs['company'])) {
            $user->city = $inputs["city"];
            $user->state = $inputs["state"];
            $user->zip = $inputs["zip"];
            $user->country = $inputs["country"];
            $user->company = $inputs["company"];
            $user->address = $inputs["address"];
            $user->phone_number = $inputs["phone_number"];
        }
        if (isset($inputs["password"]) && $inputs['password']) {
            $user->password = Hash::make($inputs["password"]);
        }

        $resendConfirmation = true;
        if ($inputs["email"] === $user->email) {
            $resendConfirmation = false;
        } else {
            $user->email = $inputs["email"];
        }

        $user->save();

        if ($user->errors) {
            Flash::danger("There was a problem updating your details.");

            return Redirect::back()->withInput()->withErrors($user->errors);
        }

        if ($resendConfirmation) {
            $this->userMailer->confirmation($user);
        }
        $this->userSubscription->updateDescription($user);
        Flash::success("Your details have been updated successfully.");

        // If the password changed we need to reauthenticate, because updating the password invalidates old sessions.
        if ($inputs["password"]) {
            Auth::login($user);
        }

        return Redirect::back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCard(Request $request)
    {
        $user = Auth::user();

        $token = $request->stripe_token;
        $name =  explode(' ', trim($request->full_name), 2);
        $firstname = $name[0] ?? null;
        $lastname = $name[1] ?? null;

        $this->userRepo->updateBillingName($user, $firstname, $lastname);

        if (!$token) {
            return response()->json([
                'message' => "There was a problem updating your card."
            ], 422);
        }

        try {
            $this->userSubscription->updateCard($user, $token);

            $message = "Your card details have been updated successfully.";
            if (!$user->isSubscriptionActive()) {
                $message .= "<br/>It may take a few minutes for your account to be reactivated.";
            }

            return response()->json([
                'message' => $message
            ], 200);
        } catch (Stripe_Error $e) {
            return response()->json([
                'message' => "There was a problem updating your card. The error was: <br/><strong>" . $e->getMessage() . "</strong>"
            ], 500);
        }
    }

    /**
     * Confirm email address
     */
    public function setConfirmed($userId, $confirmationCode)
    {
        $user = User::findOrFail($userId);

        $user = $user->confirmEmail($confirmationCode);

        if ($user->confirmed) {
            Flash::info("Fantastic! Your email address has been confirmed.");
        } else {
            Flash::danger("The verification link provided is Invalid.");
        }

        if (Auth::check()) {
            return Redirect::to("account");
        }

        return Redirect::to("account/login");
    }

    /**
     * Create Subscription
     * @param Request $request
     * @return mixed
     */
    public function createSubscription(Request $request)
    {
        $user = Auth::user();
        $stripeToken = $request->stripe_token;
        $billing_id = $request->billing_id;
        $couponId = $request->coupon_id;
        $billingName = explode(' ', trim($request->full_name), 2);
        $firstname = $billingName[0] ?? null;
        $lastname = $billingName[1] ?? null;

        $sendFreeCustomerUpgraded = false;

        if (!$user->plan->isFree()) {
            // Already has a subscription
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Subscription exists already'
                ], 422);
            }

            return Redirect::to("/account/upgrade");
        }

        $plan = Plan::where("billing_id", "=", $billing_id)->first();

        try {
            if ($user->plan_id == Plans::FREE_ID) {
                $sendFreeCustomerUpgraded = true;
            }

            $this->userRepo->updateBillingName($user, $firstname, $lastname);

            $this->userSubscription->create($user, $plan, $stripeToken, $couponId);

        } catch (Stripe_Error $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 422);
            }

            Flash::danger("There was a problem updating your card. The error was: <br/><strong>" . $e->getMessage() . "</strong>", "locked");

            return Redirect::to("/account/upgrade");
        }

        /**
         * Upgrading from free account
         */
        $user = $user->refresh();
        if ($sendFreeCustomerUpgraded) {
            $this->feedbackMailer->freeCustomerUpgraded($user);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Subscription created'
            ], 201);
        }

        return Redirect::to("sign-up/thank-you/paid?p=" . $plan->billing_id . "&s=upgrade");
    }

    /**
     * Creates a Paypal Url for registered users - who have a FREE plan.
     * So they can upgrade from FREE to Monthly or FREE to Yearly
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function generatePaypalUrlAction(Request $request)
    {
        $user = Auth::user();
        $billing_id = $request->billing_id;

        if (!$user->plan->isFree()) {
            // Already has a subscription
            return response()->json([
                'error' => 'Subscription already exists.'
            ], 400);
        }

        $plan = Plan::where("billing_id", $billing_id)->first();

        $isNewCustomer = false;
        $redirectUrl = $this->paypalService->createPaypalSubscriptionPaymentUrl($plan, $isNewCustomer);

        return response()->json([
            'redirect' => $redirectUrl
        ], 201);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scheduleCancel(Request $request)
    {
        $user = Auth::user();

        $freePlan = Plan::where('billing_id', '=', 'free')->first();

        if ($user->onGracePeriod()) {
            $this->resumeSubscription();
        }

        $downgradeReason = $request->downgrade_reason;
        $downgradeFeedback = $request->downgrade_feedback;
        $this->userSubscription->downgradeReason($user, $downgradeReason, $downgradeFeedback);

        try {
            $this->billingAction->scheduleDowngrade($user, $freePlan);
        } catch (\Exception $exception) {
            $this->userSubscription->refresh($user);

            Flash::danger("Theres was an error. Please try again, if this persist <a href='/contact'>contact us</a>.", "locked");

            return Redirect::to('/account/upgrade');
        }

        return Redirect::to("/account/subscription");
    }

    /**
     * If theres an schedule downgrade for the user, downgrade now instead of waiting
     */
    public function downgradeNow()
    {
        $user = Auth::user();

        $billingAction = $this->userSubscription->downgradeNow($user);

        if ($billingAction && $billingAction->change_to_billing_id == 'free') {
            Flash::info("Your subscription has been cancelled.", "locked");
        } else {
            Flash::info("Your account has been downgraded.", "locked");
        }

        return Redirect::to("/account/subscription");
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelSubscription(Request $request)
    {
        $user = Auth::user();

        $this->userSubscription->cancelSubscription($user);

        $downgradeReason = $request->downgrade_reason;
        $downgradeFeedback = $request->downgrade_feedback;
        $this->userSubscription->downgradeReason($user, $downgradeReason, $downgradeFeedback);

        Flash::info("Your subscription has been cancelled.", "locked");

        return Redirect::to("/account/subscription");
    }

    public function resumeSubscription()
    {
        $user = Auth::user();

        $user->subscription($user->stripe_plan)->resume();

        // Ensure the account isn't disabled
        $user->subscription_expired = 0;
        $user->save();

        return Redirect::to("/account/subscription");
    }

    /**
     * Change User Subscription
     * @param Request $request
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function changeSubscription(Request $request)
    {
        $billing_id = $request->billing_id;

        /** @var Plan $plan */
        $plan = Plan::where("billing_id", $billing_id)->firstOrFail();

        /** @var User $user */
        $user = Auth::user();

        $p = "?p=" . $plan->billing_id . "&s=upgrade";

        if ($plan) {
            $oldPlan = $user->plan;

            /**
             * Cancel any current graceperiod
             */
            if ($user->onGracePeriod()) {
                $this->resumeSubscription();
            }

            /**
             * Determine if this is an upgrade or a downgrade
             */
            if ($this->planRepo->isDowngradingPlans($oldPlan, $plan)) {
                $this->userEventLogger->log(new UserChangedPlan($user->id, $plan->id));
                /**
                 * Create billing action to schedule downgrade
                 */
                $this->billingAction->scheduleDowngrade($user, $plan);

                /**
                 * Send email to MA team
                 */
                $this->feedbackMailer->downgradeSubscription($user, $oldPlan, $plan);

                return Redirect::to("/account/subscription");
            } else {

                try {
                    $success = $this->userSubscription->upgrade($user, $plan);

                    if ($success) {
                        /**
                         * Send upgraded email
                         */
                        $this->userMailer->upgraded($user);

                        /**
                         * Send email to MA team
                         */
                        $this->feedbackMailer->upgradeSubscription($user, $oldPlan, $plan);

                        $this->userEventLogger->log(new UserChangedPlan($user->id, $plan->id));

                        /**
                         * Redirect to thank you page
                         */
                        return redirect("sign-up/thank-you/paid" . $p);
                    }
                } catch (Stripe_Error $e) {
                    /**
                     * Return exception error as flash message
                     */
                    Flash::danger("There was a problem with your card. The error was: <br/><strong>" . $e->getMessage() . "</strong><br/>We've set you up on a <strong>free</strong> account. You remain on your exisiting subscription. Please update your card details and try changing your subscription again.", "locked");

                    return redirect("/account/billing");
                }
            }
        }

        /**
         * Return to subscription page
         */
        return Redirect::to("/account/subscription");
    }

    public function cancelDowngrade()
    {
        $this->billingAction->deleteScheduledDowngradesForUser(Auth::user());

        Flash::success("Your downgrade request has been cancelled successfully.");

        return Redirect::to("/account/subscription");
    }

    public function downloadInvoice($invoice_id)
    {
        $invoice = null;

        if (Auth::check()) {
            $invoice = Auth::user()->findInvoice($invoice_id);

            return $invoice->pdfDownload([
                'vendor' => 'Motion Array',
                'product' => Auth::user()->stripe_plan
            ]);
        }

        if (!$invoice) {
            return 'Invalid invoice ID.';
        }
    }
    /**
     * Fix a Laravel bug
     * https://github.com/laravel/framework/issues/27105
     * Where `password_hash` is kept in the session after logout,
     * resulting in the next login failure.
     */
    public function logout()
    {
        request()->session()->forget('password_hash');
        return parent::logout();
    }

    public function delete()
    {
        $user = $this->userRepo->delete(Auth::user()->id);

        Auth::logout();

        if ($user) {
            Flash::success('Your account has been deleted.');

            return redirect()->to('/account/login');
        }

        Flash::danger('Unable to delete account');

        return redirect()->back();
    }

    public function restore($userId, $token)
    {
        $user = $this->userRepo->findById($userId, true);

        if (Password::tokenExists($user, $token)) {
            if ($this->userRepo->restoreUser($user)) {
                Flash::success('Your account has been restored.');
            }
        }

        return Redirect::to('account/login');
    }
}
