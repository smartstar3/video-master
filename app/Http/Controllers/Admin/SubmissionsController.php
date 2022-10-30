<?php

namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Models\ModelRelease;
use MotionArray\Repositories\SubmissionRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\MediaSender\HttpMediaSender;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use MotionArray\Services\Submission\SubmissionService;
use App;
use View;
use Auth;
use Request;
use Response;

class SubmissionsController extends BaseController
{
    /**
     * @var SubmissionRepository
     */
    private $submissionRepo;

    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var HttpMediaSender
     */
    private $mediaSender;

    /**
     * @var CategoriesWithRelationsHelper
     */
    protected $categoriesWithRelationsHelper;

    /**
     * @var SubmissionService
     */
    protected $submissionService;

    public function __construct(
        SubmissionRepository $submissionRepository,
        UserRepository $userRepository,
        ProductRepository $productRepository,
        HttpMediaSender $mediaSender,
        CategoriesWithRelationsHelper $categoriesWithRelationsHelper,
        SubmissionService $submissionService
    )
    {
        $this->submissionRepo = $submissionRepository;
        $this->userRepo = $userRepository;
        $this->productRepo = $productRepository;
        $this->mediaSender = $mediaSender;
        $this->categoriesWithRelationsHelper = $categoriesWithRelationsHelper;
        $this->submissionService = $submissionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $user = Auth::user();
        $all_categories = $this->categoriesWithRelationsHelper->categoriesWithSubCategories($user);
        $product_specs = $this->productRepo->allSpecs($user);
        $assoc_music = $this->productRepo->getStockMusic();
        $access_services = Auth::user()->accessServices;
        $all_statuses = $this->submissionRepo->getSubmissionStatuses();
        $status_id = Request::get('status');
        //Set statuses and categories according to user.
        $statuses = [];
        $categories = [];
        $categories_status = [];

        if (Auth::user()->hasRole(1)) {
            $statuses = $all_statuses;
            foreach ($all_categories as $category) {
                array_push($categories, $category);
            }
            foreach ($statuses as $status) {
                $categories_status[$status->id] = $categories;
            }
        } else {
            //Set statuses according to user.
            foreach ($all_statuses as $status) {
                if ($status->id == 3) {
                    foreach ($access_services as $access_service) {
                        if ($access_service->id > 4 && $access_service->id < 9) {
                            array_push($statuses, $status);
                            break;
                        }
                    }
                } else {
                    foreach ($access_services as $access_service) {
                        if ($access_service->id > 0 && $access_service->id < 5) {
                            array_push($statuses, $status);
                            break;
                        }
                    }
                }
            }
            $categories = $this->submissionService->categoriesByAccessServiceAndStatus($access_services, $status_id);
            // Get categories according to allowend status
            foreach ($statuses as $status) {
                $categories_status[$status->id] = $this->submissionService->categoriesByAccessServiceAndStatus($access_services, $status->id);
            }
        }

        //If there is no status set, default to pending-review
        // don't use for now
        // if (!$status_id && Auth::user()->hasRole(1))
        // {
        //     $status_id = $this->submissionRepo->findStatusByStatus('pending')->id;
        // }
        // Set $status to null if all statuses are requested
        if ($status_id === "all") {
            $status_id = null;
        }

        $free = Request::get("free") == 'on';
        $kickass = Request::get("kickass") == 'on';
        $soldtoma = Request::get("soldtoma") == 'on';

        $submissions = $this->submissionRepo->getSubmissions($status_id, Request::get('category'), $categories_status, Request::get('q'), $free, $kickass, $soldtoma);
        // Add AWS credentials.
        foreach ($submissions as $submission) {
            if ($submission->seller) {
                $submission->seller->append('isNewSeller');
            }
            if ($submission->product) {
                $submission->product->makeVisible(['weight']);
                $submission->product->aws = $submission->product->getAwsAttribute();
                $submission->product->model_releases = ModelRelease::whereProductId($submission->product->id)->get();
                $submission->product->append('request');
                $submission->product->append('excluded');
            }

            $submission->submission_reviewer = $this->submissionRepo->getReviewStatus($submission);
        }

        return View::make('admin.submissions.index', compact('statuses', 'categories', 'submissions', 'product_specs', 'assoc_music'));
    }

    /**
     * assign reviewer for this submission
     * @return response
     */
    public function assignReviewer($id)
    {
        // Find the submission
        $submission = $this->submissionRepo->findById($id);
        if ($submission) {
            $reviewer = $this->submissionRepo->assignReviewer($submission, Auth::user());

            // Return a successful response
            return Response::json($reviewer);
        }

        // Submission or Status not found
        return App::abort('404');
    }

    /**
     * remove reviewer who is assigned for this submission
     * @return response
     */
    public function removeReviewer($id)
    {
        // Find the submission
        $submission = $this->submissionRepo->findById($id);
        if ($submission) {
            $reviewer = $this->submissionRepo->removeReviewer($submission);

            // Return a successful response
            if ($reviewer) {
                return Response::json('success', 200);
            }
        }

        // Submission or Status not found
        return App::abort('404');
    }

    /**
     * Update the Submission's status.
     *
     * @param int $id
     *
     * @return Response
     */
    public function updateStatus($id)
    {
        // Find the submission
        $submission = $this->submissionRepo->findById($id);
        $status = Request::input('status');

        if ($submission) {
            // Update the status
            switch ($status) {
                case 'approved':
                    $this->submissionRepo->setApproved($submission, Auth::user(), [
                        'body_raw' => Request::input('body_raw'),
                        'is_kick_ass' => Request::input('is_kick_ass', false),
                        'weight' => Request::input('weight'),
                    ]);
                    break;
                case 'needs-work':
                    $this->submissionRepo->setNeedsWork($submission, Auth::user(), [
                        'body_raw' => Request::input('body_raw'),
                    ]);
                    break;
                case 'rejected':
                    $this->submissionRepo->setRejected($submission, Auth::user(), [
                        'body_raw' => Request::input('body_raw'),
                    ]);
                    $this->submissionRepo->delete($submission);
                    break;
            }

            // Return a successful response
            return Response::make('success', 200);
        }

        // Submission or Status not found
        return App::abort('404');
    }

    /**
     * Delete the Submission and its related Product
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $submission = $this->submissionRepo->findById($id);
        if (!$this->submissionRepo->delete($submission)) {
            return Response::json("Failed to delete product", 500);
        }

        return Response::json("Submission $id deleted successfully.", 200);
    }

    /**
     * Sends product to Vimeo/Youtube.
     *
     * @param int $id
     *
     * @return Response
     */
    public function sendToSocial($id)
    {
        $submission = $this->submissionRepo->findById($id);
        if (!$submission || !$submission->product) {
            return Response::json(['error' => 'Product not found']);
        }
        $product = $submission->product;
        $product = $this->mediaSender->send($product);

        return Response::json($product);
    }

    public function getOldAudio()
    {
        $user = Auth::user();
        $statuses = $this->submissionRepo->getSubmissionStatuses();
        $status = Request::get('status');
        // If there is no status set, default to pending-review
        if (!$status) {
            $status = $this->submissionRepo->findStatusByStatus('pending')->id;
        }
        // Set $status to null if all statuses are requested
        if ($status === "all") {
            $status = null;
        }
        //Get Categories
        $all_categories = $this->categoriesWithRelationsHelper->categoriesWithSubCategories($user);
        $categories = [];
        $categories_status = [];
        foreach ($all_categories as $category) {
            array_push($categories, $category);
        }
        foreach ($statuses as $status) {
            $categories_status[$status->id] = $categories;
        }
        $submissions = $this->submissionRepo->getSubmissions($status, Request::get('category'), $categories_status, Request::get('q'));
        // Add AWS credentials.
        foreach ($submissions as $submission) {
            if ($submission->seller) {
                $submission->seller->append('isNewSeller');
            }
            if ($submission->product) {
                $submission->product->aws = $submission->product->getAwsAttribute();
                $submission->product->append('request');
            }
        }
        $product_specs = $this->productRepo->allSpecs($user);
        $assoc_music = $this->productRepo->getStockMusic();
        $old_stock_products = $this->productRepo->oldAudio();

        return View::make('admin.submissions.index', compact('statuses', 'categories', 'submissions', 'product_specs', 'assoc_music', 'old_stock_products'));
    }
}
