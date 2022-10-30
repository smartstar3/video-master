<?php

namespace MotionArray\Http\Controllers\Shared;

use MotionArray\Models\ModelRelease;
use MotionArray\Repositories\SubmissionRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\Category;
use MotionArray\Services\Submission\SubmissionService;
use Request;
use Response;
use Auth;
use App;

class SubmissionsController extends BaseController
{
    protected $submissionRepo;
    protected $productRepo;
    protected $category;

    /**
     * @var SubmissionService
     */
    protected $submissionService;

    public function __construct(
        ProductRepository $productRepository,
        SubmissionRepository $submissionRepository,
        Category $category,
        SubmissionService $submissionService
    )
    {
        $this->submissionRepo = $submissionRepository;
        $this->productRepo = $productRepository;
        $this->category = $category;
        $this->submissionService = $submissionService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if (!Auth::user()->confirmed) {
            $response = [
                'success' => false,
                'state' => 'error',
                'error' => 'Email is not confirmed'
            ];

            return Response::json($response, 400);
        }

        $inputs = array_except(Request::all(), '_method');
        $inputs['name'] = substr($inputs['name'], 0, 48); // limit name length to 48 characters.
        $inputs = $inputs + [
                'seller_id' => Auth::user()->id, // Assign to the authenticated user
                'product_status_id' => 3,  // Processing
                'credit_seller' => true,
                'owned_by_ma' => false
            ];

        $product = $this->productRepo->make($inputs);

        if (isset($inputs['request']) && isset($inputs['request']['id'])) {
            $product->requests()->attach($inputs['request']['id']);
        }

        if ($product) {
            // Create a submission
            $submission = $this->submissionRepo->create($product, Auth::user());

            // Reload submission with relations
            $submission = $this->submissionRepo->findById($submission->id);

            // Get the AWS data
            $submission->product->aws = $submission->product->getAwsAttribute();

            return Response::json($submission, 200);
        }

        $response['state'] = 'error';
        $response['errors'] = json_decode($this->productRepo->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id Product ID
     *
     * @return Response
     */
    public function update($id)
    {
        $response = [];
        $inputs = array_except(Request::all(), '_method');

        $inputs = $inputs + [
                'owned_by_ma' => false
            ];

        $updateResults = $this->productRepo->update($id, $inputs);

        if ($updateResults) {
            $response = $this->submissionService->prepareProductJson($updateResults);

            return Response::json($response, 200);
        }

        $response['state'] = 'error';
        $response['errors'] = json_decode($this->productRepo->errors);

        http_response_code(400);

        return Response::json($response, 400);
    }

    /**
     * Submit a product for review
     *
     * @param  Integer $id The submission ID.
     *
     * @return Response
     */
    public function submit($id)
    {
        $submission = $this->submissionRepo->findById($id);

        if ($submission) {
            $submission = $this->submissionRepo->submitForReview($submission);
            $submission->product->aws = $submission->product->getAwsAttribute();
            $submission->product->model_releases = ModelRelease::whereProductId($submission->product->id)->get();

            return Response::json($submission, 200);
        }

        return App::abort('404');
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

        $submission = $this->submissionRepo->findById($id);

        if ($submission) {
            // Already approved and live, can't just delete.
            if ($submission->status->status == "approved") {

                // Delete already requested by author, cancel the delete request
                if (is_null($submission->delete_requested_at)) {
                    if ($this->submissionRepo->requestDelete($submission))
                       return Response::json("Submission will be deleted in 30 days.", 200);
                    else
                        return Response::json("Cannot delete this submission yet.", 501);
                } else {
                    // Author is requesting a delete
                    if ($this->submissionRepo->cancelDeleteRequest($submission))
                       return Response::json("Submission deletion request cancelled.", 200);
                    else
                        return Response::json("Cannot cancel submission delete request.", 501);
                }
            }
            else {
                $this->submissionRepo->delete($submission);
                return Response::json("Submission $id deleted successfully.", 200);
            }
        }

        return App::abort('404');
    }
}
