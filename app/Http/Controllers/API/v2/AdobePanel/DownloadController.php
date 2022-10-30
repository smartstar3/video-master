<?php

namespace MotionArray\Http\Controllers\API\v2\AdobePanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Services\AdobePanel\AdobePanelService;
use MotionArray\Services\Product\PackageDownloadService;
use MotionArray\Http\Requests\AdobePanel\UserDownloadsRequest;
use Auth;

class DownloadController extends Controller
{
    /**
     * @var ProductRepository
     */
    protected $productRepo;

    /**
     * @var PackageDownloadService
     */
    protected $packageDownloadService;

    /**
     * @var AdobePanelService
     */
    private $adobePanelService;

    public function __construct(
        ProductRepository $productRepo,
        PackageDownloadService $packageDownloadService,
        AdobePanelService $adobePanelService
    )
    {
        $this->productRepo = $productRepo;
        $this->packageDownloadService = $packageDownloadService;
        $this->adobePanelService = $adobePanelService;
    }

    /**
     * This returns products which user downloaded.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userDownloads(UserDownloadsRequest $request)
    {
        $page = $request->json('page');
        $perPage = $request->json('perPage');

        return new JsonResponse($this->adobePanelService->userDownloads(Auth::user(), $page, $perPage));
    }

    /**
     * This returns a url to download a product and check if user is authorized
     *
     * @param $id
     * @return JsonResponse
     */
    public function downloadUrl($id)
    {
        $product = $this->productRepo->findById($id);
        if ($product === null) {
            return new JsonResponse([
                'message' => 'Resource not found',
            ], 404);
        }

        $user = Auth::user();

        $authResult = $this->packageDownloadService->authorization($user, $product);

        if ($authResult->denied()) {
            return new JsonResponse([
                'message' => $authResult->message(),
            ], 400);
        }

        $signedUrl = $this->packageDownloadService->getUrlAndStoreDownload($user, $product);
        if ($signedUrl === null) {
            return new JsonResponse([
                'message' => "Download couldn't be initiated",
            ], 400);
        }

        return new JsonResponse([
            'url' => $signedUrl,
        ]);
    }
}
