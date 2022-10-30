<?php namespace MotionArray\Http\Controllers\Site;

use Illuminate\Support\Facades\Auth;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Repositories\PageRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\Algolia\AlgoliaClientDataService;
use MotionArray\Services\Algolia\DbToAlgoliaResponseConverter;
use MotionArray\Services\Algolia\Queries\AlgoliaLatestQuery;
use MotionArray\Services\Product\LatestProductsService;

class HomeController extends BaseController
{
    /**
     * @var PageRepository
     */
    private $page;

    /**
     * @var ProductRepository
     */
    private $product;

    /**
     * @var AlgoliaClientDataService
     */
    private $algoliaClientData;

    /**
     * @var LatestProductsService
     */
    private $latestProductsService;

    /**
     * @var DbToAlgoliaResponseConverter
     */
    private $dbToAlgoliaResponse;

    public function __construct(
        PageRepository $page,
        ProductRepository $product,
        AlgoliaClientDataService $algoliaClientData,
        LatestProductsService $latestProductsService
    )
    {
        $this->page = $page;
        $this->product = $product;
        $this->algoliaClientData = $algoliaClientData;
        $this->latestProductsService = $latestProductsService;
    }

    /**
     * Display Home Page
     *
     * @return mixed
     */
    public function index()
    {
        $entry = $this->page->getHomeEntry();
        $loggedIn = Auth::check();

        $products = $this->latestProductsService->get();

        $viewData = [
            'entry' => $entry,
            'JS_DATA' => [
                'browse_category_filters' => $this->algoliaClientData->browseMetaData(),
                'browse_legacy_category_slugs' => Categories::legacySlugs(),
                'browse_legacy_sub_category_slugs' => SubCategories::legacySlugs(),
                'browse_latest' => [
                    'products' => $products,
                ],
                'user_logged_in' => $loggedIn,
            ]
        ];

        return view('site.pages.home', $viewData);
    }
}
