<?php

namespace MotionArray\Policies;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;
use MotionArray\Models\ModelRelease;
use MotionArray\Models\StaticData\SubmissionStatuses;
use MotionArray\Models\User;

class ModelReleasePolicy
{
    use HandlesAuthorization;

    public const downloadModelRelease = 'downloadModelRelease';
    public const deletedModelRelease = 'deletedModelRelease';

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
     * @param User $user
     * @param ModelRelease $modelRelease
     * @return bool
     * @throws AuthorizationException
     */
    public function downloadModelRelease(User $user, ModelRelease $modelRelease): bool
    {
        if ($modelRelease->product->seller_id != $user->id) {
            throw new AuthorizationException();
        }

        return true;
    }

    /**
     * @param User $user
     * @param ModelRelease $modelRelease
     * @return bool
     * @throws AuthorizationException
     */
    public function deletedModelRelease(User $user, ModelRelease $modelRelease): bool
    {
        if ($modelRelease->product->seller_id != $user->id) {
            throw new AuthorizationException();
        }

        // Users should not be able to delete model releases on products that are not editable
        if (in_array($modelRelease->product->submission->submission_status_id, [
            SubmissionStatuses::PENDING_ID,
            SubmissionStatuses::APPROVED_ID
        ])) {
            throw new AuthorizationException();
        }

        return true;
    }
}
