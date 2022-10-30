<?php

namespace MotionArray\Services\Product;

use Illuminate\Support\Facades\Gate;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use MotionArray\Policies\ProductPolicy;
use MotionArray\Repositories\DownloadRepository;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Repositories\UserRepository;
use MotionArray\Services\Cdn\PackageCdnChecker;
use MotionArray\Services\Laravel\Auth\Access\Response;

class PackageDownloadService
{
    /**
     * @var PackageCdnChecker
     */
    private $packageCdnChecker;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var DownloadRepository
     */
    private $downloadRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        PackageCdnChecker $packageCdnChecker,
        ProductRepository $productRepository,
        DownloadRepository $downloadRepository,
        UserRepository $userRepository
    )
    {
        $this->packageCdnChecker = $packageCdnChecker;
        $this->productRepository = $productRepository;
        $this->downloadRepository = $downloadRepository;
        $this->userRepository = $userRepository;
    }

    public function getUrlAndStoreDownloadIfAuthorized(User $user, Product $product): ?string
    {
        if ($this->authorization($user, $product)->denied()) {
            return null;
        }

        return $this->getUrlAndStoreDownload($user, $product);
    }

    public function getUrlAndStoreDownload(User $user, Product $product): string
    {
        if (!$user->isAdmin()) {
            $this->downloadRepository->recordDownload($user, $product);
        }

        return $this->getUrl($user, $product);
    }

    public function getUrl(User $user, Product $product): string
    {
        $useCdn = $this->packageCdnChecker->shouldUseCDN($user);
        return $this->productRepository->getDownloadUrl($product, $user, $useCdn);
    }

    public function authorization(User $user, Product $product): Response
    {
        return Gate::forUser($user)->authorizeResult(ProductPolicy::downloadPackage, $product);
    }
}
