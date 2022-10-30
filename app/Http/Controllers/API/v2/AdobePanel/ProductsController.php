<?php

namespace MotionArray\Http\Controllers\API\v2\AdobePanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MotionArray\Services\AdobePanel\AdobePanelService;
use MotionArray\Repositories\Products\ProductRepository;
use Response;

class ProductsController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var AdobePanelService
     */
    private $adobePanelService;

    public function __construct(
        ProductRepository $productRepository,
        AdobePanelService $adobePanelService
    )
    {
        $this->productRepository = $productRepository;
        $this->adobePanelService = $adobePanelService;
    }

    public function search(Request $request)
    {
        return $this->adobePanelService->searchProducts($request->all());
    }

    public function show($id)
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            return Response::json([
                'message' => 'Resource not found'
            ], 404);
        }

        $product = $this->adobePanelService->prepareProduct($product);

        return Response::json($product);
    }
}
