<?php

namespace MotionArray\Http\Controllers\API;

use MotionArray\Repositories\PortfolioRepository;
use MotionArray\Repositories\PortfolioUploadRepository;

/**
 * Class PortfolioUploadsController
 *
 * @package MotionArray\Http\Controllers\API
 */
class PortfolioUploadsController extends BaseController
{
    /**
     * @var \MotionArray\Repositories\PortfolioRepository
     */
    protected $portfolio;

    /**
     * @var \MotionArray\Repositories\PortfolioUploadRepository
     */
    protected $portfolioUpload;

    /**
     * PortfolioUploadsController constructor.
     *
     * @param \MotionArray\Repositories\PortfolioRepository $portfolio
     * @param \MotionArray\Repositories\PortfolioUploadRepository $portfolioUpload
     */
    public function __construct(
        PortfolioRepository $portfolio,
        PortfolioUploadRepository $portfolioUpload
    )
    {
        $this->portfolio = $portfolio;
        $this->portfolioUpload = $portfolioUpload;
    }

    /**
     * Get Portfolio Uploads
     *
     * @param $portfolioId
     * @return mixed
     */
    public function index($portfolioId)
    {
        $portfolio = $this->portfolio->findById($portfolioId);

        $portfolioUploads = $this->portfolioUpload->findByPortfolio($portfolio);

        return $portfolioUploads;
    }

    /**
     * Remove Portfolio Upload
     *
     * @param $portfolioId
     * @param $portfolioUploadId
     * @return mixed
     */
    public function delete($portfolioId, $portfolioUploadId)
    {
        $portfolioUpload = $this->portfolioUpload->findById($portfolioUploadId);

        if ($portfolioUpload) {
            $portfolioUpload->delete();
        }

        return response()->json(['success' => 'true']);
    }

    /**
     * Download Portfolio Upload
     *
     * @param $portfolioId
     * @param $portfolioUploadId
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($portfolioId, $portfolioUploadId)
    {
        $portfolioUpload = $this->portfolioUpload->findById($portfolioUploadId);

        if (!$portfolioUpload) {
            return response()->json(['success' => false]);
        }

        $url = $portfolioUpload->getDownloadUrl();

        return redirect()->to($url);
    }
}
