<?php

namespace MotionArray\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use MotionArray\Events\UserEvent\Admin\UserChangedSubscriptionToMonthly;
use MotionArray\Http\Resources\UserManagerResource;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use MotionArray\Repositories\AdminUserRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Repositories\UserSubscriptionRepository;
use MotionArray\Support\UserEvents\UserEventLogger;
use Response;
use View;

class UserSubscriptionController extends BaseController
{
    /**
     * @var \MotionArray\Repositories\UserRepository
     */
    private $userRepository;

    /**
     * @var \MotionArray\Repositories\UserSubscriptionRepository
     */
    private $userSubscriptionRepository;

    /**
     * @var AdminUserRepository
     */
    private $adminUserRepo;

    /**
     * @var UserEventLogger
     */
    private $logger;

    public function __construct(
        UserRepository $userRepository,
        UserSubscriptionRepository $subscriptionRepository,
        AdminUserRepository $adminUserRepo,
        UserEventLogger $logger
    ) {
        $this->userRepository = $userRepository;
        $this->userSubscriptionRepository = $subscriptionRepository;
        $this->adminUserRepo = $adminUserRepo;
        $this->logger = $logger;
    }

    /**
     * Renders confirmation dialog view. Asks admins to if they really want to downgrade account.
     *
     * @param $user_id
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function confirmDowngrade($user_id)
    {
        $user = $this->userRepository->findById($user_id);
        if ($user) {
            return View::make('admin.user-manager._partials.confirm-downgrade', compact('user'));
        }

        return Response::make([], 404);
    }

    /**
     * Renders confirmation dialog view. Asks admins to if they really want to change subscription from yearly to monthly.
     * Also shows to admin, how many days user has been using their subscription.
     *
     * @param $user_id
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function confirmChangeSubscriptionToMonthly($user_id)
    {
        /** @var User $user */
        $user = $this->userRepository->findById($user_id);
        if ($user) {
            $usedDays = $this->userRepository->getSubscriptionUsageAsDays($user);
            $refundAmount = $this->userRepository->getSubscriptionRefundAmountForYearlyToMonthlyTransition($user);

            $subscriptionStartDate = $user->subscription()->getSubscriptionStartDate();
            $trialWillEndAt = $this->getTrialEndDate($subscriptionStartDate);

            return View::make('admin.user-manager._partials.confirm-change-subscription-to-monthly', [
                'user' => $user,
                'usedDays' => $usedDays,
                'refundAmount' => $refundAmount,
                'trialWillEndAt' => $trialWillEndAt,
            ]);
        }

        return Response::make([], 404);
    }

    /**
     * PUT request.
     * Cancels user's subscription and downgrades user's account to FREE.
     *
     * @param $user_id
     *
     * @return \Illuminate\Http\Response|UserManagerResource
     * @throws \Exception
     */
    public function downgrade($userId)
    {
        $user = $this->adminUserRepo->downgrade($userId);
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }
        $user->load(['roles', 'plan']);

        return new UserManagerResource($user);
    }

    /**
     * PUT request.
     *
     * - Partial refund for invoice
     * - Cancel active subscription
     * - Create new monthly subscription - with trial
     *
     * @param $userId
     * @return \Illuminate\Http\Response|UserManagerResource
     * @throws \Exception
     */
    public function changeSubscriptionToMonthly($userId)
    {
        /** @var User $user */
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return Response::make([], 404);
        }

        if (!$user->subscribed()) {
            return Response::make([], 422);
        }

        if (Plans::CYCLE_YEARLY !== $user->plan->cycle) {
            return Response::make([], 422);
        }

        $subscriptionStartDate = $user->subscription()->getSubscriptionStartDate();

        $refundAmountDecimal = $this->userRepository->getSubscriptionRefundAmountForYearlyToMonthlyTransition($user);
        $refundAmountPercent = $refundAmountDecimal * 100;

        $this->userSubscriptionRepository->refundInvoice(
            $user,
            $user->subscription()->getStripeCustomer()->subscription->latest_invoice,
            $refundAmountPercent
        );

        $this->userSubscriptionRepository->cancelSubscription($user);

        $trialWillEndAt = $this->getTrialEndDate($subscriptionStartDate);

        $plan = Plan::find(Plans::MONTHLY_UNLIMITED_2018_ID);
        $this->userSubscriptionRepository->createSubscriptionWithTrial($user, $plan, $trialWillEndAt);

        $user->load(['roles', 'plan']);
        $this->logger->log(new UserChangedSubscriptionToMonthly($user->id, Auth::user()));

        return new UserManagerResource($user);
    }

    /**
     * Example would be better to understand this method.
     *
     * Today: 2019-05-20
     *
     * Subscription Start: 2019-05-20
     * Trial Will End: 2019-06-20
     *
     * Subscription Start: 2019-05-10
     * Trial Will End: 2019-06-10
     *
     * Subscription Start: 2019-04-03
     * Trial Will End: 2019-06-03
     *
     * @param Carbon $subscriptionStartDate
     * @return Carbon
     */
    private function getTrialEndDate(Carbon $subscriptionStartDate)
    {
        $today = new Carbon();
        $trialWillEndAt = $subscriptionStartDate->copy()->addMonth(1);
        while ($trialWillEndAt <= $today) {
            $trialWillEndAt->addMonth(1);
        }

        return $trialWillEndAt;
    }
}
