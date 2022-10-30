<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Models\ModelRelease;
use MotionArray\Models\Submission;
use MotionArray\Models\User;
use MotionArray\Repositories\SubmissionRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\Category;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use View;
use Auth;
use Request;

class SubmissionsController extends BaseController
{

    /**
     * @var ProductRepository
     */
    protected $productRepo;

    /**
     * @var SubmissionRepository
     */
    protected $submissionRepo;

    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    public function __construct(
        ProductRepository $productRepository,
        SubmissionRepository $submissionRepository,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper
    )
    {
        $this->productRepo = $productRepository;
        $this->submissionRepo = $submissionRepository;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
    }

    public function index()
    {
        $seller = Auth::user();

        $statuses = $this->submissionRepo->getSubmissionStatuses();

        $status = Request::get('status');

        $orderDirection = Request::get('order_direction');

        $requestId = Request::get('request');

        $request = null;

        if ($requestId) {
            $request = \MotionArray\Models\Request::find($requestId);
        }

        if (!$orderDirection) {
            $orderDirection = 'desc';
        }

        $submissions = $this->submissionRepo->getSubmissionsBySeller(
            $seller,
            $status,
            Request::get('category'),
            Request::get('order_by'),
            $orderDirection
        );

        // Add AWS credentials.
        foreach ($submissions as $key => $submission) {
            $submission->product->aws = $submission->product->getAwsAttribute();
            $submission->product->model_releases = ModelRelease::whereProductId($submission->product->id)->get();
        }

        $categories = $this->categoriesWithRelationsHelper->categoriesWithSubCategories($seller);

        $product_specs = $this->productRepo->allSpecs($seller);

        $assoc_music = $this->productRepo->getStockMusic();

        return View::make('site.account.seller-submissions', compact('request', 'statuses', 'categories', 'submissions', 'product_specs', 'assoc_music'));
    }

    public function pendingInfo()
    {
        /** @var User $seller */
        $seller = Auth::user();
        $pendingSubmissionsCount = Submission::whereSellerId($seller->id)->pending()->count();

        return response()->json([
            'submissions_pending' => $pendingSubmissionsCount,
            'submission_limit' => $seller->getSubmissionLimit(),
            'submissions_remaining' => ($seller->getSubmissionLimit() - $pendingSubmissionsCount)
        ]);
    }

}
