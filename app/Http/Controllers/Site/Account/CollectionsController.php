<?php namespace MotionArray\Http\Controllers\Site\Account;

use MotionArray\Models\Category;
use MotionArray\Repositories\CollectionRepository;
use MotionArray\Http\Controllers\Site\BaseController;
use MotionArray\Models\Collection;
use Redirect;
use View;
use Auth;
use Request;


class CollectionsController extends BaseController
{
    protected $paginationRange = 10;
    protected $redirectTo = "/";

    protected $collection;

    protected $userRepo;

    protected $sellerPayout;

    public function __construct(
        CollectionRepository $collectionRepository
    )
    {
        $this->collection = $collectionRepository;
    }

    /**
     * This lists out all of the collections a user has created.
     */
    public function index()
    {
        if (Auth::user()->collections()->count()) {
            $page_no = Request::get("page") ? Request::get("page") : 1;
            $per_page = Request::get("show") ? Request::get("show") : 24;

            $collections = $this->collection->getCollectionsByUser(Auth::user(), $page_no, $per_page);

            $pagination = [
                'products_per_page' => $per_page,
                'current_page_no' => $page_no,
                'product_count' => Auth::user()->collections->count(),
                'pagination_range' => $this->paginationRange,
            ];

            return view("site.account.collections", compact('collections', 'pagination'));
        }

        return Redirect::to("account");
    }

    /**
     * This shows a single collection.
     */
    public function show($slug)
    {
        $page_no = Request::get("page") ? Request::get("page") : 1;
        $products_per_page = Request::get("show") ? Request::get("show") : 24;

        $category_slug = Request::get('category');
        $category_id = null;
        $free = ($category_slug == 'free');

        if ($category_slug && $category_slug != 'all' && $category_slug != 'free') {
            $category = Category::where("slug", "=", $category_slug)->first();
            $category_id = $category->id;
        }

        $collection = Collection::where("user_id", "=", Auth::user()->id)->where("slug", "=", $slug)->firstOrFail();
        $title = $collection->title;
        $products = $this->collection->getCollectionProducts($slug, $category_id, $free, $page_no, $products_per_page);
        $products_count = $this->collection->getCollectionProductsCount($slug, $category_id, $free);
        //$collection->products->count();

        $books = $this->collection->getBooks($collection);

        $pagination = [
            'products_per_page' => $products_per_page,
            'current_page_no' => $page_no,
            'product_count' => $products_count,
            'pagination_range' => $this->paginationRange,
            'remove_browse' => true
        ];

        if ($category_slug) {
            $pagination['filters'] = '&category=' . $category_slug;
        }

        return view("site.account.collection", compact('slug', 'title', 'products', 'pagination', 'books'));
    }
}
