<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use MotionArray\Facades\Flash;
use MotionArray\Models\Category;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\SellerPayoutRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Repositories\DownloadRepository;
use Carbon\Carbon;

class AccountsController extends BaseController
{
    protected $paginationRange = 10;

    protected $product;

    protected $download;

    protected $userRepo;

    protected $sellerPayout;

    public function __construct(
        ProductRepository $productRepository,
        DownloadRepository $downloadRepository,
        UserRepository $userRepository,
        SellerPayoutRepository $sellerPayoutRepository
    )
    {
        $this->product = $productRepository;
        $this->download = $downloadRepository;
        $this->userRepo = $userRepository;
        $this->sellerPayout = $sellerPayoutRepository;
    }

    public function index()
    {
        return view("site.account.details");
    }

    public function billing()
    {
        $lastSubscription = \Auth::user()->getLastSubscription();

        return view("site.account.billing")
            ->with('lastSubscription', $lastSubscription)
            ->with('JS_DATA', [
                'payment_gateways' => [
                    'PAYPAL_ID' => PaymentGateways::PAYPAL_ID,
                    'STRIPE_ID' => PaymentGateways::STRIPE_ID,
                ],
            ])
            ;
    }

    public function subscription()
    {
        $user = auth()->user();
        /**
         * Ensure user is subscribed to a plan
         */
        if ($user->subscribed()) {
            /**
             * Show resume message if user is on a grace period after
             * cancelling their account
             */
            if ($user->onGracePeriod()) {
                Flash::info("Your subscription has been cancelled and will expire on <strong>" . $user->present()->gracePeriodEndDate . "</strong>.<br/> To resume your subscription without interruption " . link_to_action("Site\UsersController@resumeSubscription", "click here") . ".<br/> Alternatively you can upgrade or downgrade your subscription below to automatically resume.", "locked");
            } /**
             * Show scheduled downgrade message if user has previously set this
             * action in motion
             */
            else if ($user->onDowngradeGracePeriod()) {
                $billingAction = $user->billingActions()->first();

                if ($billingAction->change_to_billing_id == 'yearly_unlimited_2018' || $billingAction->change_to_billing_id == 'monthly_unlimited_2018') {
                    $info = 'Your membership will be moved to a ';
                } else {
                    $info = 'Your subscription will be downgraded to a ';
                }

                $info .= "<strong>" . $user->present()->downgradingToPlanName . "</strong> on <strong>" . $user->present()->downgradeSubscriptionDate . "</strong>.";
                if (!$billingAction->forced) {
                    $info .= ' Or you can <a href="subscription/downgrade-now">Downgrade now</a>.';
                    $info .= "<br/>To cancel your downgrade request " . link_to_action("Site\UsersController@cancelDowngrade", "click here") . ".";
                }

                Flash::info($info, "locked");
            }

            return view("site.account.subscription");
        }

        /**
         * User isn't subscribed to redirect to upgrade
         * page
         */
        return redirect("/account/upgrade");
    }

    /**
     * View Upgrade Account
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function upgrade()
    {
        // @todo remove this if block. It was used to make this view publicly accessible for Google Analytic experiment.
        if (!Auth::check()) {
            return view("site.account.upgrade");
        }

        if (auth()->user()->subscribed()) {
            return redirect()->to("account/subscription");
        }

        return view("site.account.upgrade");
    }

    public function downgrade()
    {
        return view("site.account.downgrade");
    }

    public function invoices()
    {
        return view("site.account.invoices");
    }

    public function downloads()
    {
        if (Auth::user()->downloads()->where("active", "=", 1)->count()) {
            $page_no = Request::get("page") ? Request::get("page") : 1;
            $products_per_page = Request::get("show") ? Request::get("show") : 24;

            $category_slug = Request::get('category');
            $category_id = null;

            if ($category_slug && $category_slug != 'all' && $category_slug != 'free') {
                $category = Category::where("slug", "=", $category_slug)->first();
                $category_id = $category->id;
            }

            $filters = [
                'category_id' => $category_id,
                'page' => $page_no,
                'per_page' => $products_per_page,
                'free' => ($category_slug == 'free'),
                'with_trashed_products' => false
            ];

            $downloads = $this->download->getUserDownloadedProducts(Auth::user()->id, $filters);
            $downloadsCount = $this->download->getDownloadedProductsCount(Auth::user()->id, $filters);

            $pagination = [
                'products_per_page' => $products_per_page,
                'current_page_no' => $page_no,
                'product_count' => $downloadsCount,
                'pagination_range' => $this->paginationRange,
                'remove_browse' => true
            ];

            if ($category_slug) {
                $pagination['filters'] = '&category=' . $category_slug;
            }

            return view("site.account.downloads", compact('downloads', 'pagination'));
        }

        return Redirect::to("account");
    }

    public function sellerDetails()
    {
        if (Auth::user()->isSeller()) {
            return view("site.account.seller-details");
        }

        return App::abort('404');
    }

    public function sellerStats()
    {
        if (!Auth::user()->isSeller()) {
            return Redirect::to("/account");
        }

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        return view("site.account.seller-stats", compact('month', 'year'));
    }

    public function postSellerStats()
    {
        return Redirect::to('account/seller-stats');
    }
}
