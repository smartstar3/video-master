<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Cookie;
use MotionArray\Repositories\CollectionRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Mailers\CollectionMailer;
use MotionArray\Models\Collection;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use Redirect;
use View;
use Auth;
use Request;

/**
 * @TODO stop using this for api routes, use this instead: MotionArray\Http\Controllers\API\CollectionsController
 * the methods that return redirects and widget html prevented this controller from being used for new code
 */
class CollectionsController extends BaseController
{
    protected $paginationRange = 10;

    protected $redirectTo = "/";

    protected $collection;

    protected $product;

    protected $mailer;

    public function __construct(CollectionRepository $collection, CollectionMailer $mailer, ProductRepository $product)
    {
        $this->collection = $collection;
        $this->product = $product;
        $this->mailer = $mailer;
    }

    // @TODO remove ajax json support
    public function create()
    {
        /**
         * Get inputs
         */
        $inputs = Request::all();
        /**
         * Check if authenticated
         */
        if (Auth::check()) {
            if (isset($inputs["new-collection"]) && !empty($inputs["new-collection"])) {
                $collection = new Collection;
                $collection->title = $inputs["new-collection"];
                $collection->user_id = Auth::user()->id;
                $collection->slug = $collection->generateSlug();
            } elseif (isset($inputs["existing-collection"])) {
                $collection = Collection::where("user_id", "=", Auth::user()->id)->where("slug", "=", $inputs["existing-collection"])->firstOrFail();
            }

            $product = Product::find($inputs["product_id"]);

            $is_duplicate = $collection->products()
                ->where('product_id', '=', $product->id)
                ->count();

            if ($is_duplicate == 0) {
                $product->collections()->save($collection);
                $collection->push();
            }

            if (Request::ajax()) {
                return $product->present()->collectionWidget($inputs["type"]);
            }

            return Redirect::back();
        }
    }

    public function rename()
    {
        $inputs = Request::all();
        $slug = $inputs["slug"];
        $collection = Collection::where("slug", "=", $slug)
            ->update(['title' => $inputs["collection-form-rename"]]);

        return Redirect::back();
    }

    /**
     * Remove a product from a collection.
     */
    public function remove()
    {
        $inputs = Request::all();
        $product_id = $inputs["product_id"];
        $slug = $inputs["slug"];

        $collection = Collection::where("slug", "=", $slug)->firstOrFail();
        $collection->products()->detach($product_id);

        $product = Product::where("id", "=", $product_id)->firstOrFail();

        if (Request::ajax()) {
            return $product->present()->collectionWidget($inputs["type"]);
        }

        return Redirect::back();
    }

    /**
     * Delete a collection
     */
    public function delete()
    {
        $inputs = Request::all();
        $slug = $inputs["slug"];
        if (Auth::check()) {
            $collection = Collection::where("slug", "=", $slug)->firstOrFail();
            $collection->delete();
        }

        return Redirect::to("/account/collections");
    }

    public function share()
    {
        /**
         * Get inputs
         */
        $inputs = Request::all();

        $slug = $inputs["slug"];
        $collection = Collection::where("slug", "=", $slug)->firstOrFail();
        $url = Request::root() . "/browse/collection/" . $collection->slug;
        $sender = Auth::user();
        $recipient = $inputs["email"];

        $this->mailer->shareCollection($url, $sender, $recipient);

        return Redirect::back();
    }

    public function index($slug)
    {
        /**
         * Get parameters
         */
        $page_no = Request::get("page", 1);
        $category_slug = Request::get("category");
        $products_per_page = Request::get("show", 24);
        $filter = Request::get("filter");
        Cookie::queue('last_freemium_collection_url', Request::getUri(), 30);

        /**
         * Find collection
         */
        $collection = Collection::where("slug", "=", $slug)->first();

        if (!$collection) {
            return Redirect::to($this->redirectTo);
        }

        $title = $collection->title;

        $user = User::where("id", "=", $collection->user_id)->first();
        $user_name = $user->firstname . " " . $user->lastname;

        /**
         * Get product by collection
         */
        if ($category_slug && $category_slug != "all") {
            if ($category_slug == "free") {
                $products = $this->collection->getCollectionFreeProducts($slug, $page_no, $products_per_page, $filter);
                $product_count = $this->collection->getCollectionFreeProductsCount($slug, $filter);
            } else {
                $products = $this->collection->getCollectionProductsByCategory($slug, $category_slug, $page_no, $products_per_page, $filter);
                $product_count = $this->collection->getCollectionProductsByCategoryCount($slug, $category_slug, $filter);
            }
        } else {
            $products = $this->collection->getCollectionProducts($slug, null, null, $page_no, $products_per_page, $filter);
            $product_count = $this->collection->getCollectionProductsCount($slug, null, null, $filter);
        }

        $books = $this->collection->getBooks($collection);

        /**
         * Filters
         */
        $filters = "";
        if ($category_slug) {
            $filters .= "&category={$category_slug}";
        }
        if ($filter) {
            $filters .= "&filter={$filter}";
        }

        /**
         * Set pagination
         */
        $pagination = [
            'products_per_page' => $products_per_page,
            'current_page_no' => $page_no,
            'product_count' => $product_count,
            'pagination_range' => $this->paginationRange,
            'filters' => $filters
        ];

        return View::make("site.browse.collection", compact('title', 'products', 'user_name', 'pagination', 'books'));
    }
}
