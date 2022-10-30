<?php namespace MotionArray\Repositories;

use Carbon\Carbon;

use HavocInspired\Cashier\StripeGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use MotionArray\Helpers\Helpers;
use MotionArray\Models\Category;
use MotionArray\Models\Download;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\User;
use MotionArray\Models\Role;
use MotionArray\Models\Plan;
use MotionArray\Models\AccessService;
use MotionArray\Models\SellerPayout;
use MotionArray\Repositories\OAuth\AccessTokenRepository;
use DB;
use Request;

// todo: Remove the horrible usage of Request class inherited from previous devs

class UserRepository extends EloquentBaseRepository
{
    public $pagination = [
        "page_no" => 1,
        "items_per_page" => 24,
        "item_count" => 0,
        "pagination_range" => 10,
        "filters" => ""
    ];

    public $sorting = [
        "order_by" => "created_at",
        "order" => "desc",
        "filters" => ""
    ];

    /**
     * @var UserUploadRepository
     */
    protected $userUpload;

    /**
     * @var DownloadRepository
     */
    protected $download;

    protected $accessTokenRepository;

    /**
     * UserRepository constructor.
     * @param User $user
     * @param UserUploadRepository $userUpload
     * @param DownloadRepository $download
     * @param AccessTokenRepository $accessTokenRepository
     */
    public function __construct(
        User $user,
        UserUploadRepository $userUpload,
        DownloadRepository $download,
        AccessTokenRepository $accessTokenRepository
    )
    {
        $this->model = $user;
        $this->userUpload = $userUpload;
        $this->download = $download;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * @param $email
     *
     * @return mixed
     */
    public function findByEmail($email)
    {
        return User::where('email', '=', $email)->first();
    }

    /**
     * Get users
     *
     * @param array $options [page_no, items_per_page, order_by, order]
     *
     * @return mixed
     */
    public function getUsers(array $pagination = null, array $sorting = null, $get_all_users = false)
    {
        $pagination = $this->setPagination($pagination);
        $sorting = $this->setSorting($sorting);
        $query = User::leftJoin("downloads", "users.id", "=", "downloads.user_id")
            ->select(DB::raw("users.*, count(downloads.user_id) as downloads"));
        /**
         * Where customer type based on request
         */
        if (!is_null(Request::get("customers"))) {
            /**
             * Limit by new customers
             */
            if (Request::get("customers") == "new") {
                $query = $query->leftJoin("user_role", "users.id", "=", "user_role.user_id")
                    ->where("user_role.role_id", "=", 4);
            } /**
             * Limit by sellers
             */
            elseif (Request::get("customers") == "sellers") {
                $query = $query->leftJoin("user_role", "users.id", "=", "user_role.user_id")
                    ->where("user_role.role_id", "=", 1)
                    ->orWhere("user_role.role_id", "=", 2)
                    ->orWhere("user_role.role_id", "=", 3);
            }
        }
        /**
         * Limit by plan
         */
        if (!is_null(Request::get("plan"))) {
            if (Request::get("plan") !== "all") {
                $query = $query->where("users.plan_id", "=", Request::get("plan"));
            }
        }
        $query = $query->groupBy("users.id")
            ->orderBy($sorting["order_by"], $sorting["order"]);

        if (!$get_all_users) {
            $query = $query->skip(($pagination["page_no"] - 1) * $pagination["items_per_page"])
                ->take($pagination["items_per_page"]);
        }

        return $query->get();
    }

    /**
     * Get total user count
     * @return mixed
     */
    public function getUsersCount()
    {
        $query = null;
        /**
         * Where customer type based on request
         */
        if (!is_null(Request::get("customers"))) {
            /**
             * Limit by new customers
             */
            if (Request::get("customers") == "new") {
                $query = User::leftJoin("user_role", "users.id", "=", "user_role.user_id")
                    ->where("user_role.role_id", "=", 4);
            } /**
             * Limit by sellers
             */
            elseif (Request::get("customers") == "sellers") {
                $query = User::leftJoin("user_role", "users.id", "=", "user_role.user_id")
                    ->where("user_role.role_id", "=", 1)
                    ->orWhere("user_role.role_id", "=", 2)
                    ->orWhere("user_role.role_id", "=", 3);
            }
        }
        /**
         * Limit by plan
         */
        if (!is_null(Request::get("plan"))) {
            if (Request::get("plan") !== "all") {
                $query = $query ? $query->where("users.plan_id", "=", Request::get("plan")) : User::where("users.plan_id", "=", Request::get("plan"));
            }
        }

        return isset($query) ? $query->count() : User::count();
    }

    public function getSellersWithDownloads(Carbon $start, Carbon $end)
    {
        return User::whereIn('id', function ($query) use ($start, $end) {

            $query->select('seller_id')
                ->from(with(new Product())->getTable())
                ->whereIn('id', function ($query) use ($start, $end) {

                    $query->select('product_id')
                        ->from(with(new Download())->getTable())
                        ->where('first_downloaded_at', '>=', $start)
                        ->where('first_downloaded_at', '<=', $end)
                        ->whereNotNull('download_period_id')
                        ->groupBy('product_id');

                });

        })->get();
    }

    /**
     * Find a user by their payoneer id.
     *
     * @param String $payoneer_id The user's payoneer ID.
     *
     * @return User
     */
    public function findByPayoneerId($payoneerId)
    {
        if ($payoneerId) {
            $user = User::where('payoneer_id', '=', $payoneerId)->first();

            return $user;
        }
    }

    protected function getSearchQuery($q)
    {
        $parts = array_filter(explode(" ", trim($q)));
        $query = (new User)->newQuery();

        $isEmail = false;
        $isId = false;
        if (count($parts) === 1) {
            $isEmail = filter_var($parts[0], FILTER_VALIDATE_EMAIL);
            $isId = is_numeric($parts[0]);

            if ($isEmail) {
                $query->where('email', '=', trim($isEmail));
            }

            if ($isId) {
                $query->where('id', '=', trim($parts[0]));
            }
        }

        if (!$isEmail && !$isId) {
            $query->where(function ($q) use ($parts) {
                foreach ($parts as $part) {
                    $q->orWhere("id", "=", $part)
                        ->orWhere("firstname", "LIKE", "%$part%")
                        ->orWhere("lastname", "LIKE", "%$part%")
                        ->orWhere("email", "LIKE", "%$part%")
                        ->orWhere("stripe_id", "LIKE", "%$part%")
                        ->orWhere("paypal_email", "LIKE", "%$part%")
                        ->orWhere("payoneer_id", "LIKE", "%$part%")
                        ->orWhere("company_name", "LIKE", "%$part%");
                }
            });
        }

        return $query;
    }

    /**
     * Search for users
     *
     * @param $q
     * @param array $options
     *
     * @return mixed
     */
    public function search($q, array $pagination = null, array $sorting = null)
    {
        $pagination = $this->setPagination($pagination);

        $query = $this->getSearchQuery($q);

        return $query
            ->skip(($pagination["page_no"] - 1) * $pagination["items_per_page"])
            ->take($pagination["items_per_page"])
            ->get();
    }

    /**
     * Get total user count from search
     *
     * @param $q
     *
     * @return mixed
     */
    public function getSearchCount($q)
    {
        $query = $this->getSearchQuery($q);

        return $query->count();
    }

    /**
     * Make a new user
     *
     * @param array $attributes
     * @param array $role_ids
     *
     * @return User
     */
    public function make(array $attributes, array $role_ids = [4])
    {
        $user = new User;
        $user->fill($attributes);

        if ($user->company_name) {
            $user->slug = $this->slugify($user->company_name);
        }

        $user->portfolio_trial_ends_at = Carbon::now()->addDays(User::PORTFOLIO_TRIAL_LENGTH);

        $user->save();

        if (!$user->errors) {
            $user = $this->setRoles($user->id, $role_ids);
        }

        return $user;
    }

    public function slugExists($companyName): bool {
        $slug = $this->slugify($companyName);

        return User::where('slug', $slug)->exists();
    }

    /**
     * Update user
     *
     * @param User $user
     * @param array $attributes
     *
     * @return object $user
     */
    public function update($user_id, array $attributes)
    {
        if (array_key_exists('company_name', $attributes) && !$attributes['company_name']) {
            $attributes['company_name'] = null;
        }
        if (array_key_exists('slug', $attributes) && !$attributes['slug']) {
            $attributes['slug'] = null;
        }

        /** @var User $user */
        $user = $this->findById($user_id);

        $user->fill($attributes);

        if ($user->company_name) {
            $user->slug = $this->slugify($user->company_name);
        }

        $user->save();

        if ($user->isSeller() && array_key_exists('submission_limit', $attributes)) {
            $user->sellerProfile()->updateOrCreate(
                ["user_id"        => $user->id],
                ["submission_limit" => $attributes['submission_limit']]
            );
        }

        return $user;
    }

    /**
     * Delete user
     *
     * @param User $user
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete($userId)
    {
        $user = $this->findById($userId);

        /**
         * Cancel subscription
         */
        if ($user->subscribed()) {
            $user->subscription()->cancelNow();
        }

        $deleted = $user->delete();

        if (!$user->stripe_id) {
            $this->removeDeletedUserData($user);
        }

        return $deleted;
    }

    /**
     * Set user role
     *
     * @param User $user
     * @param array $role_ids
     *
     * @return  User
     */
    public function setRoles($user_id, array $role_ids)
    {
        $user = $this->findById($user_id);
        $user->roles()->detach();

        foreach ($role_ids as $id) {
            $role = Role::find($id);
            if ($role) {
                $user->roles()->save($role);
            }
        }

        return $user;
    }

    /**
     * Set user access services
     *
     * @param User $user
     * @param array $access_services
     *
     * @return  User
     */
    public function setAccessServices($user_id, array $services_ids)
    {
        $user = $this->findById($user_id);
        $user->accessServices()->detach();

        foreach ($services_ids as $id) {
            $access_service = AccessService::find($id);
            if ($access_service) {
                $user->accessServices()->save($access_service);
            }
        }

        return $user;
    }

    /**
     * Upgrade a user to be a seller.
     *
     * @param User $user The user to be upgraded.
     * @param array $data An array of data.
     *
     * @return User The updated user.
     */
    public function upgradeToSeller($user, array $data)
    {
        // Change the users role to be a seller.
        $role = Role::where('name', '=', 'Seller')->first();
        $this->setRoles($user->id, [
            $role->id
        ]);
        // Add the company_name to the Users model
        $user->company_name = $data['company_name'];
        $user->slug = $this->slugify($data['company_name']);
        $user->save();

        return $user;
    }

    /**
     * Downgrade user
     *
     * @param $user_id
     *
     * @param bool $deactivate_downloads
     *
     * @return false|User
     *
     * @throws \Exception
     */
    public function downgrade($userId)
    {
        $this->setRoles($userId, [Roles::CUSTOMER_ID]);
        $user = $this->findById($userId);

        $this->cancelSubscription($user);

        return $user;
    }

    /**
     * Set user password
     *
     * @param $user_id
     * @param $password
     * @return Model
     */
    public function setPassword($user_id, $password)
    {
        /** @var User $user */
        $user = $this->findById($user_id);
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    /**
     * Disable user
     *
     * @param User $user
     *
     * @return User
     */
    public function setDisabled($user_id)
    {
        $user = $this->findById($user_id);
        $user->disabled = 1;
        $user->save();

        return $user;
    }

    /**
     * Enable user
     *
     * @param User $user
     *
     * @return User
     */
    public function setEnabled($user_id)
    {
        $user = $this->findById($user_id);
        $user->disabled = 0;
        $user->save();

        return $user;
    }

    /**
     * Force log out for a user.
     *
     * @param User $user
     * @return void
     */
    public function setForceLogOut(User $user)
    {
        $user->force_log_out = 1;
        $user->save();
    }

    /**
     * Cancel a force log out for a user.
     *
     * @param User $user
     *
     * @return void
     */
    public function cancelForceLogOut(User $user)
    {
        $user->force_log_out = 0;
        $user->save();
    }

    /**
     * @param null $cycle
     * @param null $isActive
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed[]
     */
    public function getPlans($cycle = null, $isActive = null)
    {
        return Plan::where("cycle", "=", $cycle)
            ->when($cycle, function($query) use ($cycle) {
                return $query->where('cycle', $cycle);
            })
            ->when($isActive !== null, function($query) use ($isActive) {
                return$query->where('active', $isActive);
            })
            ->orderBy("order", "asc")
            ->get();
    }

    private function setNullToEmptyArray($var)
    {
        is_null($var) ? $var = [] : $var = $var;

        return $var;
    }

    public function setSorting($sorting)
    {
        $sorting = $this->setNullToEmptyArray($sorting);
        $this->sorting = array_merge($this->sorting, $sorting);

        return $this->sorting;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    public function setPagination($pagination)
    {
        $pagination = $this->setNullToEmptyArray($pagination);
        $this->pagination = array_merge($this->pagination, $pagination);

        return $this->pagination;
    }

    public function getPagination()
    {
        return $this->pagination;
    }

    public function getSellers()
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('role_id', '=', 3);
        });

        return $query->get();
    }

    public function getSellersByProvider($provider)
    {
        $query = User::withTrashed()->where('payout_method', '=', $provider);
        $users = $query->get();

        return $users;
    }

    // @TODO remove unused function
    public function getTotalEarningsForPeriod($seller, $start_date, $end_date, $cache = null)
    {
        $earnings = 0;

        // Add this months earnings.
        $stats = $seller->sellerStats($start_date, $end_date, $cache);

        $earnings += floatval(preg_replace("/,/", "", $stats['total_seller_payout']));

        // Add any previously witheld payouts.
        $earnings += $this->sellerPayout->getTotalRetainedPayouts($seller, $end_date->startOfMonth());

        return $earnings;
    }

    private function slugify($string)
    {
        return preg_replace("/ /", "-", trim(strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $string))));
    }

    public function getIntercomData(User $user, $includeStats = false)
    {
        return $this->getHelpdeskData($user, $includeStats);
    }

    private function getHelpdeskData(User $user, $includeStats = false)
    {
        $copyAttributes = [
            'company',
            'payout_method',
            'paypal_email',
            'payoneer_id',
            'slug',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'phone_number',
            'disabled',
            'upload_rules_accepted',
            'thank_you',
            'deleted_at',
            'stripe_created_at'
        ];
        $isSeller = $user->isSeller();
        $customAttributes = array_only($user->toArray(), $copyAttributes);
        $customAttributes['producer_name'] = $user->company_name ? $user->company_name : '';
        $customAttributes['has_stripe_id'] = !!$user->stripe_id;
        $customAttributes['is_seller'] = $isSeller;
        $customAttributes['email_confirmed'] = !!$user->confirmed;

        $lastSubscription = $user->getLastSubscription();
        if ($lastSubscription) {
            $customAttributes['payment_gateway'] = $lastSubscription->paymentGateway->name;
        } elseif ($customAttributes['has_stripe_id']) {
            $customAttributes['payment_gateway'] = PaymentGateways::STRIPE;
        }
        //@TODO: We will update below lines when we start using `subscriptions` table for Stripe too.
        // Currently Paypal's data has been stored in `subscriptions` table but Stripe's data
        // stored in `users` table.
        // If there is `lastSubscription` on `subscriptions` table, use its data. If not (that means user is using Stripe), use `users` table data.
        $customAttributes['payment_gateway_customer_id'] = $lastSubscription->payment_gateway_customer_id ?? $user->stripe_id;
        $customAttributes['payment_gateway_subscription_id'] = $lastSubscription->payment_gateway_subscription_id ?? $user->stripe_subscription;
        $customAttributes['payment_gateway_email'] = $lastSubscription->payment_gateway_email ?? null;

        // Plan
        $currentPlan = $user->plan;
        $planDescription = $currentPlan->name . ($currentPlan->isFree() ? '' : ' (' . ucwords($user->plan->cycle) . ')');
        $customAttributes['current_plan'] = $planDescription;

        // Portfolio and Projects data
        $customAttributes['portfolio'] = '';
        $customAttributes['portfolio_projects'] = 0;
        $customAttributes['review_projects'] = 0;
        if ($user->site) {
            if ($user->site->portfolio) {
                $customAttributes['portfolio'] = $user->site->getPortfolioUrl();
                $customAttributes['portfolio_projects'] = $user->activeProjects()->count();
            }
            $customAttributes['review_projects'] = $user->projects()->withReview()->count();
        }
        $customAttributes['portfolio_trial_end'] = $user->created_at->addDays(30)->timestamp;

        // Plugins
        $pluginToken = $user->tokens()->first();

        $customAttributes['plugins_use'] = false;
        $customAttributes['plugins_version'] = null;
        if ($pluginToken) {
            $customAttributes['plugins_use'] = true;
            $customAttributes['plugins_version'] = $pluginToken->plugins_version;
        }

        // Adobe Panel
        $customAttributes['adobe_panel_use'] = $this->accessTokenRepository->hasUsedAdobePanel($user);

        $customAttributes['requests'] = $user->requests()->count();

        if ($includeStats) {
            $customAttributes = array_merge($customAttributes, $this->getHelpdeskStats($user));
        }

        $userArr = [
            'user_id' => $user->id,
            'signed_up_at' => $user->created_at->getTimestamp(),
            'email' => $user->email,
            'name' => $user->fullName,
            'custom_attributes' => $customAttributes,
        ];

        return $userArr;
    }

    private function getHelpdeskStats(User $user)
    {
        $now = Carbon::now();
        $start = Carbon::createFromDate(2010, 01, 01);
        $lastMonth = Carbon::now()->subMonths(3);
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        // All Downloads
        $downloadsQuery = $user->downloads()->withTrashed();
        $downloads = $downloadsQuery->count();
        $downloadsLastMonth = $downloadsQuery->where('first_downloaded_at', '>', $lastMonth)->count();
        $downloadsLastThreeMonths = $downloadsQuery->where('first_downloaded_at', '>', $threeMonthsAgo)->count();
        // Payment Downloads
        $paymentDownloads = $user->getPremiumDownloadsCount($start, $now);
        $paymentDownloadsLastMonth = $user->getPremiumDownloadsCount($lastMonth, $now);
        $paymentDownloadsLastThreeMonths = $user->getPremiumDownloadsCount($threeMonthsAgo, $now);
        // Collections
        $collections = $user->collections()->count();
        $collectionsProducts = 0;
        $collectionsProductsAvg = 0;

        foreach ($user->collections as $collection) {
            $collectionsProducts += $collection->products()->count();
        }

        if ($collections) {
            $collectionsProductsAvg = round(($collectionsProducts / $collections) * 100) / 100;
        }

        // Defaults
        $downloads = $downloads ? $downloads : 0;
        $downloadsLastMonth = $downloadsLastMonth ? $downloadsLastMonth : 0;
        $downloadsLastThreeMonths = $downloadsLastThreeMonths ? $downloadsLastThreeMonths : 0;
        $uploadedProducts = 0;
        $uploadedProductsLastMonth = 0;
        $uploadedProductsLastThreeMonths = 0;
        $approvedSubmissions = 0;
        $rejectedSubmissions = 0;
        $activeProducts = 0;
        $sellerDownloads = 0;
        $sellerDownloadsLastMonth = 0;
        $sellerDownloadsLastThreeMonths = 0;

        if ($user->isSeller()) {
            $uploadedProductsQuery = $user->products()->withTrashed();
            $uploadedProducts = $uploadedProductsQuery->count();
            $uploadedProductsLastMonth = $uploadedProductsQuery->where('created_at', '>', $lastMonth)->count();
            $uploadedProductsLastThreeMonths = $uploadedProductsQuery->where('created_at', '>', $threeMonthsAgo)->count();
            $approvedSubmissions = $user->submissions()->withTrashed()->approved()->count();
            $rejectedSubmissions = $user->submissions()->withTrashed()->rejected()->count();
            $activeProducts = $user->products()->published()->count();

            $sellerDownloads = null;
            $sellerDownloadsLastMonth = null;
            $sellerDownloadsLastThreeMonths = null;
        }

        $stats = [
            'downloads_count' => $downloads,
            'downloads_count_last_month' => $downloadsLastMonth,
            'downloads_count_last_three_months' => $downloadsLastThreeMonths,
            'payment_downloads_count' => $paymentDownloads,
            'payment_downloads_count_last_month' => $paymentDownloadsLastMonth,
            'payment_downloads_count_last_three_months' => $paymentDownloadsLastThreeMonths,
            'uploaded_products_ever' => $uploadedProducts,
            'uploaded_products_last_month' => $uploadedProductsLastMonth,
            'uploaded_products_last_three_months' => $uploadedProductsLastThreeMonths,
            'approved_submissions_ever' => $approvedSubmissions,
            'rejected_submissions_ever' => $rejectedSubmissions,
            'current_active_products' => $activeProducts,
            'seller_downloads' => $sellerDownloads,
            'seller_downloads_last_month' => $sellerDownloadsLastMonth,
            'seller_downloads_last_three_months' => $sellerDownloadsLastThreeMonths,
            'collections_count' => $collections,
            'collections_products' => $collectionsProducts,
            'collections_products_avg' => $collectionsProductsAvg,
        ];

        $uploadsStats = $this->getUploadsStatsForHelpdesk($user);
        $stats = array_merge($stats, $uploadsStats);

        $downloadStats = $this->getDownloadsStatsForHelpdesk($user);
        $stats = array_merge($stats, $downloadStats);

        return $stats;
    }

    public function getDownloadsStatsForHelpdesk(User $user)
    {
        $categories = Category::all();
        $downloads = [];

        foreach ($categories as $category) {
            $shortname = str_replace(' ', '_', strtolower($category->short_name));
            $filters = [
                'category_id' => $category->id
            ];

            $downloads['downloads_' . $shortname] = $this->download->getDownloadedProductsCount($user->id, $filters);
        }

        return $downloads;
    }

    public function getUploadsStatsForHelpdesk(User $user)
    {
        $categories = Category::all();

        $uploads = [];

        if (!$user->isSeller()) {
            foreach ($categories as $category) {
                $shortname = str_replace(' ', '_', strtolower($category->short_name));

                $uploads['uploads_' . $shortname] = 0;
            }

            return $uploads;
        }

        $products = $user->products()
            //->published()
            ->where('products.free', '=', 0)
            ->select(DB::raw('products.category_id, COUNT(products.id) as count'))
            ->groupBy('products.category_id')->get();

        $products = $products->toArray();

        foreach ($categories as $category) {
            $categoryIds = array_column($products, 'category_id');

            $index = array_search($category->id, $categoryIds);

            $shortname = str_replace(' ', '_', strtolower($category->short_name));

            $count = 0;
            if ($index !== false) {
                $count = $products[$index]['count'];
            }

            $uploads['uploads_' . $shortname] = $count;
        }

        return $uploads;
    }

    public function getDiskUsage(User $user)
    {
        $bytes = 0;

        $projects = $user->projects;

        foreach ($projects as $project) {
            $bytes += $project->getDiskUsage();
        }

        $uploadingFiles = $this->userUpload->getUploadingRecords($user);

        if ($uploadingFiles) {
            foreach ($uploadingFiles as $uploadingFile) {
                if ($uploadingFile->type == 'project') {
                    $bytes += $uploadingFile->size;
                }
            }
        }

        return $bytes;
    }

    public function getDiskUsageInGB(User $user)
    {
        $bytes = $this->getDiskUsage($user);

        return Helpers::bytesToGB($bytes);
    }

    public function getDiskUsageInPercentage(User $user)
    {
        $percentage = 0;

        if ($user->plan->diskSpaceInKb()) {
            $percentage = 100 * ($this->getDiskUsage($user) / $user->plan->diskSpaceInKb());
        }

        return number_format($percentage, 2);
    }

    public function findRestorableUser($email)
    {
        $user = User::where('email', '=', $email)->onlyTrashed()->first();

        return $user;
    }

    public function restoreUser(User $user)
    {
        if ($user->trashed()) {
            return $user->restore();
        }
    }

    public function removeDeletedUserData($user)
    {
        if ($user->trashed()) {
            $excluded_fields = ['id', 'plan_id', 'email', 'firstname', 'lastname', 'deleted_at', 'password', 'created_at', 'updated_at'];

            $update = [];

            //Get all table fields
            $fields = $this->model->getConnection()->getSchemaBuilder()->getColumnListing($this->model->getTable());

            foreach ($fields as $field) {

                if (!(in_array($field, $excluded_fields))) {

                    $column_data = $this->model->getConnection()->getDoctrineColumn($this->model->getTable(), $field);
                    $type = $column_data->getType()->getName();

                    if ($type == 'boolean') {
                        $update[$field] = false;
                    } else {
                        if ($column_data->getNotnull()) {
                            $update[$field] = '';
                        } else {
                            $update[$field] = null;
                        }
                    }
                }
            }

            $update['plan_id'] = 5;
            $update['firstname'] = 'removed';
            $update['lastname'] = 'removed';
            $update['email'] = 'removed-user-' . $user->id . '@' . config('app.host');

            $new_password = $user->id;
            $update['password'] = $new_password;

            return User::onlyTrashed()->where('id', $user->id)->update($update);
        }

        return false;
    }

    public function getContentReadyForRemovalUsers($status, $date)
    {
        $users = User::onFreePlan()
            ->has('projects')
            ->where('content_removal_warning_status', '<=', $status)
            ->whereNotNull('portfolio_trial_ends_at')
            ->where('portfolio_trial_ends_at', '<', $date);

        return $users;
    }

    /**
     * @param User $user
     * @param $firstname
     * @param $lastname
     * @return User
     */
    public function updateBillingName(User $user, $firstname, $lastname)
    {
        $user->billing_firstname = $firstname;
        $user->billing_lastname = $lastname;
        $user->save();

        return $user;
    }

    public function acceptTos(User $user)
    {
        $user->tos_accepted = true;
        $user->save();
    }

    /**
     * @deprecated use `UserSubscriptionRepository@cancelSubscription` method.
     *
     * This method will cancel user's subscription at the moment.
     * Also changes user's subscription to FREE plan.
     *
     * @param User $user
     *
     * @throws \MotionArray\Services\Subscription\Exceptions\Paypal\PaypalCancelationException
     */
    public function cancelSubscription(User $user)
    {
        /**
         * If user has subscription on Stripe, cancel it on
         * Sometimes there could be data integrity problems, i.e. manually modified database.
         * And this `if` control prevents us to have exception
         */
        if ($user->subscribed()) {
            $user->subscription()->cancelNow();
        }

        $user->plan_id = Plans::FREE_ID;
        $user->stripe_plan = null;
        $user->last_four = null;
        $user->subscription_ends_at = null;
        $user->access_starts_at = null;
        $user->access_expires_at = null;
        $user->stripe_active = false;

        $user->save();
    }

    /**
     * This method returns calculation of how many days users used their subscriptions
     * Simply (Today - Start Date).
     *
     * @param User $user
     *
     * @return int
     */
    public function getSubscriptionUsageAsDays(User $user)
    {
        /** @var StripeGateway $subscription */
        $subscription = $user->subscription();

        return $subscription->getSubscriptionStartDate()->diff(new Carbon())->days;
    }

    /**
     * Calculate refund amount when we want to change user's account
     * from yearly to monthly.
     *
     *  annualPrice - (monthlyPrice * (user's current subscription month))
     *
     * @param User $user
     *
     * @return float
     */
    public function getSubscriptionRefundAmountForYearlyToMonthlyTransition(User $user)
    {
        if (!$user->subscribed()) {
            return 0;
        }

        if ($user->plan->cycle !== Plans::CYCLE_YEARLY) {
            return 0;
        }

        $monthlyPlan = Plan::find(Plans::MONTHLY_UNLIMITED_2018_ID);

        /** @var StripeGateway $subscription */
        $subscription = $user->subscription();

        /**
         * Reason of +1:
         *
         * >>> (new \Carbon\Carbon())->diffInMonths(new \Carbon\Carbon('+32 days'));
         * => 1
         * >>> (new \Carbon\Carbon())->diffInMonths(new \Carbon\Carbon('+2 days'));
         * => 0
         *
         * We won't refund user's current month and will add trial till end of the monthly period
         */
        $months = $subscription->getSubscriptionStartDate()->diffInMonths(new Carbon()) + 1;

        $userPaid = $user->plan->price;
        $costOfMonthsUser = $months * $monthlyPlan->price;
        $willBeRefunded = $userPaid - $costOfMonthsUser;

        // convert to dollars
        return $willBeRefunded / 100.0;
    }

    public function findByIdWithSubmissionLimit($id)
    {
        return User::with('sellerProfile')->find($id);
    }
}
