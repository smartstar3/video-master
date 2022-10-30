<?php

namespace MotionArray\Policies;

use Illuminate\Auth\Access\AuthorizationException;
use MotionArray\Models\Product;
use MotionArray\Models\User;
use MotionArray\Policies\ProductPolicy\AlreadyDownloadingAuthorizationException;
use MotionArray\Policies\ProductPolicy\NotAPayingMemberAuthorizationException;
use MotionArray\Policies\ProductPolicy\OverDownloadRateLimitAuthorizationException;
use MotionArray\Policies\ProductPolicy\ProductDeletedAuthorizationException;
use MotionArray\Policies\ProductPolicy\NotAConfirmedMemberAuthorizationException;
use MotionArray\Policies\ProductPolicy\ProductUnpublishedAuthorizationException;
use MotionArray\Repositories\DownloadRepository;

class ProductPolicy
{
    public const downloadPackage = 'downloadPackage';
    public const downloadWithoutCaptcha = 'downloadWithoutCaptcha';

    /**
     * @var DownloadRepository
     */
    private $downloadRepo;

    public function __construct(DownloadRepository $downloadRepo)
    {
        $this->downloadRepo = $downloadRepo;
    }

    /**
     * Runs before any method in this policy.
     * If returns true, requested method won't be run.
     * If returns void, requested method will run.
     *
     * @param User $user
     * @param $ability
     *
     * @return bool|void
     */
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * @param User    $user
     * @param Product $product
     *
     * @return bool
     *
     * @throws AuthorizationException
     */
    public function downloadPackage(User $user, Product $product): bool
    {
        if ($product->seller_id == $user->id) {
            return true;
        }

        if ($product->trashed()) {
            throw new ProductDeletedAuthorizationException();
        }

        if (!$user->isConfirmed()) {
            throw new NotAConfirmedMemberAuthorizationException();
        }

        if (!$product->isPublished()) {
            throw new ProductUnpublishedAuthorizationException();
        }

        if ($product->free) {
            return true;
        }

        if ($this->downloadRepo->checkOverDownloadRateLimit($user)) {
            throw new OverDownloadRateLimitAuthorizationException();
        }

        if ($this->downloadRepo->isDownloading($user, $product)) {
            throw new AlreadyDownloadingAuthorizationException();
        }

        if (!$user->isPayingMember()) {
            throw new NotAPayingMemberAuthorizationException();
        }

        return true;
    }

    /**
     * If user is admin or products seller; we don't need to validate captcha.
     * Otherwise, users must provide captcha token.
     *
     * @param User $user
     * @param Product $product
     *
     * @return bool
     */
    public function downloadWithoutCaptcha(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id == $product->seller_id) {
            return true;
        }

        return false;
    }
}
