<?php namespace MotionArray\Http\Controllers\Site;

use Bugsnag\Report;
use Illuminate\Http\Request;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\PreviewFile;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use MotionArray\Policies\ProductPolicy;
use MotionArray\Repositories\DownloadRepository;
use MotionArray\Repositories\Products\ProductRepository;
use Response;
use MotionArray\Services\Product\PackageDownloadService;
use MotionArray\Services\Product\ModelReleaseService;
use ReCaptcha\ReCaptcha;
use Redirect;
use Auth;

class DownloadsController extends BaseController
{
    /**
     * @var ProductRepository
     */
    protected $product;

    /**
     * @var \MotionArray\Repositories\DownloadRepository
     */
    protected $download;

    /**
     * @var PackageDownloadService
     */
    private $packageDownloadService;

    /**
     * @var ModelReleaseService
     */
    private $modelReleaseService;

    public function __construct(
        ProductRepository $product,
        DownloadRepository $download,
        PackageDownloadService $packageDownloadService,
        ModelReleaseService $modelReleaseService
    ) {
        $this->product = $product;
        $this->download = $download;
        $this->packageDownloadService = $packageDownloadService;
        $this->modelReleaseService = $modelReleaseService;
    }

    /**
     * Download Product
     *
     * Used for: /account/download/{id}
     *
     * @param $productId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function download(Request $request, $productId)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return Redirect::back();
        }

        /** @var Product $product */
        $product = $this->product->findById($productId);
        if ($product === null) {
            return Redirect::back();
        }

        $signedUrl = $this->packageDownloadService->getUrlAndStoreDownloadIfAuthorized($user, $product);
        if (null === $signedUrl) {
            return Redirect::back();
        }

        return Redirect::to($signedUrl);
    }

    /**
     * Download ModelRelease
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadModelRelease($id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return Response::json(['message' => 'Unauthorized'], 401);
        }

        /** @var ModelRelease $modelRelease */
        $modelRelease = ModelRelease::whereId($id)->first();
        if ($modelRelease === null) {
            return Response::json(['message' => 'Model release not found'], 404);
        }

        $signedUrl = $this->modelReleaseService->getUrlIfAuthorized($user, $modelRelease);
        if (null === $signedUrl) {
            return Response::json(['message' => 'Could not retrieve download URL'], 500);
        }

        return Redirect::to($signedUrl);
    }

    /**
     * Used for: /api/download-preview-file/{previewFileId}
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadPreviewFile($id)
    {
        $file = PreviewFile::find($id);

        $url = $file->getDownloadUrl();

        return Redirect::to($url);
    }

    /**
     * Download Preview
     *
     * Used for: /browse/download/preview/{productId}
     *
     * Redirects user to product's signed url for preview file.
     *
     * @param $productId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadPreview($productId)
    {
        // Prefer MP3 previews.
        $url = $this->getPreviewUrl($productId, 'mp3');

        // Try mpeg audio
        if (!$url) {
            $url = $this->getPreviewUrl($productId, 'mpeg audio');
        }

        if ($url) {
            return Redirect::to($url);
        }

        return Redirect::back();
    }

    /**
     * Returns signed url for downloading product's preview file.
     *
     * @param int $productId
     * @param string $format preview_files.format (ogg, webm, mpeg4, jpg...)
     * @return mixed
     */
    private function getPreviewUrl($productId, $format)
    {
        /** @var Product $product */
        $product = $this->product->findById($productId);
        if ($product === null) {
            \App::abort(404);
        }

        return $product->getPreviewDownloadUrl($format);
    }
}
