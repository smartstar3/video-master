<?php namespace MotionArray\Http\Controllers\API;

use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\Product;
use MotionArray\Models\Category;
use MotionArray\Services\Submission\SubmissionService;
use Response;
use File;
use Config;
use AWS;

class ProductsController extends BaseController
{
    /**
     * Product Repository
     *
     * @var Product
     */
    protected $product;
    protected $category;

    /**
     * @var SubmissionService
     */
    protected $submissionService;

    public $products_per_page = 24;

    public function __construct(
        ProductRepository $product,
        Category $category,
        SubmissionService $submissionService)
    {
        $this->product = $product;
        $this->category = $category;
        $this->submissionService = $submissionService;
    }

    public function show($id)
    {
        $product = $this->product->findById($id);
        $preparedProducts = $this->submissionService->prepareProductJson($product);

        return Response::json($preparedProducts);
    }

    public function productsByProcessing()
    {
        $products = $this->product->filterByProcessing('created_at', 'desc');

        $response = $this->prepareProductsJSON($products);

        return Response::json($response);
    }

    public function productsByUnpublished()
    {
        $products = $this->product->filterByUnpublished('created_at', 'desc');
        $response = $this->prepareProductsJSON($products);

        return Response::json($response);
    }

    public function productsByCategory($category_id, $page_no = 0)
    {
        $products = $this->product->getProductsByCategory($category_id, $page_no);
        $response = $this->prepareProductsJSON($products);

        return Response::json($response);
    }

    public function productsSearchResults($query, $page_no = 0)
    {
        $products = $this->product->getSearchResults($query, null, 0, $page_no);
        $response = $this->prepareProductsJSON($products);

        return Response::json($response);
    }

    public function productsSearchResultsCount($query)
    {
        $response['query'] = $query;
        $response['products_per_page'] = $this->products_per_page;
        $response['count'] = $this->product->getSearchResultsCount($query);

        return Response::json($response);
    }

    public function totalProductsInCategory($category_id)
    {
        $response['category_id'] = $category_id;
        $response['products_per_page'] = $this->products_per_page;
        $response['count'] = $this->product->getProductsInCategoryCount($category_id);

        return Response::json($response);
    }

    public function prepareProductsJSON($products)
    {
        $response = [];

        foreach ($products as $key => $product) {
            $response[$key] = $this->submissionService->prepareProductJson($product); // $product->toArray();
        }

        return $response;
    }


    public function getVideoFormats($id)
    {
        $product = $this->product->findById($id);

        $previews = $product->activePreviewFiles();

        $json = [];
        foreach ($previews as $format) {
            $json[str_replace(" ", "_", $format['label'])] = $format['url'];
        }

        return Response::json($json);
    }

    public function getPackageUrl($id)
    {
        $product = $this->product->findById($id);

        $package_file_path = $product->package_file_path;

        $json["package_file_path"] = $package_file_path;

        return Response::json($json);
    }

    /**
     * Get all TEMPORARY audio placeholders
     *
     * TODO: Eventually audio placeholders need ot be automatically generated from the audio file waveform. This is a
     * static approach for expedience of launch.
     */
    public function getAudioPlaceholders($id = null)
    {
        if (!is_null($id)) {
            $product = $this->product->findById($id);
        }

        $response = [
            'path' => Config::get("info.audio_placeholders_url"),
            'placeholders' => []
        ];
        $i = 0;
        $selected = 0;
        foreach (File::allFiles(Config::get('info.audio_placeholders_path')) as $partial) {
            if (isset($product) && Config::get('info.audio_placeholders_url') . $partial->getFilename() == $product->audio_placeholder) {
                $selected = 1;
            }

            $response['placeholders'][$i] = [
                'url' => Config::get('info.audio_placeholders_url') . $partial->getFilename(),
                'selected' => $selected
            ];

            $selected = 0;
            $i++;
        }

        return Response::json($response);
    }
}
