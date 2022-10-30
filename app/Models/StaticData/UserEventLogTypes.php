<?php

namespace MotionArray\Models\StaticData;

use MotionArray\Events\UserEvent\Admin\UserChangedSubscriptionToMonthly;
use MotionArray\Events\UserEvent\Admin\UserCreated;
use MotionArray\Events\UserEvent\Admin\UserDeleted;
use MotionArray\Events\UserEvent\Admin\UserDisabled;
use MotionArray\Events\UserEvent\Admin\UserDowngraded;
use MotionArray\Events\UserEvent\Admin\UserEnabled;
use MotionArray\Events\UserEvent\Admin\UserForceLogoutDisabled;
use MotionArray\Events\UserEvent\Admin\UserForceLogoutEnabled;
use MotionArray\Events\UserEvent\Admin\UserFreeloaderUpdated;
use MotionArray\Events\UserEvent\Admin\UserLoginAs;
use MotionArray\Events\UserEvent\Admin\UserPasswordUpdated;
use MotionArray\Events\UserEvent\Admin\UserRoleChanged;
use MotionArray\Events\UserEvent\Admin\UserUpdated;
use MotionArray\Events\UserEvent\Subscription\SubscriptionInitialPaymentFailed;
use MotionArray\Events\UserEvent\Subscription\SubscriptionInitialPaymentSucceeded;
use MotionArray\Events\UserEvent\Subscription\SubscriptionResumedAfterPaymentHold;
use MotionArray\Events\UserEvent\Subscription\SubscriptionStarted;
use MotionArray\Events\UserEvent\Subscription\SubscriptionSuspendedByPaymentOnHold;
use MotionArray\Events\UserEvent\UserChangedPlan;
use MotionArray\Events\UserEvent\UserDisabledByReachingDownloadLimit;
use MotionArray\Events\UserEvent\UserDowngradedByPaymentFailure;
use MotionArray\Events\UserEvent\UserFreeloaderExpired;
use MotionArray\Models\UserEventLogType;

class UserEventLogTypes extends StaticDBData
{
    public const USER_ENABLED = UserEnabled::class;
    public const USER_ENABLED_ID = 1;

    public const USER_DISABLED = UserDisabled::class;
    public const USER_DISABLED_ID = 2;

    public const USER_DELETED = UserDeleted::class;
    public const USER_DELETED_ID = 3;

    public const USER_FORCE_LOGOUT_ENABLED = UserForceLogoutEnabled::class;
    public const USER_FORCE_LOGOUT_ENABLED_ID = 4;

    public const USER_FORCE_LOGOUT_DISABLED = UserForceLogoutDisabled::class;
    public const USER_FORCE_LOGOUT_DISABLED_ID = 5;

    public const USER_FREELOADER_UPDATED = UserFreeloaderUpdated::class;
    public const USER_FREELOADER_UPDATED_ID = 6;

    public const USER_LOGIN_AS = UserLoginAs::class;
    public const USER_LOGIN_AS_ID = 7;

    public const USER_ROLE_CHANGED = UserRoleChanged::class;
    public const USER_ROLE_CHANGED_ID = 8;

    public const USER_FREELOADER_EXPIRED = UserFreeloaderExpired::class;
    public const USER_FREELOADER_EXPIRED_ID = 9;

    public const USER_CREATED = UserCreated::class;
    public const USER_CREATED_ID = 10;

    public const USER_UPDATED = UserUpdated::class;
    public const USER_UPDATED_ID = 11;

    public const USER_DOWNGRADED = UserDowngraded::class;
    public const USER_DOWNGRADED_ID = 12;

    public const USER_PASSWORD_UPDATED = UserPasswordUpdated::class;
    public const USER_PASSWORD_UPDATED_ID = 13;

    public const USER_CHANGED_SUBSCRIPTION_TO_MONTHLY = UserChangedSubscriptionToMonthly::class;
    public const USER_CHANGED_SUBSCRIPTION_TO_MONTHLY_ID = 14;

    public const USER_DISABLED_BY_REACHING_DOWNLOAD_LIMIT = UserDisabledByReachingDownloadLimit::class;
    public const USER_DISABLED_BY_REACHING_DOWNLOAD_LIMIT_ID = 15;

    public const USER_CHANGED_PLAN = UserChangedPlan::class;
    public const USER_CHANGED_PLAN_ID = 16;

    public const USER_DOWNGRADED_BY_PAYMENT_FAILURE = UserDowngradedByPaymentFailure::class;
    public const USER_DOWNGRADED_BY_PAYMENT_FAILURE_ID = 17;

    public const SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD = SubscriptionSuspendedByPaymentOnHold::class;
    public const SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD_ID = 18;

    public const SUBSCRIPTION_STARTED = SubscriptionStarted::class;
    public const SUBSCRIPTION_STARTED_ID = 19;

    public const SUBSCRIPTION_INITIAL_PAYMENT_SUCCEEDED = SubscriptionInitialPaymentSucceeded::class;
    public const SUBSCRIPTION_INITIAL_PAYMENT_SUCCEEDED_ID = 20;

    public const SUBSCRIPTION_INITIAL_PAYMENT_FAILED = SubscriptionInitialPaymentFailed::class;
    public const SUBSCRIPTION_INITIAL_PAYMENT_FAILED_ID = 21;

    public const SUBSCRIPTION_RESUMED_AFTER_PAYMENT_HOLD = SubscriptionResumedAfterPaymentHold::class;
    public const SUBSCRIPTION_RESUMED_AFTER_PAYMENT_HOLD_ID = 22;

    protected $modelClass = UserEventLogType::class;

    protected $data = [
        [
            'id' => self::USER_ENABLED_ID,
            'event_class' => self::USER_ENABLED,
        ],
        [
            'id' => self::USER_DISABLED_ID,
            'event_class' => self::USER_DISABLED,
        ],
        [
            'id' => self::USER_DELETED_ID,
            'event_class' => self::USER_DELETED,
        ],
        [
            'id' => self::USER_FORCE_LOGOUT_ENABLED_ID,
            'event_class' => self::USER_FORCE_LOGOUT_ENABLED,
        ],
        [
            'id' => self::USER_FORCE_LOGOUT_DISABLED_ID,
            'event_class' => self::USER_FORCE_LOGOUT_DISABLED,
        ],
        [
            'id' => self::USER_FREELOADER_UPDATED_ID,
            'event_class' => self::USER_FREELOADER_UPDATED,
        ],
        [
            'id' => self::USER_LOGIN_AS_ID,
            'event_class' => self::USER_LOGIN_AS,
        ],
        [
            'id' => self::USER_ROLE_CHANGED_ID,
            'event_class' => self::USER_ROLE_CHANGED,
        ],
        [
            'id' => self::USER_FREELOADER_EXPIRED_ID,
            'event_class' => self::USER_FREELOADER_EXPIRED,
        ],
        [
            'id' => self::USER_CREATED_ID,
            'event_class' => self::USER_CREATED,
        ],
        [
            'id' => self::USER_UPDATED_ID,
            'event_class' => self::USER_UPDATED,
        ],
        [
            'id' => self::USER_DOWNGRADED_ID,
            'event_class' => self::USER_DOWNGRADED,
        ],
        [
            'id' => self::USER_PASSWORD_UPDATED_ID,
            'event_class' => self::USER_PASSWORD_UPDATED,
        ],
        [
            'id' => self::USER_CHANGED_SUBSCRIPTION_TO_MONTHLY_ID,
            'event_class' => self::USER_CHANGED_SUBSCRIPTION_TO_MONTHLY,
        ],
        [
            'id' => self::USER_DISABLED_BY_REACHING_DOWNLOAD_LIMIT_ID,
            'event_class' => self::USER_DISABLED_BY_REACHING_DOWNLOAD_LIMIT,
        ],
        [
            'id' => self::USER_CHANGED_PLAN_ID,
            'event_class' => self::USER_CHANGED_PLAN,
        ],
        [
            'id' => self::USER_DOWNGRADED_BY_PAYMENT_FAILURE_ID,
            'event_class' => self::USER_DOWNGRADED_BY_PAYMENT_FAILURE,
        ],
        [
            'id' => self::SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD_ID,
            'event_class' => self::SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD,
        ],
        [
            'id' => self::SUBSCRIPTION_STARTED_ID,
            'event_class' => self::SUBSCRIPTION_STARTED,
        ],
        [
            'id' => self::SUBSCRIPTION_INITIAL_PAYMENT_SUCCEEDED_ID,
            'event_class' => self::SUBSCRIPTION_INITIAL_PAYMENT_SUCCEEDED,
        ],
        [
            'id' => self::SUBSCRIPTION_INITIAL_PAYMENT_FAILED_ID,
            'event_class' => self::SUBSCRIPTION_INITIAL_PAYMENT_FAILED,
        ],
        [
            'id' => self::SUBSCRIPTION_RESUMED_AFTER_PAYMENT_HOLD_ID,
            'event_class' => self::SUBSCRIPTION_RESUMED_AFTER_PAYMENT_HOLD,
        ],
    ];

    public function idToEventClassOrFail($id): string
    {
        $id = $this->toIdOrFail($id);
        return $this->data()[$id]['event_class'];
    }

    protected static $classToId = [];

    public function classToIdOrFail($class): string
    {
        if (!static::$classToId) {
            $data = collect($this->data())->map->event_class;
            static::$classToId = array_flip($data->toArray());
        }
        return static::$classToId[$class];
    }
}
