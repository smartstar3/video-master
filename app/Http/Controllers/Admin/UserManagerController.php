<?php

namespace MotionArray\Http\Controllers\Admin;

use Carbon\Carbon;
use MotionArray\Models\Country;
use MotionArray\Models\PayoutTotal;
use MotionArray\Models\ProductStatus;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Repositories\AdminUserRepository;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Repositories\DownloadRepository;
use MotionArray\Repositories\SellerPayoutRepository;
use MotionArray\Repositories\SubscriptionRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Models\AccessServiceCategory;
use App;
use DB;
use Auth;
use View;
use Request;
use Response;
use Redirect;
use Session;
use mnshankar\CSV\CSV;

class UserManagerController extends BaseController
{
    /**
     * @var UserRepository
     */
    private $userRepo;
    private $download;
    private $sellerPayout;

    /**
     * @var AdminUserRepository
     */
    protected $adminUserRepo;

    function __construct(
        UserRepository $userRepository,
        DownloadRepository $download,
        SellerPayoutRepository $sellerPayout,
        SubscriptionRepository $subscriptionRepository,
        AdminUserRepository $adminUserRepo
    ) {
        $this->userRepo = $userRepository;
        $this->download = $download;
        $this->sellerPayout = $sellerPayout;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->adminUserRepo = $adminUserRepo;
    }

    public function index()
    {
        $users = null;

        if (!Request::has('start')) {
            $users = $this->userRepo->getUsers($this->setupPagination(), $this->setupSorting());
            $pagination = $this->userRepo->getPagination();
        }

        $plans = $this->userRepo->getPlans();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        return View::make("admin.user-manager.index", compact("users", "plans", "month", "year"));
    }

    public function searchResults()
    {
        $q = Request::get("q");
        $users = $this->userRepo->search($q, $this->setupPagination());
        $users->load(['roles', 'plan']);

        $pagination = $this->userRepo->getPagination();

        return View::make("admin.user-manager.search-results", compact("users", "pagination"));
    }

    public function create()
    {
        $plans = $this->userRepo->getPlans($cycle = null, $isActive = true);

        return View::make("admin.user-manager.create", compact("plans"));
    }

    public function store()
    {
        $role_id = Request::get("role_id");
        $plan_id = Plans::FREE_ID;
        $attributes = [
            "plan_id" => $plan_id,
            "firstname" => Request::get("firstname"),
            "lastname" => Request::get("lastname"),
            "payout_method" => Request::get("payout_method"),
            "paypal_email" => Request::get("paypal_email"),
            "payoneer_id" => Request::get("payoneer_id"),
            "email" => Request::get("email"),
            "password" => Request::get("password"),
            "password_confirmation" => Request::get("password_confirmation")
        ];
        // Company name can be null (seller name in UI)
        if (Request::get("company_name"))
            $attributes["company_name"] = Request::get("company_name");
        $user = $this->adminUserRepo->make($attributes, $role_id);
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }

        return $this->response($user);
    }

    public function getDownloadHistory($user_id)
    {
        $filters = [
            'per_page' => 500,
            'active_only' => false
        ];

        $downloadHistory = $this->download->getUserDownloadedProducts($user_id, $filters);

        $user = $this->userRepo->findById($user_id);

        $lastDowngrade = $user->downgrades()->orderBy('created_at', 'DESC')->first();

        return View::make("admin.user-manager._partials.download-history", compact("downloadHistory", "user_id", "lastDowngrade"));
    }

    public function edit($user_id)
    {
        $user = $this->userRepo->findByIdWithSubmissionLimit($user_id);

        return View::make("admin.user-manager._partials.edit", compact("user"));
    }

    public function updateDetails($user_id)
    {
        $user = $this->adminUserRepo->update($user_id, [
            "firstname" => Request::get("firstname"),
            "lastname" => Request::get("lastname"),
            "company_name" => Request::get("company_name"),
            "payout_method" => Request::get("payout_method"),
            "paypal_email" => Request::get("paypal_email"),
            "payoneer_id" => Request::get("payoneer_id"),
            "slug" => Request::get("slug"),
            "email" => Request::get("email"),
            "submission_limit" => Request::get("submission_limit"),
        ]);
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }

        return $this->response($user);
    }

    public function resetPassword($user_id)
    {
        $user = $this->userRepo->findById($user_id);

        return View::make("admin.user-manager._partials.reset-password", compact("user"));
    }

    public function updatePassword($user_id)
    {
        $user = $this->adminUserRepo->setPassword($user_id, Request::get("password"), Request::get("password_confirmation"));
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }

        return $this->response($user);
    }

    public function changeRole($user_id)
    {
        $user = $this->userRepo->findById($user_id);
        $access_service_categories = AccessServiceCategory::all();

        return View::make("admin.user-manager._partials.change-role", compact("user", "access_service_categories"));
    }

    public function updateRole($userId)
    {
        /* update access services when is upldated a user role */
        $param = Request::only('access_service_1', 'access_service_2', 'access_service_3', 'access_service_4', 'access_service_5', 'access_service_6', 'access_service_7', 'access_service_8', 'access_service_9', 'access_service_10', 'access_service_11', 'access_service_12');
        $services_ids = [];
        foreach ($param as $key => $value) {
            $services_id = substr($key, 15);
            if ($value) {
                array_push($services_ids, $services_id);
            }
        }

        $this->userRepo->setAccessServices($userId, $services_ids);
        $roles = Request::get("role_id");

        $user = $this->adminUserRepo->setRoles($userId, $roles);
        $user->role = $user->roles()->first()->name;
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }

        return $this->response($user);
    }

    public function freeloader($user_id)
    {
        $user = $this->userRepo->findById($user_id);
        $plans = $this->userRepo->getPlans("monthly", $active = true);

        return View::make("admin.user-manager._partials.freeloader", compact("user", "plans"));
    }

    public function updateFreeloader($user_id)
    {
        $access_starts_at = Carbon::now();
        $access_expires_at = Request::get("access_expires_at") ? Carbon::createFromFormat('m/d/Y', Request::get("access_expires_at")) : null;
        $user = $this->adminUserRepo->updateFreeloader($user_id, [
            "plan_id" => Request::get("plan_id"),
            "access_starts_at" => $access_starts_at,
            "access_expires_at" => $access_expires_at
        ]);
        $user->role = $user->roles()->first()->name;
        $user->plan = $user->present()->plan;
        if ($user->errors) {
            return Response::make($user->errors, 403);
        }

        return $this->response($user);
    }

    public function confirmRevokeFreeloader($user_id)
    {
        $user = $this->userRepo->findById($user_id);
        if ($user) {
            return View::make("admin.user-manager._partials.revoke-freeloader", compact("user"));
        }

        return Response::make([], 403);
    }

    public function toggleStatus($user_id)
    {
        $user = $this->userRepo->findById($user_id);

        if ($user->disabled) {
            return $this->setEnabled($user_id);
        } else {
            return $this->setDisabled($user_id);
        }
    }

    public function toggleForceLogOut($user_id)
    {
        $user = $this->userRepo->findById($user_id);
        if ($user->forceLogOut()) {
            return $this->cancelForceLogOut($user_id);
        }

        return $this->setForceLogOut($user_id);
    }

    public function confirmDelete($user_id)
    {
        $user = $this->userRepo->findById($user_id);
        if ($user) {
            return View::make("admin.user-manager._partials.confirm-delete", compact("user"));
        }

        return Response::make([], 403);
    }

    public function delete($user_id)
    {
        $user = $this->adminUserRepo->delete($user_id);
        if ($user) {
            return Response::make([], 200);
        }

        return Response::make($user, 403);
    }

    private function setDisabled($user_id)
    {
        $user = $this->adminUserRepo->setDisabled($user_id);

        return $this->response($user);
    }

    private function setEnabled($user_id)
    {
        $user = $this->adminUserRepo->setEnabled($user_id);

        return $this->response($user);
    }

    private function setForceLogOut($user_id)
    {
        $user = $this->adminUserRepo->setForceLogOut($user_id);

        return $this->response($user);
    }

    private function cancelForceLogOut($user_id)
    {
        $user = $this->adminUserRepo->cancelForceLogOut($user_id);

        return $this->response($user);
    }

    private function setupSorting()
    {
        $sorting = null;
        if (!is_null(Request::get("orderby"))) {
            $sorting["order_by"] = Request::get("orderby");
        }
        if (!is_null(Request::get("order"))) {
            $sorting["order"] = Request::get("order");
        }

        return $sorting;
    }

    private function setupPagination()
    {
        $pagination = ["item_count" => $this->userRepo->getUsersCount()];
        if (!is_null(Request::get("q"))) {
            $pagination = ["item_count" => $this->userRepo->getSearchCount(Request::get("q"))];
        }
        if (!is_null(Request::get("page")) && is_numeric(Request::get("page"))) {
            $pagination["page_no"] = Request::get("page");
        }
        if (!is_null(Request::get("show")) && is_numeric(Request::get("page"))) {
            $pagination["items_per_page"] = Request::get("show");
        }
        $pagination["filters"] = "";
        if (!is_null(Request::get("orderby"))) {
            $pagination["filters"] .= "&orderby=" . Request::get("orderby");
        }
        if (!is_null(Request::get("order"))) {
            $pagination["filters"] .= "&order=" . Request::get("order");
        }
        if (!is_null(Request::get("q"))) {
            $pagination["filters"] .= "&q=" . Request::get("q");
        }
        if (!is_null(Request::get("plan")) && Request::get("plan") !== "all") {
            $pagination["filters"] .= "&plan=" . Request::get("plan");
        }
        if (!is_null(Request::get("customers")) && Request::get("customers") !== "all") {
            $pagination["filters"] .= "&customers=" . Request::get("customers");
        }

        return $pagination;
    }

    public function getCSV()
    {
        // Export user data to tmpfile.
        $users = DB::select("SELECT 'Firstname', 'Lastname', 'Email', 'Role', 'Stripe ID', 'Plan', 'Plan Frequency', 'Joined at', 'Download count'
                        UNION ALL
                        SELECT users.firstname, users.lastname, users.email, roles.name, users.stripe_id, plans.name, plans.cycle, users.created_at, count(downloads.id) FROM users
                        LEFT OUTER JOIN user_role ON user_role.user_id = users.id
                        LEFT OUTER JOIN roles ON roles.id = user_role.role_id
                        LEFT OUTER JOIN plans ON plans.id = users.plan_id
                        LEFT OUTER JOIN downloads ON downloads.user_id = users.id
                        WHERE users.deleted_at IS NULL
                        GROUP BY users.id;");
        $csv = new CSV();

        return $csv->setHeaderRowExists(false)->fromArray($users)->render('users.csv');
    }

    /**
     * Downloads the list of users with no payment details and approved items
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getNoPaymentCSV()
    {
        // Generate tmp file name.
        // File will be deleted on system restart.
        $status = ProductStatus::where('status', '=', 'Published')->first();
        // Export user data to tmpfile.
        $sellers = DB::select("SELECT 'Firstname', 'Lastname', 'Email', 'Role', 'Payout Method', 'Paypal', 'Payoneer', 'Live Products', 'Joined at'
                        UNION ALL
                        SELECT users.firstname, users.lastname, users.email, roles.name, ifnull(users.payout_method, ''), ifnull(users.paypal_email, ''), ifnull(users.payoneer_id, ''), count(products.id), users.created_at FROM users
                        LEFT OUTER JOIN user_role ON user_role.user_id = users.id
                        LEFT OUTER JOIN roles ON roles.id = user_role.role_id
                        LEFT OUTER JOIN products ON products.seller_id = users.id AND products.product_status_id = " . $status->id . " AND free != 1
                        WHERE users.deleted_at IS NULL
                        AND roles.name = 'Seller'
                        AND products.product_status_id = " . $status->id . "
                        GROUP BY users.id;");
        $csv = new CSV();

        return $csv->setHeaderRowExists(false)->fromArray($sellers)->render('sellers.csv');
    }

    /**
     * Log in as user.
     *
     * This will log the current admin user out.
     */
    public function logInAs($user_id)
    {
        // Find the user.
        $user = $this->userRepo->findById($user_id);
        if ($user) {
            //Get current User
            $currentUser = Auth::user();

            // Log in as this user.
            Auth::login($user);
            $this->adminUserRepo->loginAs($user_id);

            // Set Session Id of current user
            $currentUser->session_id = Session::getId();
            $currentUser->save();

            // Redirect to account page.
            return Redirect::to('/account');
        }

        // Redirect back to where we were if the user is not found.
        return Redirect::action('Admin\UserManagerController@index');
    }

    public $valid_providers = ['paypal', 'payoneer'];
    public $valid_regions = ['Global', 'US'];

    public function getPayoutCSV()
    {
        $provider = Request::get("provider");

        $includeHeader = (bool)Request::get("includeHeader");
        $includeIds = (bool)Request::get("includeId");

        $region = Request::get("region") ?? 'global';

        $start_date = !is_null(Request::get("month")) && !is_null(Request::get("year")) ? Carbon::createFromFormat('d m Y H:i:s', "1 " . Request::get("month") . " " . Request::get("year") . " 00:00:00") : null;

        $end_date = !is_null(Request::get("month")) && !is_null(Request::get("year")) ? Carbon::createFromFormat('d m Y H:i:s', "1 " . Request::get("month") . " " . Request::get("year") . " 00:00:00")->endOfMonth() : null;

        if (in_array($provider, $this->valid_providers)
            and in_array($region, $this->valid_regions)) {
            return $this->generatePayoutCSV($start_date, $end_date, $provider, $region, $includeHeader, $includeIds);
        }

        // Error with 400 code.
        App::abort(400, 'Bad request.');
    }

    public function generatePayoutCSV(Carbon $start_date,
                                      Carbon $end_date,
                                      string $provider,
                                      string $region,
                                      bool $includeHeader = false,
                                      bool $includeIds = false)
    {
        assert(in_array($provider, $this->valid_providers));
        assert(in_array($region, $this->valid_regions));

        $retainedOnly = Request::has("retained-only");

        $csv = new CSV();
        $rows = [];
        $count = 0001;

        $site_payout = PayoutTotal::where([
            'month' => $start_date->month,
            'year' => $start_date->year
        ])->first();

        $seller_payouts = $this->sellerPayout->getPayoutsByProvider($start_date, $end_date, $provider);
        if ($region != 'Global') {
            $country_payouts = $this->sellerPayout->getPayoutsByProviderAndCountry($start_date, $end_date, $provider, Country::byCode($region));
        }
        foreach ($seller_payouts as $payout) {

            $seller = $payout->seller()->withTrashed()->first();

            // Find correponding seller's country-specific payout to put on CSV
            if (isset($country_payouts)) { //NOTE: this assumes we only have 1 record here, i.e. period is 1 month
                $country_payout = $country_payouts->where('user_id', '=', $seller->id)->first();
                if ($country_payout)
                    $country_amount = $country_payout->amount;
                else
                    $country_amount = 0;
            }

            $retained = $this->sellerPayout->getTotalRetainedPayouts($seller, $start_date);

            $amount = $retained + $payout->amount;

            // Skip this seller if their payout is less than 50.
            if ($amount < 50.0) {
                continue;
            }

            // Add payout to CSV.
            if ($provider === "paypal") {
                /**
                 * Paypal
                 */
                $row = [
                    'Paypal Email' => $seller->paypal_email,
                    'Earning' => number_format($amount, 2),
                    'Currency' => 'USD',
                ];
                if (isset($country_payouts)) {
                    $row['Seller Downloads Weight'] = round($amount / max($site_payout->amount, 1) * $site_payout->weight); // Total seller downloads
                    $row[$region . '-based Earning'] = number_format($country_amount, 2);
                    $row[$region . '-based Downloads Weight'] = round($country_amount / max($site_payout->amount, 1) * $site_payout->weight);
                    $row['Month Earning'] = $site_payout->amount;
                    $row['Month Downloads Weight'] = $site_payout->weight;
                }
                $row['ID'] = 'ID' . $count;
                $row['Message'] = 'Hi ' . $seller->firstname . " " . $seller->lastname . '! Thank you for your hard work, and for being a Motion Array producer.';

            } else if ($provider === "payoneer") {
                $paymentId = strtoupper($seller->id . '.' . date('Y.M.d'));
                /**
                 * Payoneer
                 */
                $row = [
                    'Payoneer ID' => $seller->payoneer_id,
                    'Earning' => number_format($amount, 2),
                    'Currency' => 'USD'
                ];
                if (isset($country_payouts)) {
                    $row['Seller Downloads Weight'] = round($amount / max($site_payout->amount, 1) * $site_payout->weight); // Total seller downloads
                    $row[$region . '-based Earning'] = number_format($country_amount, 2);
                    $row[$region . '-based Downloads Weight'] = round($country_amount / max($site_payout->amount, 1) * $site_payout->weight);
                    $row['Month Earning'] = $site_payout->amount;
                    $row['Month Downloads Weight'] = $site_payout->weight;
                }
                $row['Payment ID'] = $paymentId;
                $row['Producer ID'] = 'Producer ID ' . $seller->payoneer_id;
                $row['Date'] = Carbon::now()->format('m/d/Y');
            }
            if ($includeIds) { // Add ID to the beginning of the row
                $row = array_merge(['Seller ID' => $seller->id], $row);
            }

            $rows[] = $row;
            $count++;
        }

        if ($includeHeader and count($rows)) { // Add headers to the beginning of rows
            array_unshift($rows, array_keys($rows[0]));
        }
        return $csv->setHeaderRowExists(false)->fromArray($rows)->render($provider . '_payouts.csv');
    }

    private function response($user)
    {
        $user = $this->userRepo->findById($user->id);

        $response = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'company_name' => $user->company_name,
            'payout_method' => $user->payout_method,
            'paypal_email' => $user->paypal_email,
            'email' => $user->email,
            'role' => $user->role,
            'plan' => $user->plan,
            'stripe_id' => $user->stripe_id,
        ];

        return response()->json($response, 200);
    }
}
