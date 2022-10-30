<?php

namespace MotionArray\Services\Product;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\User;
use MotionArray\Policies\ModelReleasePolicy;
use MotionArray\Repositories\ModelReleaseRepository;
use MotionArray\Services\Laravel\Auth\Access\Response;

class ModelReleaseService
{
    /**
     * @var ModelReleaseRepository
     */
    private $modelReleaseRepository;

    /**
     * ModelReleaseService constructor.
     * @param ModelReleaseRepository $modelReleaseRepository
     */
    public function __construct(ModelReleaseRepository $modelReleaseRepository)
    {
        $this->modelReleaseRepository = $modelReleaseRepository;
    }

    /**
     * @param User $user
     * @param ModelRelease $modelRelease
     * @return string
     */
    public function getUrlIfAuthorized(User $user, ModelRelease $modelRelease): ?string
    {
        $authorization = Gate::forUser($user)->authorizeResult(ModelReleasePolicy::downloadModelRelease, $modelRelease);
        if ($authorization->denied()) {
            throw new UnauthorizedException();
        }

        return $this->getUrl($user, $modelRelease);
    }

    /**
     * @param User $user
     * @param ModelRelease $modelRelease
     * @return string
     */
    public function getUrl(User $user, ModelRelease $modelRelease): string
    {
        return $this->modelReleaseRepository->getDownloadUrl($modelRelease);
    }

    /**
     * @param User $user
     * @param ModelRelease $modelRelease
     * @return string
     */
    public function delete(ModelRelease $modelRelease): bool
    {
        return $this->modelReleaseRepository->delete($modelRelease);
    }

    /**
     * @param User $user
     * @param ModelRelease $modelRelease
     * @throws UnauthorizedException
     * @return string
     */
    public function deleteIfAuthorized(User $user, ModelRelease $modelRelease): bool
    {
        $authorization = Gate::forUser($user)->authorizeResult(ModelReleasePolicy::deletedModelRelease, $modelRelease);
        if ($authorization->denied()) {
            throw new UnauthorizedException();
        }

        return $this->delete($modelRelease);
    }
}

