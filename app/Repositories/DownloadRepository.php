<?php namespace MotionArray\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use MotionArray\Models\Country;
use MotionArray\Models\Download;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use MotionArray\Mailers\FeedbackMailer;
use Illuminate\Support\Facades\Cache;
use MotionArray\Repositories\Download\Illuminate;

class DownloadRepository
{
    /**
     * @var FeedbackMailer
     */
    protected $feedbackMailer;

    public function __construct(FeedbackMailer $feedbackMailer)
    {
        $this->feedbackMailer = $feedbackMailer;
    }

    public function recordDownload(User $user, Product $product)
    {
        $downloadRecord = $user->hasDownloadedProductBefore($product->id);

        if ($downloadRecord) {
            return $this->incrementDownloadCount($downloadRecord);
        }

        return $this->addToDownloadHistory($product, $user);
    }

    /**
     * Download cooldown
     *
     * @param $productId
     *
     * @return bool
     */
    public function isDownloading(User $user, Product $product): bool
    {
        $downloadInterval = 5;
        $checkStartTime = Carbon::now()->subSeconds($downloadInterval);

        $recentDownloadCount = Download::where('created_at', '>=', $checkStartTime)
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->count();

        return (bool)$recentDownloadCount;
    }

    /**
     * Add product to users download history
     *
     * @param Product $product
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Model|Download
     */
    public function addToDownloadHistory(Product $product, User $user)
    {
        $download = Download::create([
            'user_id' => $user->id,
            'plan_id' => $user->plan_id,
            'product_id' => $product->id
        ]);

        return $download;
    }

    public function getUserDownloadedProducts(int $userId, array $filters)
    {
        $defaults = [
            'page' => 1,
            'per_page' => 9,
        ];

        $filters = array_merge($defaults, $filters);

        $perPage = $filters['per_page'];
        $page = $filters['page'];

        $orderBy = 'downloads.first_downloaded_at';
        $order = 'desc';

        return $this->userDownloadedProductsQuery($userId, $filters)
            ->orderBy($orderBy, $order)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
    }

    public function getDownloadedProductsCount(int $userId, array $filters)
    {
        return $this->userDownloadedProductsQuery($userId, $filters)->count();
    }

    /**
     * @param $start_date
     * @param $end_date
     * @param $cache
     * @return mixed
     */
    public function getDownloadsByCategoryAndSeller($start_date, $end_date, $cache)
    {
        if ($cache) {
            $query = Download::remember($cache)->cacheTags('seller_downloads');
        } else {
            $query = Download::query();
        }

        $sellerDownloadsByCategory = $query
            ->withTrashed()
            ->select(DB::raw('products.category_id, products.seller_id, COUNT(downloads.product_id) as count'))
            ->leftJoin('products', 'products.id', '=', 'downloads.product_id')
            ->where(function ($query) {
                Download::premiumScope($query);
            })
            ->whereBetween('downloads.first_downloaded_at', [$start_date, $end_date])
            ->groupBy('products.category_id')
            ->groupBy('products.seller_id')
            ->get();

        return $sellerDownloadsByCategory;
    }

    protected function salesQuery($start_date, $end_date, $cache = false)
    {
        if ($cache) {
            $query = Download::remember($cache)->cacheTags('seller_stats');
        } else {
            $query = Download::query();
        }

        return $query->withTrashed()->select(DB::raw('products.category_id, COUNT(downloads.product_id) as count'))
            ->leftJoin('products', 'products.id', '=', 'downloads.product_id')
            ->where(function ($query) {
                Download::premiumScope($query);
            })
            ->whereBetween('downloads.first_downloaded_at', [$start_date, $end_date]);
    }

    /**
     * Returns global download count
     *
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return int
     */
    public function getGlobalSalesCount($start_date, $end_date)
    {
        return $this->salesQuery($start_date, $end_date)->count();
    }

    /**
     * Returns download count by a certain country
     *
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param Country $downloadCountry
     * @return int
     */
    public function getCountrySalesCount($start_date, $end_date, Country $downloadCountry)
    {
        return $this->salesQuery($start_date, $end_date)->whereIn('user_id', function ($query) use ($downloadCountry) {
            $query->select('id')
                ->from('users')
                ->where('country_id', '=', $downloadCountry->id);
        })->count();
    }

    /**
     * Get downloads for a certain seller
     *
     * @param User $seller
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return int
     */
    public function getSalesCountBySeller(User $seller, $start_date, $end_date)
    {
        return $this->salesQuery($start_date, $end_date)
            ->where('products.seller_id', '=', $seller->id)
            ->count();
    }

    /**
     * Get downloads for a certain seller
     *
     * @param User $seller
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param Country $downloadCountry
     * @return int
     */
    public function getSalesCountBySellerAndCountry(User $seller, $start_date, $end_date, Country $downloadCountry)
    {
        return $this->salesQuery($start_date, $end_date)
            ->where('products.seller_id', '=', $seller->id)
            ->whereIn('user_id', function ($query) use ($downloadCountry) {
                $query->select('id')
                    ->from('users')
                    ->where('country_id', '=', $downloadCountry->id);
            })->count();
    }

    public function globalDownloadsByCategory($start_date, $end_date, $cache)
    {
        return $this->salesQuery($start_date, $end_date, $cache)
            ->groupBy('products.category_id')
            ->get();
    }

    /**
     * Returns users premium downloads for given User
     *
     * @param User|null $user
     *
     * @return mixed
     */
    public function getPremiumDownloads(User $user)
    {
        $downloads = $user->downloads()->withTrashed()->leftJoin('products', function ($join) {
            $join->on('products.id', '=', 'downloads.product_id');
        })->select('downloads.*')
            ->where('products.free', '=', 0)
            ->where('downloads.active', '=', 1)
            ->get();

        return $downloads;
    }

    /**
     * Deactivates Premium downloads for given User
     *
     * @param User $user
     */
    public function deactivatePremiumDownloads(User $user)
    {
        $premiumDownloads = $this->getPremiumDownloads($user);

        $ids = $premiumDownloads->pluck('id');

        if ($ids->count()) {
            Download::whereIn('id', $ids)->update(['active' => 0]);
        }
    }

    private function userDownloadedProductsQuery(int $userId, array $filters)
    {
        $defaults = [
            'category_id' => null,
            'free' => false,
            'active_only' => true,
            'with_trashed_products' => true
        ];

        $filters = array_merge($defaults, $filters);

        $categoryId = $filters['category_id'];
        $free = $filters['free'];
        $activeOnly = $filters['active_only'];
        $withTrashedProducts = $filters['with_trashed_products'];

        $query = Download::withTrashed()
            ->where('user_id', '=', $userId);

        $query->leftJoin('products', function ($join) {
            $join->on('products.id', '=', 'downloads.product_id');
        });

        $query->whereNotNull('products.package_file_path')
            ->where('products.package_file_path', '!=', '');

        if (!$withTrashedProducts) {
            $query->whereNull('products.deleted_at');
        }

        if ($categoryId) {
            $query->where("products.category_id", '=', $categoryId);
        }

        if ($free) {
            $query->where('products.free', '=', 1);
        }

        if ($activeOnly) {
            $query->where('active', '=', 1);
        }

        return $query;
    }

    public function userDownloadsCountToday($user, $includeFree = true)
    {
        $yesterday = Carbon::now()->subDay();

        $query = Download::where(['user_id' => $user->id])
            ->where('first_downloaded_at', '>', $yesterday);

        if (!$includeFree) {
            $query->whereHas('product', function ($q) {
                $q->where(['free' => 0]);
            });
        }

        return $query->count();
    }

    public function checkOverDownloadRateLimit(User $user)
    {
        $overDownloadLimit = $this->overDownloadRateLimit($user);
        if ($overDownloadLimit) {
            $user->disabled = 1;
            $user->save();
            $this->feedbackMailer->downloadsLimited($user);
        }

        return $overDownloadLimit;
    }

    public function overDownloadRateLimit(User $user)
    {
        $current = Carbon::now();

        $rate_limit = config('download.rate_limit');
        if (!is_array($rate_limit))
            return false;

        foreach ($rate_limit as $minutes => $count) {
            $start_time = $current->copy()->subMinutes($minutes);
            $downloads_in_short_time = Download::where('user_id', $user->id)
                ->where('free', '=', 0)
                ->whereBetween('created_at', [$start_time, $current])
                ->count();
            if ($downloads_in_short_time > $count) {
                return true;
            }
        }

        return false;
    }

    /**
     * Relatively slow query, 2 to 60 seconds
     * Called indirectly, through $this->getWeightForPeriodByCategory()
     * @param Carbon $startDate
     * @param carbon $endDate
     * @param User $seller
     * @return mixed
     */
    protected function calculateWeightForPeriodByCategory(Carbon $startDate, carbon $endDate, User $seller = null)
    {
        $query = Download::where('first_downloaded_at', '>=', $startDate)
            ->where('first_downloaded_at', '<=', $endDate);

        // This is a hack to allow months before september to include excluded users in the calculation. We don't want
        // to alter previous months' seller stats. This does however mean that we can't add new excluded users before
        // reworking how seller stats work. If we add more excluded users before reworking then previous months' stats
        // will retroactively be changed to exclude newly added exclude users as well. We don't want to mess with past
        // data. So we'll need to add a new table or something that saves seller stats.
        if ($startDate->gte(Carbon::parse('2019-09-01'))) {
            $query->whereRaw('downloads.user_id NOT IN (SELECT user_id FROM users_excluded_from_payout_calculations)');
        }

        // If seller is provided the query is much faster.
        if ($seller) {
            $query->where('products.seller_id', '=', $seller->id)
                ->where('products.owned_by_ma', '=', 0);
        }

        $query->select(DB::raw('products.category_id, SUM(downloads.weight) as weight'))
            ->leftJoin('products', 'products.id', '=', 'downloads.product_id')
            ->groupBy('category_id');

        return $query->get();
    }

    /**
     * Return a cached version of category weights
     * NOTE: This function will lock for up to 30 seconds, so that only
     * one instance is running the slow query at a time. If it fails to acquire
     * the lock in 30 seconds, it will throw an exception
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param User $seller
     * @return int
     * @throws Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function getWeightForPeriodByCategory(Carbon $startDate, Carbon $endDate, User $seller = null)
    {
        $cache_timeout = 10;
        $key = "category-weight-{$startDate}-{$endDate}";
        if (!is_null($seller))
            $key .= "-{$seller->id}";

        //@TODO(abiusx): Abstract the following mechanism into a function or trait for other areas of the code
        // If not in cache, cache it
        if (!Cache::has($key))
        {
            $lock = $key . "-lock";
            // Wait up to 30 seconds to acquire lock, or throw timeout exception
            if (Cache::lock($lock)->block(30))
            {
                // During the lock wait, other processes might've created the cache entry, check:
                if (!Cache::has($key))
                {
                    // We need a try block to make sure even if there's an error in the code, we release the lock.
                    try {
                        $res = $this->calculateWeightForPeriodByCategory($startDate, $endDate, $seller);
                    }
                    catch (\Throwable $e) {
                        Cache::lock($lock)->release();
                        throw $e;
                    }
                    Cache::put($key, $res, $cache_timeout);
                }
                Cache::lock($lock)->release();
            }
        }
        return Cache::get($key);
    }

    public function totalWeight($startDate, $endDate)
    {
        return Cache::remember("totalWeight-{$startDate}-{$endDate}", 10, function () use ($startDate, $endDate) {
            return $this->getWeightForPeriodByCategory($startDate, $endDate)->sum('weight');
        });
    }

    private function incrementDownloadCount(Download $download)
    {
        $download->download_count += 1;
        $download->save();

        return $download;
    }

    public function userDownloadedProductIds($userId, array $productIds): array
    {
        return Download::query()
            ->withTrashed()
            ->where('user_id', '=', $userId)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();
    }
}
