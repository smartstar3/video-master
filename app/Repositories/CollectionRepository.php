<?php namespace MotionArray\Repositories;

use MotionArray\Models\User;
use MotionArray\Services\Encoding\EncodingInterface;
use Carbon\Carbon;
use App;
use Config;
use Event;
use AWS;
use File;
use DB;
use MotionArray\Models\Product;
use MotionArray\Models\Collection;
use MotionArray\Models\Category;

class CollectionRepository
{

    public $errors = [];

    public $collections_per_page = 9;


    public function allCollections($order_by = 'created_at', $order = 'desc', $user_id = null)
    {
        if ($user_id) {
            return Collection::where('user_id', '=', $user_id)
                ->orderBy($order_by, $order)
                ->get();
        }

        return Collection::orderBy($order_by, $order)->get();
    }

    /**
     * Save collection and all relationships
     *
     * @param  array $attributes
     *
     * @return boolean
     */
    public function make(array $attributes)
    {

        // Set attributes
        $collection = new Product;

        $collection->user_id = (int)$attributes['user_id'];
        $collection->title = $attributes['title'];
        $collection->slug = $collection->generateSlug();

        // Save the collection
        if ($collection->save()) {
            // Store products
            if ($attributes['product_id[]']) {
                if (is_array($attributes['product_id[]'])) {
                    foreach ($attributes['product_id[]'] as $product_id) {
                        $collection->products()->save(Product::find($product_id));
                    }
                } else {
                    $collection->products()->save(Product::find($attributes['product_id[]']));
                }
            }

            return $collection;
        }

        // Set any validation errors
        $this->errors = $collection->errors;

        return false;
    }


    /**
     * Update collection and any relationships
     *
     * @param  integer $id Product ID
     * @param  array $attributes
     *
     * @return boolean
     */
    public function update($id, array $attributes)
    {
        $collection = Collection::find($id);

        // Set attributes
        if (isset($attributes['user_id'])) $collection->user_id = (int)$attributes['user_id'];
        if (isset($attributes['title'])) $collection->title = (int)$attributes['title'];
        if (isset($attributes['slug'])) $collection->slug = (int)$attributes['slug'];

        if ($collection->save()) {
            // Store products
            if ($attributes['product_id[]']) {
                if (is_array($attributes['product_id[]'])) {
                    foreach ($attributes['product_id[]'] as $product_id) {
                        $collection->products()->save(Product::find($product_id));
                    }
                } else {
                    $collection->products()->save(Product::find($attributes['product_id[]']));
                }
            }

            return $collection;
        }

        // Set any validation errors
        $this->errors = $collection->errors;

        return false;
    }

    public function getCollectionsByUser(User $user, $page_no = null, $per_page = null)
    {
        $query = Collection::where("user_id", "=", $user->id)
            ->orderBy('id', 'desc');

        if ($page_no && $per_page) {
            $query = $query
                ->skip(($page_no - 1) * $per_page)
                ->take($per_page);
        }

        return $query->get();
    }

    public function getCollectionsWithProductIdsByUser(User $user)
    {
        $collections = Collection::query()
            ->where("user_id", "=", $user->id)
            ->select([
                'id',
                'title',
                'slug',
            ])
            ->orderBy('id', 'desc')
            ->get();

        $collections->load(['collectionProducts' => function ($query) {
            $query->select('collection_product.id', 'product_id', 'collection_id');
        }]);

        return $collections->map(function ($collection) {
            $collection->product_ids = $collection->collectionProducts->pluck('product_id')->toArray();

            $keys = [
                'id',
                'slug',
                'title',
                'product_ids'
            ];

            return array_only($collection->toArray(), $keys);
        });
    }

    public function getCollectionProducts($slug, $category_id = null, $free = null, $page_no = 1, $products_per_page = 9, $filters = null, $order_by = 'updated_at', $order = 'desc')
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();

        $query = $collection->products();

        if ($category_id) {
            $query->where("products.category_id", '=', $category_id);
        }

        if ($free) {
            $query->where('products.free', '=', 1);
        }

        if ($filters == "downloads") {
            $start_date = Carbon::now()->subMonth();
            $end_date = Carbon::now();

            $query = $query->join('downloads', 'products.id', '=', 'downloads.product_id')
                ->whereBetween('downloads.first_downloaded_at', [$start_date, $end_date])
                ->groupBy('products.id')
                ->orderBy('download_count', 'desc');
        } else {
            $query = $query->orderBy($order_by, $order);
        }

        return $query->skip(($page_no - 1) * $products_per_page)
            ->take($products_per_page)
            ->get();
    }


    public function getCollectionProductsCount($slug, $category_id = null, $free = null, $filters = null)
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();

        $query = $collection->products();

        if ($category_id) {
            $query->where("products.category_id", '=', $category_id);
        }

        if ($free) {
            $query->where('products.free', '=', 1);
        }

        return $query->count();
    }

    public function getCollectionProductsByCategory($slug, $category_slug, $page_no = 1, $products_per_page = 9, $filters = null, $order_by = 'updated_at', $order = 'desc')
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();
        $category = Category::where("slug", "=", $category_slug)->first();

        if ($filters == "downloads") {
            $start_date = Carbon::now()->subMonth();
            $end_date = Carbon::now();

            return $collection->products()
                ->join('downloads', 'products.id', '=', 'downloads.product_id')
                ->where("category_id", "=", $category->id)
                ->whereBetween('downloads.first_downloaded_at', [$start_date, $end_date])
                ->groupBy('products.id')
                ->orderBy('download_count', 'desc')
                ->skip(($page_no - 1) * $products_per_page)
                ->take($products_per_page)
                ->get();
        }

        return $collection->products()
            ->where("category_id", "=", $category->id)
            ->orderBy($order_by, $order)
            ->skip(($page_no - 1) * $products_per_page)
            ->take($products_per_page)
            ->get();
    }

    public function getCollectionProductsByCategoryCount($slug, $category_slug, $filters = null)
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();
        $category = Category::where("slug", "=", $category_slug)->firstOrFail();

        return $collection->products()
            ->where("category_id", "=", $category->id)
            ->count();
    }

    public function getCollectionFreeProducts($slug, $page_no = 1, $products_per_page = 9, $filters = null, $order_by = 'updated_at', $order = 'desc')
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();

        if ($filters == "downloads") {
            $start_date = Carbon::now()->subMonth();
            $end_date = Carbon::now();

            return $collection->products()
                ->join('downloads', 'products.id', '=', 'downloads.product_id')
                ->where('free', '=', 1)
                ->whereBetween('downloads.first_downloaded_at', [$start_date, $end_date])
                ->groupBy('products.id')
                ->orderBy('download_count', 'desc')
                ->skip(($page_no - 1) * $products_per_page)
                ->take($products_per_page)
                ->get();
        }

        return $collection->products()
            ->where('free', '=', 1)
            ->orderBy($order_by, $order)
            ->skip(($page_no - 1) * $products_per_page)
            ->take($products_per_page)
            ->get();
    }


    public function getCollectionFreeProductsCount($slug, $category_slug, $filters = null)
    {
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();

        return $collection->products()
            ->where('free', '=', 1)
            ->count();
    }

    /**
     * Returns books in collection
     *
     * @param Collection $collection
     */
    public function getBooks(Collection $collection)
    {
        return $collection->books()->get();
    }

}
