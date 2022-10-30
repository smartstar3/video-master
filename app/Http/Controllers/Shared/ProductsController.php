<?php

namespace MotionArray\Http\Controllers\Shared;

use MotionArray\Http\Controllers\Controller;
use MotionArray\Services\Submission\SubmissionService;
use MotionArray\Repositories\Products\ProductRepository;
use Response;

class ProductsController extends BaseController
{
    /**
     * @var SubmissionService
     */
    protected $submissionService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        SubmissionService $submissionService,
        ProductRepository $productRepository
    )
    {
        $this->submissionService = $submissionService;
        $this->productRepository = $productRepository;
    }

    public function show($id)
    {
        $product = $this->productRepository->findById($id);
        $prepareProduct = $this->submissionService->prepareProductJson($product);

        return Response::json($prepareProduct);
    }
}
