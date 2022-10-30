<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request as HttpRequest;

use MotionArray\Http\Requests\Site\BrowseRequest;
use MotionArray\Http\Requests\Site\BrowseSellerRequest;
use MotionArray\Jobs\CreateProductImpression;
use MotionArray\Mailers\ProducerMailer;
use MotionArray\Models\CustomGallery;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\Resolutions;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Models\SubCategory;
use MotionArray\Repositories\BrowseRepository;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\User;
use MotionArray\Models\Category;
use MotionArray\Services\Algolia\AlgoliaClientDataService;
use MotionArray\Services\Algolia\DbToAlgoliaResponseConverter;
use MotionArray\Services\Algolia\AlgoliaSearchService;

class BrowseController extends BaseController
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ProductRepository
     */
    protected $product;

    /**
     * @var BrowseRepository
     */
    protected $browse;

    /**
     * @var \MotionArray\Repositories\PageRepository
     */
    protected $page;

    protected $redirectTo = "/";

    protected $paginationRange = 10;

    protected $products_per_page = 24;

    /**
     * @var AlgoliaClientDataService
     */
    private $algoliaClientData;

    public function __construct(
        ProductRepository $product,
        Category $category,
        BrowseRepository $browse,
        PageRepository $page,
        AlgoliaClientDataService $algoliaClientData
    )
    {
        $this->product = $product;
        $this->category = $category;
        $this->browse = $browse;
        $this->page = $page;
        $this->algoliaClientData = $algoliaClientData;
    }

    /**
     * Algolia Search
     *
     * @param BrowseRequest $request
     * @param null $categorySlug
     * @param null $subCategorySlug
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(BrowseRequest $request, $categorySlug = null, $subCategorySlug = null)
    {
        // Check whether old URLs are used, and if so, redirect to new URLs
        $redirect = $this->redirect();
        if ($redirect) {
            return $redirect;
        }

        $loggedIn = Auth::check();
        $userHasFreePlan = false;
        if ($loggedIn) {
            $userHasFreePlan = Auth::user()->is_plan_free;
        }

        $categoryData = $this->browse->getCategoryData($categorySlug, $subCategorySlug, $request);
        $category = $categoryData['category'];
        $subCategory = $categoryData['subcategory'];
        $secondaryContentPage = $this->browse->getSecondaryContentPage($request);
        $ogImage = $this->browse->getOGImage($category);

        if ($categorySlug && !$category) {
            return Redirect::to('/browse/');
        }

        if ($category && $subCategorySlug && !$subCategory) {
            return Redirect::to('/browse/' . $category->slug);
        }

        // If the query parameter 'pass_through_custom_gallery_slug' is set, then load a Custom Gallery
        // so that we can display its hero bar on the browse page.
        $customGallerySlug = $request->query('pass_through_custom_gallery_slug');
        $customGallery = null;
        if ($customGallerySlug) {
            $customGallery = CustomGallery::whereSlug($customGallerySlug)->first();
        }

        $siteTotalProductsCount = $this->product->totalProductCount();

        $seoTitle = false;
        $metaDescription = false;
        if ($category) {
            $seoTitle = $this->categorySeoTitle($category, $subCategory);
            $metaDescription = $this->categoryMetaDescription($category, $subCategory);
        }

        $viewData = [
            'category' => $category,
            'subCategory' => $subCategory,
            'previousUrl' => $request->getPreviousUrl(),
            'nextUrl' => $request->getNextUrl(),
            'secondary_content_page' => $secondaryContentPage,
            'ogImage' => $ogImage,
            'customGallery' => $customGallery,
            'siteTotalProductsCount' => $siteTotalProductsCount,
            'seoTitle' => $seoTitle,
            'metaDescription' => $metaDescription,
            'JS_DATA' => [
                'browse_category_filters' => $this->algoliaClientData->browseMetaData(),
                'browse_custom_gallery' => $customGallery,
                'browse_cache_enabled' => config('algolia.client_cache.enabled'),
                'user_logged_in' => $loggedIn,
                'user_has_free_plan' => $userHasFreePlan,
                'site_total_products_count' => $siteTotalProductsCount,
            ],
        ];

        return view('site.browse.index', $viewData);
    }

    public function customGallery(AlgoliaSearchService $service, BrowseRequest $request, string $slug)
    {
        $loggedIn = Auth::check();
        $userHasFreePlan = false;
        if ($loggedIn) {
            $userHasFreePlan = Auth::user()->is_plan_free;
        }

        $customGallery = CustomGallery::whereSlug($slug)->firstOrFail();
        $customGalleryData = $customGallery->toArray();
        $productIds = $customGallery->collection->products()->pluck('products.id')->toArray();
        $searchAttributes = [
            'onlyProductIds' => $productIds
        ];
        $results = $service->searchForSite($searchAttributes, Auth::user());

        $customGalleryData['products'] = $results['products'];

        $viewData = [
            'previousUrl' => $request->getPreviousUrl(),
            'nextUrl' => $request->getNextUrl(),
            'customGallery' => $customGallery,
            'JS_DATA' => [
                'browse_category_filters' => $this->algoliaClientData->browseMetaData(),
                'browse_custom_gallery' => $customGalleryData,
                'browse_cache_enabled' => config('algolia.client_cache.enabled'),
                'user_logged_in' => $loggedIn,
                'user_has_free_plan' => $userHasFreePlan,
                'algolia_index' => $results['algolia_index'],
                'algolia_options' => $results['algolia_options'],
                'algolia_search_tags' => $results['algolia_search_tags'],
                'meta' => $results['meta'],
            ],
        ];

        return view('site.browse.custom-gallery', $viewData);
    }

    protected function categorySeoTitle(Category $category, SubCategory $subCategory = null)
    {
        $subCategoryTitle = false;
        $categoryTitle = $category->seo_title;

        if ($subCategory) {
            $subCategoryTitleFallback = $subCategory->name . ' - ' . $category->seo_title;
            $subCategoryTitle = trim($subCategory->seo_title) ?: $subCategoryTitleFallback;
        }

        return $subCategoryTitle ?: $categoryTitle;
    }

    protected function categoryMetaDescription(Category $category, SubCategory $subCategory = null)
    {
        $subCategoryDescription = false;
        $categoryDescription = trim(strip_tags($category->meta_description));

        if ($subCategory) {
            $subCategoryDescription = trim(strip_tags($subCategory->meta_description));
        }

        return $subCategoryDescription ?: $categoryDescription;
    }

    // @TODO: Remove
    public function free(BrowseRequest $request)
    {
        return $this->index($request);
    }

    // @TODO: Remove
    public function category(BrowseRequest $request, $categorySlug)
    {
        return $this->index($request, $categorySlug);
    }

    // @TODO: Remove
    public function subCategory(BrowseRequest $request, $categorySlug, $subCategorySlug)
    {
        return $this->index($request, $categorySlug, $subCategorySlug);
    }

    /**
     * Redirects old search URLs to new search URLs
     *
     * @param bool $force
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($force = false)
    {
        $data = Request::all();

        if (isset($data['category']) || isset($data['show']) || $force) {

            $url = $force ? '/browse' : Request::path();

            if (isset($data['category'])) {
                if (trim($data['category'])) {
                    $url .= '/' . $data['category'];
                }

                unset($data['category']);
            }

            if (isset($data['show'])) {
                unset($data['show']);
            }

            if (count($data)) {
                $url .= '?' . http_build_query($data);
            }

            return Redirect::to($url, 301);
        }
    }

    // todo: Remove
    public function results()
    {
        return $this->redirect(true);
    }

    // todo: Remove
    public function noResults()
    {
        return $this->redirect(true);
    }

    /**
     * Product Details page
     *
     * @param $categorySlug
     * @param $productSlug
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function product($categorySlug, $productSlug)
    {
        $category = $this->category->where("slug", "=", $categorySlug)->first();

        if ($category) {
            $product = $this->product->findBySlug($productSlug, $category->id, true);

            if ($product && $product->slug != $productSlug) {
                return Redirect::to('/' . $categorySlug . '/' . $product->slug);
            }

            if ($product) {
                if (Auth::check()) {
                    dispatch(new CreateProductImpression($product, Auth::user()));
                }

                // If product was removed
                $downloadedBefore = Auth::check() && Auth::user()->hasDownloadedProductBefore($product->id);

                $isAdmin = Auth::check() && Auth::user()->isAdmin();

                // If product was removed
                if ($isAdmin || ($product->seller && (!$product->trashed() || $downloadedBefore))) {

                    $loggedIn = Auth::check();
                    $userHasFreePlan = false;
                    if ($loggedIn) {
                        $user = Auth::user();
                        $userHasFreePlan = $user->is_plan_free;
                    }

                    $keyWords = $product->tags()->get()->pluck('name');
                    $viewData = [
                        'product' => $product,
                        'JS_DATA' => [
                            'browse_category_filters' => $this->algoliaClientData->browseMetaData(),
                            'browse_cache_enabled' => config('algolia.client_cache.enabled'),
                            'browse_related' => [
                                'category_slug' => $category->slug,
                                'product_id' => $product->id,
                                'optional_words' => $keyWords,
                                'search_tags' => $keyWords,
                            ],
                            'user_logged_in' => $loggedIn,
                            'user_has_free_plan' => $userHasFreePlan,
                        ]
                    ];

                    return view("site.browse.details", $viewData);
                } else {
                    return Response::view("site.errors.404", [
                        'title' => 'Uh Oh. That product is no longer available.',
                        'description' => 'Sorry, but the product you were looking for is no longer available on our library. ' .
                            'This usually happens when the creator decides to remove it from Motion Array. ' .
                            'Fortunately, we add lots of new content every day!'
                    ], 404);
                }
            } else {
                //Lets check if this product has moved categories
                $product = $this->product->findBySlug($productSlug);

                if ($product) {
                    return redirect()->to($product->url, 301);
                }
            }
        }

        return app()->abort(404);
    }

    public function seller(BrowseSellerRequest $request, DbToAlgoliaResponseConverter $dbParser, $seller_slug)
    {
        $seller = User::where("slug", "=", $seller_slug)->firstOrFail();

        $page = $request->page();
        $productsPerPage = $request->perPage($this->products_per_page);
        $filter = $request->filter();

        $products = $this->product->getSellerProducts($seller->id, $page, $productsPerPage, $filter);
        $productCount = $this->product->getSellerProductsCount($seller->id);

        $products = $dbParser->prepareProducts($products);

        $pagination = [
            'products_per_page' => $productsPerPage,
            'current_page_no' => $page,
            'product_count' => $productCount,
            'pagination_range' => $this->paginationRange,
            'total_pages' => ceil($productCount / $productsPerPage)
        ];

        /**
         * Set stats
         */
        $stats = [
            'product_count' => $this->product->getSellerProductsCount($seller->id),
            'new_products' => $this->product->getProductsCountCreatedInLast(30, null, $seller->id),
            'new_period' => 30
        ];
        $entry = $this->page->getHomeEntry();

        $viewData = [
            'seller' => $seller,
            'stats' => $stats,
            'entry' => $entry,
            'pagination' => $pagination,
            'JS_DATA' => [
                'browse_category_filters' => $this->algoliaClientData->browseMetaData(),
                'browse_seller_products' => $products,
                'browse_cache_enabled' => config('algolia.client_cache.enabled'),
                'browse_seller_id' => $seller->id,
                'seller' => $seller->present()->seller(),
                'auth_user' => auth()->user()
            ]
        ];


        return view("site.browse.seller", $viewData);
    }


    /**
     * Sends contact message from producer page contact form
     *
     * @param $seller_slug
     * @param ProducerMailer $producerMailer
     */
    public function postSellerForm($seller_slug, ProducerMailer $producerMailer)
    {
        $seller = User::where("slug", "=", $seller_slug)->first();

        $form = Request::all();

        $producerMailer->producerContactForm($seller, $form);
    }
}
