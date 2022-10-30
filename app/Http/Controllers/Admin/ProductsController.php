<?php namespace MotionArray\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use MotionArray\Models\User;
use MotionArray\Policies\ProductPolicy;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\SubmissionRepository;
use MotionArray\Models\Category;
use MotionArray\Services\Cdn\PackageCdnChecker;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use View;
use Request;
use Redirect;
use Response;
use Auth;

class ProductsController extends BaseController
{

    use AuthorizesRequests;

    /**
     * Product Repository
     *
     * @var ProductRepository|ProductRepository
     */
    protected $product;

    /**
     * Submission Repository
     *
     * @var SubmissionRepository
     */
    protected $submissionRepo;

    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    public function __construct(
        ProductRepository $product,
        Category $category,
        SubmissionRepository $submissionRepository,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper
    )
    {
        $this->product = $product;
        $this->category = $category;
        $this->submissionRepo = $submissionRepository;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }


    public function index($id = null)
    {
        return View::make('admin.products.index', compact('id'));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $product = $this->product->findById($id);

        if (is_null($product)) {
            $response['state'] = "error";
            $response['errors'] = [
                "message" => "No product found",
            ];

            return Response::json($response);
        }

        $response['state'] = "success";
        $response['entry'] = $product->toArray();
        $response['entry']['downloads'] = $product->downloads()->count();
        $response['entry']['seller'] = $product->seller()->first()->toArray();
        $response['entry']['category'] = $product->category()->first()->toArray();
        $response['entry']['compressions'] = $product->compressions()->get()->toArray();
        $response['entry']['formats'] = $product->formats()->get()->toArray();
        $response['entry']['resolutions'] = $product->resolutions()->get()->toArray();
        $response['entry']['versions'] = $product->versions()->get()->toArray();
        $response['entry']['bpms'] = $product->bpms()->get()->toArray();
        $response['entry']['fpss'] = $product->fpss()->get()->toArray();
        $response['entry']['sample_rates'] = $product->sampleRates()->get()->toArray();
        $response['entry']['plugins'] = $product->plugins()->get()->toArray();
        $response['entry']['previews'] = $product->activePreviewFiles();
        $response['entry']['music'] = $product->music()->get()->toArray();
        $response['entry']['tags'] = $product->tags()->get()->toArray();

        $preview_type = $response['entry']['category']['preview_type'];
        $previewPolicy = $product->generateAWSPolicy($preview_type);
        $response['entry']['preview'] = ['bucket' => $product->getBucket($preview_type),
            'awsKey' => $product->getAWSKey(),
            'awsPolicy' => $previewPolicy,
            'awsSignature' => $product->generateAWSSignature($previewPolicy),
            'bucketKey' => $product->getBucketKey($preview_type),
            'newFilename' => $product->generateFilename($product->previewPrefix . $product->id)];

        $packagePolicy = $product->generateAWSPolicy('package');
        $response['entry']['package'] = ['bucket' => $product->getBucket('package'),
            'awsKey' => $product->getAWSKey(),
            'awsPolicy' => $packagePolicy,
            'awsSignature' => $product->generateAWSSignature($packagePolicy),
            'bucketKey' => $product->getBucketKey('package'),
            'newFilename' => $product->generateFilename($product->packagePrefix . $product->id)];
        $response['entry']['preview_type'] = $product->category->preview_type;

        return Response::json($response);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $sellers = $this->product->sellersList();

        $categories = $this->categoriesWithRelationsHelper->categoriesWithVersions(Auth::user())->toArray();
        $sub_categories = $this->product->allSubCategories();

        $compressions = $this->product->allCompressions();
        $resolutions = $this->product->allResolutions();
        $versions = $this->product->allVersions();
        $formats = $this->product->allFormats();
        $fpss = $this->product->allFps();
        $bpms = $this->product->allBpm();
        $sample_rates = $this->product->allSampleRates();
        $plugins = $this->product->allPlugins();

        return View::make('admin.forms.product', compact('sellers', 'categories', 'sub_categories', 'compressions', 'resolutions', 'versions', 'formats', 'fpss', 'bpms', 'sample_rates', 'plugins'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $product = $this->product->findById($id);

        if (is_null($product)) {
            return Redirect::route('mabackend.products.index');
        }

        $allSellers = $this->product->allSellers();

        $categories = $this->categoriesWithRelationsHelper->categoriesWithVersions(Auth::user())->toArray();
        $sub_categories = $this->product->allSubCategories();
        $compressions = $this->product->allCompressions();
        $resolutions = $this->product->allResolutions();
        $versions = $this->product->allVersions();
        $formats = $this->product->allFormats();
        $fpss = $this->product->allFps();
        $bpms = $this->product->allBpm();
        $sample_rates = $this->product->allSampleRates();
        $plugins = $this->product->allPlugins();
        $edit = true;
        $preview_type = $product->category()->first()->preview_type;

        $sellers = [];
        foreach ($allSellers as $seller) {
            $sellers[$seller->id] = $seller->name;
        }

        $tags = "";
        foreach ($product->tags()->get() as $tag) {
            $tags .= $tag->name . ", ";
        }
        $tags = substr($tags, 0, -2);

        $product->tags = $tags;

        return View::make('admin.forms.product', compact('product', 'sellers', 'categories', 'sub_categories', 'compressions', 'resolutions', 'versions', 'formats', 'fpss', 'bpms', 'sample_rates', 'plugins', 'edit', 'preview_type'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $inputs = array_except(Request::all(), '_method');
        $inputs = $inputs + [
                'encoding_status_id' => 1, // Waiting
                'product_status_id' => 3  // Processing
            ];

        $saveResults = $this->product->make($inputs);

        if ($saveResults) {
            $response = $saveResults->toArray();
            $response['downloads'] = $saveResults->downloads()->count();
            $response['seller'] = $saveResults->seller()->first()->toArray();
            $response['category'] = $saveResults->category()->first()->toArray();
            $response['sub_categories'] = $saveResults->subCategories()->get()->toArray();
            $response['compressions'] = $saveResults->compressions()->get()->toArray();
            $response['formats'] = $saveResults->formats()->get()->toArray();
            $response['resolutions'] = $saveResults->resolutions()->get()->toArray();
            $response['versions'] = $saveResults->versions()->get()->toArray();
            $response['bpms'] = $saveResults->bpms()->get()->toArray();
            $response['fpss'] = $saveResults->fpss()->get()->toArray();
            $response['sample_rates'] = $saveResults->sampleRates()->get()->toArray();
            $response['plugins'] = $saveResults->plugins()->get()->toArray();
            $response['previews'] = $saveResults->activePreviewFiles();
            $response['music'] = $saveResults->music()->get()->toArray();
            $response['tags'] = $saveResults->tags()->get()->toArray();

            $preview_type = $response['category']['preview_type'];
            $previewPolicy = $saveResults->generateAWSPolicy($preview_type);
            $response['preview'] = ['bucket' => $saveResults->getBucket($preview_type),
                'awsKey' => $saveResults->getAWSKey(),
                'awsPolicy' => $previewPolicy,
                'awsSignature' => $saveResults->generateAWSSignature($previewPolicy),
                'bucketKey' => $saveResults->getBucketKey($preview_type),
                'newFilename' => $saveResults->generateFilename($saveResults->previewPrefix . $saveResults->id)];

            $packagePolicy = $saveResults->generateAWSPolicy('package');
            $response['package'] = ['bucket' => $saveResults->getBucket('package'),
                'awsKey' => $saveResults->getAWSKey(),
                'awsPolicy' => $packagePolicy,
                'awsSignature' => $saveResults->generateAWSSignature($packagePolicy),
                'bucketKey' => $saveResults->getBucketKey('package'),
                'newFilename' => $saveResults->generateFilename($saveResults->packagePrefix . $saveResults->id)];
            $response['entry']['preview_type'] = $saveResults->category->preview_type;

            return Response::json($response, 200);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->product->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    // public function show($id)
    // {
    // 	$product = $this->product->find($id);

    // 	if(is_null($product))
    // 	{
    // 		return Redirect::route('mabackend.products.index');
    // 	}

    // 	return View::make('admin.products.show', compact('product'));
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $response = [];
        $inputs = array_except(Request::all(), '_method');

        $updateResults = $this->product->update($id, $inputs);

        if ($updateResults) {
            $response = $updateResults->toArray();
            $response['downloads'] = $updateResults->downloads()->count();
            $response['seller'] = $updateResults->seller()->first()->toArray();
            $response['category'] = $updateResults->category()->first()->toArray();
            $response['sub_categories'] = $updateResults->subCategories()->get()->toArray();
            $response['compressions'] = $updateResults->compressions()->get()->toArray();
            $response['formats'] = $updateResults->formats()->get()->toArray();
            $response['resolutions'] = $updateResults->resolutions()->get()->toArray();
            $response['versions'] = $updateResults->versions()->get()->toArray();
            $response['bpms'] = $updateResults->bpms()->get()->toArray();
            $response['fpss'] = $updateResults->fpss()->get()->toArray();
            $response['sample_rates'] = $updateResults->sampleRates()->get()->toArray();
            $response['plugins'] = $updateResults->plugins()->get()->toArray();
            $response['music'] = $updateResults->music()->get()->toArray();
            $response['tags'] = $updateResults->tags()->get()->toArray();
            $response['previews'] = $updateResults->activePreviewFiles();

            $preview_type = $response['category']['preview_type'];
            $previewPolicy = $updateResults->generateAWSPolicy($preview_type);
            $response['preview'] = ['bucket' => $updateResults->getBucket($preview_type),
                'awsKey' => $updateResults->getAWSKey(),
                'awsPolicy' => $previewPolicy,
                'awsSignature' => $updateResults->generateAWSSignature($previewPolicy),
                'bucketKey' => $updateResults->getBucketKey($preview_type),
                'newFilename' => $updateResults->generateFilename($updateResults->previewPrefix . $updateResults->id)];

            $packagePolicy = $updateResults->generateAWSPolicy('package');
            $response['package'] = ['bucket' => $updateResults->getBucket('package'),
                'awsKey' => $updateResults->getAWSKey(),
                'awsPolicy' => $packagePolicy,
                'awsSignature' => $updateResults->generateAWSSignature($packagePolicy),
                'bucketKey' => $updateResults->getBucketKey('package'),
                'newFilename' => $updateResults->generateFilename($updateResults->packagePrefix . $updateResults->id)];
            $response['entry']['preview_type'] = $updateResults->category->preview_type;

            return Response::json($response, 200);
        }

        $response['state'] = "error";
        $response['errors'] = json_decode($this->product->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }

    /**
     * weekly  products
     */
    function weeklyProducts()
    {
        $products = $this->product->weeklyProducts();

        return View('admin.automate-newsletters.new-products', compact('products'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $product = $this->product->findById($id);

        $this->submissionRepo->delete($product->submission);

        return Response::json("Product $id deleted successfully.", 200);
    }


    /**
     * Used for `/mabackend/products/{productId}/download` endpoint.
     *
     * We don't want to create `\MotionArray\Models\Download` record for admin's downloads.
     * There is another endpoint (`/account/download/{productId}`), that creates `\MotionArray\Models\Download` record for users.
     *
     * Downloads -that has been made with this endpoint-, will not be included when calculating user payouts.
     *
     * @param $productId
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadProductForReview($productId)
    {
        $product = $this->product->find($productId);
        if (!$product) {
            \App::abort(404);
        }

        $this->authorize(ProductPolicy::downloadPackage, $product);

        /** @var User $user */
        $user = \Auth::user();

        $useCdn = app(PackageCdnChecker::class)->shouldUseCDN($user);

        $downloadUrl = $this->product->getDownloadUrl($product, $user, $useCdn);

        return \Redirect::to($downloadUrl);
    }

}
