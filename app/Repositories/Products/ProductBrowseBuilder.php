<?php namespace MotionArray\Repositories\Products;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use MotionArray\Models\Category;
use MotionArray\Models\Product;
use Cache;
use MotionArray\Models\StaticData\ProductStatuses;

trait ProductBrowseBuilder
{

    public function getDiverseProducts($limit)
    {
        $cacheKey = 'diverse-products' . $limit;

        $cachedResults = Cache::get($cacheKey);

        if ($cachedResults) {
            return $cachedResults;
        }

        $results = [];

        $categories = Category::all();

        $productsByCategory = [];

        $numberOfProductsByCat = ceil($limit / $categories->count());

        foreach ($categories as $category) {
            $productsByCategory[$category->id] = $this->getProductsByCategory($category->id, $page_no = 1, $numberOfProductsByCat * 2);
        }

        while (count($productsByCategory)) {
            foreach ($productsByCategory as $categoryId => $products) {
                if ($products->count()) {
                    $results[] = $products->shift();
                } else {
                    unset($productsByCategory[$categoryId]);
                }

                if (count($results) == $limit) {
                    break 2;
                }
            }
        }

        $expiresAt = Carbon::now()->addMinutes(10);

        Cache::put($cacheKey, $results, $expiresAt);

        return $results;
    }

    private function orderByDownloads($type = 'month', $query = null)
    {
        $start_date = null;

        if (!$query) {
            $query = Product::query();
        }

        $query
            ->select('product_downloads_count.*', 'products.*')
            ->leftJoin('product_downloads_count', 'product_downloads_count.product_id', '=', 'products.id');

        if ($type == 'downloads') {
            $query->orderBy('product_downloads_count.downloads_last_month', 'desc');
        } elseif ($type == 'downloads-6months') {
            $query->orderBy('product_downloads_count.downloads_last_six_months', 'desc');
        } else {
            $query->orderBy('product_downloads_count.downloads_all_time', 'desc');
        }

        return $query;
    }

    public function getProducts(
        $page_no = 1,
        $products_per_page = 24,
        $filters = null,
        $order_by = 'published_at',
        $order = 'desc',
        $categoryId = null
    )
    {
        $query = $this->publicProductsQuery();

        if ($categoryId) {
            $query->where('category_id', '=', $categoryId);
        }

        if (preg_match('#^downloads#i', $filters)) {
            $query = $this->orderByDownloads($filters, $query)
                ->where('products.free', "=", 0);
        } else {
            $query = $query->orderBy($order_by, $order);
        }

        return $query
            ->skip(($page_no - 1) * $products_per_page)
            ->take($products_per_page)
            ->get();
    }

    public function getSellerProducts($userId, $page = 1, $perPage = 9, $filters = null)
    {
        $query = $this->getSellerProductsQuery($userId, $page, $perPage);

        if (preg_match('#^downloads#i', $filters)) {
            $query = $this->orderByDownloads($filters, $query);
        } else {
            $query->orderBy('published_at', 'desc');
        }

        return $query->get();
    }

    private function getSellerProductsQuery($userId, $page = 1, $perPage = 9)
    {
        $query = $this->publicProductsQuery()
            ->where('seller_id', '=', $userId)
            ->where('owned_by_ma', '=', false)
            ->where(function ($query) {
                $query->where('free', '=', 0)
                    ->orWhere('seller_id', '>', 3);
            });

        $query->where('products.credit_seller', '=', 1);


        return $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage);
    }

    public function getSellerProductsCount($userId)
    {
        return $this->publicProductsQuery()
            ->where('seller_id', '=', $userId)
            ->where(function ($query) {
                $query->where('free', '=', 0)
                    ->orWhere('seller_id', '>', 3);
            })
            ->where('credit_seller', '=', 1)
            ->count();
    }

    public function getProductsByCategory(
        $categoryId,
        $page = 1,
        $productsPerPage = 32,
        $filters = null,
        $orderBy = 'published_at',
        $order = 'desc'
    )
    {
        return $this->getProductsByCategoryQuery($categoryId, $page, $productsPerPage, $filters, $orderBy, $order)
            ->get();
    }

    public function getProductIdsByCategory(
        $categoryId,
        $page = 1,
        $productsPerPage = 32,
        $filters = null,
        $orderBy = 'published_at',
        $order = 'desc'
    ): array
    {
        return $this->getProductsByCategoryQuery($categoryId, $page, $productsPerPage, $filters, $orderBy, $order)
            ->pluck('id')
            ->toArray();
    }

    private function getProductsByCategoryQuery(
        $categoryId,
        $page = 1,
        $productsPerPage = 32,
        $filters = null,
        $orderBy = 'published_at',
        $order = 'desc'
    )
    {
        $query = $this->publicProductsQuery()
            ->where('category_id', '=', $categoryId);

        if (preg_match('#^downloads#i', $filters)) {
            $query = $query->where('products.free', "=", 0);

            $query = $this->orderByDownloads($filters, $query);
        } else {
            $query->orderBy($orderBy, $order);
        }

        if ($productsPerPage !== -1) {
            $query->take($productsPerPage)
                ->skip(($page - 1) * $productsPerPage);
        }

        return $query;
    }

    public function getProductSiteMapDataByCategoryQuery($categoryId): Builder
    {
        return $this->publicProductsQuery()
            ->select([
                'id',
                'slug',
                'published_at'
            ])
            ->where('category_id', '=', $categoryId)
            ->orderBy('published_at', 'desc');
    }

    public function getProductsCount()
    {
        $query = $this->publicProductsQuery();

        return Cache::remember("getProductsCount", 5, function () use ($query) {
            return $query->count();
        });
    }

    public function getProductsInCategoryCount($category_id)
    {
        $query = $this->publicProductsQuery()
            ->where('category_id', '=', $category_id);

        return $query->count();
    }

    public function publicProductsQuery()
    {
        $query = Product::query()
            ->where('product_status_id', '=', ProductStatuses::PUBLISHED_ID);

        return $query;
    }
}
