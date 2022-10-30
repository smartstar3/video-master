<?php

namespace MotionArray\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use HavocInspired\Cashier\BillableInterface;
use HavocInspired\Cashier\BillableTrait;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Notifications\ResetPassword;
use MotionArray\Services\Intercom\IntercomService;
use MotionArray\Services\Intercom\IntercomUserObserver;
use MotionArray\Support\Database\CacheQueryBuilder;
use MotionArray\Traits\PresentableTrait;
use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, BillableInterface
{
    use SoftDeletes;
    use Authenticatable, CanResetPassword;
    use Authorizable;
    use BillableTrait;
    use PresentableTrait;
    use CacheQueryBuilder;
    use HasApiTokens;
    use Notifiable;
    use Seller;

    protected $billingCycleAnchor = 'now';

    protected $appends = ['is_plan_free', 'is_admin'];

    const PORTFOLIO_TRIAL_LENGTH = 30;

    const CONTENT_REMOVAL_NO_NOTIFICATION_STATUS = 0;
    const CONTENT_REMOVAL_FIRST_NOTIFICATION_STATUS = 1;
    const CONTENT_REMOVAL_SECOND_NOTIFICATION_STATUS = 2;
    const CONTENT_REMOVAL_FINAL_NOTIFICATION_STATUS = 3;

    protected $presenter = 'MotionArray\Presenters\UserPresenter';

    public static $messages = [
        'full_name.required' => 'We need to know your name',
        'firstname.required' => 'We need to know your firstname',
        'lastname.required' => 'We need to know your lastname',
        'email.required' => 'We need to know your email address',
        'email.email' => 'This email address looks invalid',
        'email.unique' => 'That email address has already been used',
        'password.required' => 'You need to set a password',
        'stripe_token.required' => 'Did not receive Stripe token',
        'terms.accepted' => 'Please accept Motion Array\'s Terms of Service and Privacy Policy'
    ];

    public static $adminRoles = [
        Roles::SUPER_ADMIN_ID,
        Roles::ADMIN_ID,
    ];

    protected $periodEndDate;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];
    protected $dates = ['portfolio_trial_ends_at', 'trial_ends_at', 'subscription_ends_at', 'access_starts_at', 'access_expires_at', 'period_end_at', 'deleted_at'];
    protected $fillable = ['plan_id', 'firstname', 'lastname', 'company_name', 'payout_method', 'paypal_email', 'payoneer_id', 'payoneer_confirmed', 'slug', 'email', 'password', 'password_confirmation', 'access_starts_at', 'access_expires_at', 'upload_rules_accepted'];

    protected $casts = [
        'stripe_created_at' => 'timestamp'
    ];

    /**
     * Extend boot method.
     */
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {
            $user->submissions()->delete();
            $user->products()->delete();
            $user->collections()->delete();
        });

        static::restoring(function ($user) {
            $user->collections()->withTrashed()->restore();
        });

        /**
         * Notify the user for critical changes:
         * Email, Password or payout method
         */
        static::updating(function ($user) {
            $original = $user->getOriginal();
            $mailer = new UserMailer();
            if (isset($user->email) && $user->email != $original['email']) {
                $mailer->importantChange($user, 'email');
            } elseif (
                (isset($user->paypal_email) && $user->paypal_email != $original['paypal_email']) ||
                (isset($user->payoneer_id) && $user->payoneer_id != $original['payoneer_id'])
            ) {
                $mailer->importantChange($user, 'payout information');
            }
        });

        if (app(IntercomService::class)->enabled()) {
            static::observe(IntercomUserObserver::class);
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    /**
     * Country
     * Note that this is based on country_id, and shouldn't be confused with the `country` text field
     * @return Country
     */
    public function country()
    {
        return $this->belongsTo('MotionArray\Models\Country', 'country_id');
    }

    public function upvotes()
    {
        return $this->hasMany('MotionArray\Models\RequestUpvote');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Returns user's active subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->active();
    }

    /**
     * Returns user's last subscription. It doesn't matter if it is active or not.
     *
     * @param int|null $paymentGatewayId
     *
     * @return Subscription|null
     */
    public function getLastSubscription(int $paymentGatewayId = null): ?Subscription
    {
        return $this->subscriptions()
            ->when($paymentGatewayId, function($query) use ($paymentGatewayId) {
                $query->where('payment_gateway_id', $paymentGatewayId);
            })
            ->latest()
            ->first();
    }

    public function roles()
    {
        return $this->belongsToMany('MotionArray\Models\Role', 'user_role');
    }

    public function accessServices()
    {
        return $this->belongsToMany('MotionArray\Models\AccessService', 'user_access_service');
    }

    public function plan()
    {
        return $this->belongsTo('MotionArray\Models\Plan');
    }

    public function projects()
    {
        return $this->hasMany('\MotionArray\Models\Project');
    }

    public function activeProjects()
    {
        return $this->projects()->active()->public();
    }

    public function activeReviews()
    {
        return $this->projects()->withReview()->active();
    }

    public function requests()
    {
        return $this->hasMany('MotionArray\Models\Request');
    }

    public function downloads()
    {
        return $this->hasMany('MotionArray\Models\Download')->withTrashed();
    }

    public function collections()
    {
        return $this->hasMany('MotionArray\Models\Collection');
    }

    public function billingActions()
    {
        return $this->hasMany('MotionArray\Models\BillingAction');
    }

    public function site()
    {
        return $this->hasOne('MotionArray\Models\UserSite');
    }

    public function downgrades()
    {
        return $this->hasMany('MotionArray\Models\UserDowngrade');
    }

    public function confirmationToken()
    {
        return $this->hasOne('MotionArray\Models\ConfirmationToken');
    }

    public function tokens()
    {
        return $this->hasMany('MotionArray\Models\UserToken');
    }

    public function portfolioThemes()
    {
        return $this->hasMany('MotionArray\Models\PortfolioTheme');
    }

    public function userIps()
    {
        return $this->hasMany('MotionArray\Models\UserIp');
    }

    /*
    |--------------------------------------------------------------------------
    | Getters and Setters
    |--------------------------------------------------------------------------
    */

    /**
     * Removes invalid phone characters
     *
     * @param $value
     *
     * @return mixed
     */
    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = preg_replace('#[^[0-9\)\(\+]]*#', '', $value);
    }

    public function getFullNameAttribute()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }

    public function getNameAndLastInitialAttribute()
    {
        return trim($this->firstname . ' ' . substr($this->lastname, 0, 1));
    }

    public function getPortfolioTrialEndsAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getPortfolioTrialRemainingAttribute()
    {
        $now = Carbon::now();

        if ($this->plan->isFree() && $this->portfolio_trial_ends_at && $this->portfolio_trial_ends_at->gt($now)) {
            $days = $now->diffInDays($this->portfolio_trial_ends_at);

            $hours = $now->diffInHours($this->portfolio_trial_ends_at) - (24 * $days);

            return [
                'days' => $days,
                'hours' => $hours
            ];
        }
    }

    public function getPortfolioTrialExpiredAttribute()
    {
        return $this->portfolio_trial_ends_at && $this->portfolio_trial_ends_at < Carbon::now();
    }

    /**
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $firstLine = $this->address;
        $secondLine = $this->city .
            ($this->city && $this->state ? ', ' : '') .
            $this->state .
            ($this->city || $this->state ? '. ' : '') .
            $this->zip;
        $thirdLine = $this->country;
        $address = $firstLine .
            ($firstLine && $secondLine ? '<br>' : '') .
            $secondLine .
            ($secondLine && $thirdLine ? '<br>' : '') .
            $thirdLine;

        return $address;
    }

    public function getIsAdminAttribute()
    {
        return $this->isAdmin();
    }

    public function getIsPlanFreeAttribute()
    {
        // todo: remove if, check plan_id is always present on Model
        if ($this->plan) {
            return $this->plan->isFree();
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Repo Methods
    |--------------------------------------------------------------------------
    | These methods may be moved to a repository later on in the development
    | cycle as needed.
    */
    /**
     * Get a boolean value for the force_log_out flag.
     *
     * @return bool
     */
    public function forceLogOut()
    {
        if ($this->force_log_out) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the force_log_out flag.
     */
    public function setForceLogOut($value)
    {
        $this->force_log_out = $value;
    }

    /**
     * @param $session
     */
    public function setSession($session)
    {
        $this->session_id = $session;
    }

    /**
     * @param $product_id
     *
     * @return bool|Download
     */
    public function hasDownloadedProductBefore($product_id)
    {
        $downloadRecord = Download::withTrashed()
            ->where('active', 1)
            ->where('user_id', $this->id)
            ->where('product_id', $product_id)
            ->first();

        if ($downloadRecord) {
            return $downloadRecord;
        }

        return false;
    }

    public function isConfirmed()
    {
        // Exception: Users created before implementation are not required to confirm their emails
        $ignoreConfirm = $this->created_at < Carbon::createFromDate(2016, 06, 21);

        return $this->confirmed || $ignoreConfirm || !$this->plan->isFree() || $this->isFreeloader();
    }

    public function isAuthenticatedUser()
    {
        if (Auth::check() && $this->id === Auth::user()->id) {
            return true;
        }

        return false;
    }

    public function getPremiumDownloadsCount($periodStart = null, $periodEnd = null)
    {
        if (!$periodStart) {
            $periodStart = $this->getPeriodRenewalDate()->subMonth();
        }

        if (!$periodEnd) {
            $periodEnd = $this->getPeriodRenewalDate();
        }

        $premiumDownloadsCount = $this->downloads()->withTrashed()->leftJoin('products', function ($join) {
            $join->on('products.id', '=', 'downloads.product_id');
        })->select('downloads.*')
            ->where(function ($query) {
                Download::premiumScope($query);
            })
            ->whereBetween('downloads.first_downloaded_at', [$periodStart, $periodEnd])
            ->count();

        return $premiumDownloadsCount;
    }

    public function getPeriodRenewalDate()
    {
        if ($this->subscribed()) {
            if (!$this->periodEndDate) {
                if (!$this->period_end_at || $this->period_end_at < Carbon::now()) {
                    if ($this->plan->cycle == 'yearly') {
                        $this->period_end_at = $this->getEndOfPeriodFromStripe();
                    } else {
                        $this->period_end_at = $this->subscription()->getSubscriptionEndDate();
                    }

                    $this->save();
                }

                $this->periodEndDate = $this->period_end_at;
            }

            return clone $this->periodEndDate;
        }

        if ($this->isFreeloader()) {
            if (!$this->periodEndDate) {
                $periodEnd = $this->access_starts_at->addMonth();

                $current_date = Carbon::now();

                while ($current_date > $periodEnd) {
                    $periodEnd = $periodEnd->addMonth();
                }

                $this->periodEndDate = $periodEnd;
            }

            return clone $this->periodEndDate;
        }
    }

    public function getBillingRenewalDate()
    {
        return $this->subscription()->getSubscriptionEndDate();
    }

    public function isAdmin()
    {
        foreach (static::$adminRoles as $role_id) {
            if ($this->hasRole($role_id)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(1);
    }

    public function isSeller()
    {
        return $this->hasRole(3);
    }

    /**
     * Is freeloader?
     *
     * @return bool
     */
    public function isFreeloader()
    {
        return $this->hasRole(6);
    }

    public function canUpload()
    {
        if (!$this->plan->isFree() || ($this->portfolio_trial_ends_at && $this->portfolio_trial_ends_at->gt(Carbon::now()))) {
            return true;
        }

        return false;
    }

    public function isPayingMember()
    {
        return $this->subscribed() && !$this->plan->isFree() && $this->isSubscriptionActive();
    }

    /**
     *  Check to see if the user's subscription is active (if they have one).
     */
    public function isSubscriptionActive()
    {
        if ($this->plan->billing_id === Plans::FREE) {
            return true;
        }

        $activeSubscriptionExists = $this->activeSubscription()->exists();

        // If user doesn't have a subscription, return false
        if (!$this->stripe_subscription && !$activeSubscriptionExists) {
            return false;
        }

        // Check: Stripe user, with an expired subscription, that's not the free plan.
        if ($this->stripe_subscription && $this->subscription_expired) {
            // Subscription expired
            return false;
        }

        // Subscription active
        return true;
    }

    public function onDowngradeGracePeriod($billingId = null)
    {
        $query = BillingAction::where('user_id', '=', $this->id)
            ->where('action', '=', 'downgrade');

        if ($billingId) {
            $query->where('change_to_billing_id', '=', $billingId);
        }

        return $query->exists();
    }

    public function hasRole($role_id)
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->where('id', $role_id)->count() > 0;
        }

        return $this->roles()->where('role_id', '=', $role_id)->exists();
    }

    public function hasAccessService($service_id)
    {
        $access_service = $this->AccessServices()->where('access_service_id', $service_id)->first();

        if ($access_service) {
            return true;
        }

        return false;
    }

    public function hasAccessUser($user = null)
    {
        if ($user) {
            $role = $user->roles()->where('role_id', '<', 3)->first();
        }

        if (!$user || !$role) {
            $access_service = $this->AccessServices()->where('access_service_id', '>', 8)->where('access_service_id', '<', 14)->first();

            if ($access_service) {
                return true;
            }
        }

        return false;
    }

    public function hasAccessSubmission()
    {
        $access_service = $this->AccessServices()->where('access_service_id', '<', 9)->first();
        if ($access_service)
            return true;

        return false;
    }

    public function getEndOfPeriod()
    {
        $periodEnd = null;
        try {
            if ($this->isFreeloader()) {
                $periodEnd = $this->getPeriodRenewalDate();
            } else {
                $periodEnd = $this->getEndOfPeriodFromStripe();
            }
        } catch (\Exception $e) {
        }

        return $periodEnd;
    }

    /**
     * @return mixed
     */
    public function getEndOfPeriodFromStripe()
    {
        $periodEnd = $this->subscription()->getSubscriptionEndDate()->subYear()->addMonth();

        $current_date = Carbon::now();

        while ($current_date > $periodEnd) {
            $periodEnd = $periodEnd->addMonth();
        }

        return $periodEnd;
    }

    public function createConfirmationToken()
    {
        $token = str_random();
        $confirmationToken = $this->confirmationToken()->create([
            'email' => $this->email,
            'token' => $token
        ]);

        return $confirmationToken->token;
    }

    public function confirmEmail($confirmationCode)
    {
        $yesterday = Carbon::now()->subDay();
        $token = $this->confirmationToken()
            ->where('token', '=', $confirmationCode)
            ->where('email', '=', $this->email)
            ->where('created_at', '>', $yesterday)
            ->first();
        if ($token) {
            $this->confirmed = 1;
            $this->save();
        }

        return $this;
    }

    /**
     * Get Last User Downgrade Relation
     *
     * @return mixed
     */
    public function getLastDowngrade()
    {
        return $this->downgrades()->latest()->first();
    }

    public function scopeDataNotRemoved($query)
    {
        return $query->where('firstname', '!=', 'removed');
    }

    public function scopeOnFreePlan($query)
    {
        return $query->where('plan_id', '=', Plans::FREE_ID);
    }
}
