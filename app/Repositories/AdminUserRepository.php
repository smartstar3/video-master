<?php

namespace MotionArray\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MotionArray\Events\UserEvent\Admin\UserCreated;
use MotionArray\Events\UserEvent\Admin\UserDeleted;
use MotionArray\Events\UserEvent\Admin\UserDisabled;
use MotionArray\Events\UserEvent\Admin\UserEnabled;
use MotionArray\Events\UserEvent\Admin\UserForceLogoutDisabled;
use MotionArray\Events\UserEvent\Admin\UserForceLogoutEnabled;
use MotionArray\Events\UserEvent\Admin\UserFreeloaderUpdated;
use MotionArray\Events\UserEvent\Admin\UserLoginAs;
use MotionArray\Events\UserEvent\Admin\UserPasswordUpdated;
use MotionArray\Events\UserEvent\Admin\UserRoleChanged;
use MotionArray\Events\UserEvent\Admin\UserUpdated;
use MotionArray\Events\UserEvent\UserDisabledByReachingDownloadLimit;
use MotionArray\Events\UserEvent\Admin\UserDowngraded;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\User;
use MotionArray\Support\UserEvents\UserEventLogger;

class AdminUserRepository
{
    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * @var UserEventLogger
     */
    protected $logger;
    /**
     * @var UserSubscriptionRepository
     */
    private $subscriptionRepo;

    public function __construct(
        UserRepository $userRepo,
        UserSubscriptionRepository $subscriptionRepo,
        UserEventLogger $logger
    )
    {
        $this->userRepo = $userRepo;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->logger = $logger;
    }

    public function setRoles($userId, array $roleIds)
    {
        $user = $this->userRepo->setRoles($userId, $roleIds);
        $freePlanRoleIds = [
            Roles::SELLER_ID,
            Roles::FREELOADER_ID,
        ];

        // existing behavior only checks first index
        if (in_array($roleIds[0], $freePlanRoleIds)) {
            $this->userRepo->cancelSubscription($user);
        }
        $this->logger->log(new UserRoleChanged($userId, $roleIds));
        return $user;
    }

    public function setEnabled($userId)
    {
        $user = $this->userRepo->setEnabled($userId);
        $this->logger->log(new UserEnabled($userId, Auth::user()));
        return $user;
    }

    public function setDisabled($userId)
    {
        $user = $this->userRepo->setDisabled($userId);
        $this->logger->log(new UserDisabled($userId, Auth::user()));
        return $user;
    }

    public function setForceLogOut($userId)
    {
        $user = $this->userRepo->findById($userId);
        $this->userRepo->setForceLogOut($user);
        $this->logger->log(new UserForceLogoutEnabled($userId, Auth::user()));
        return $user;
    }

    public function cancelForceLogOut($userId)
    {
        $user = $this->userRepo->findById($userId);
        $this->userRepo->cancelForceLogOut($user);
        $this->logger->log(new UserForceLogoutDisabled($userId, Auth::user()));
        return $user;
    }

    public function updateFreeloader($userId, $attributes)
    {
        $user = $this->userRepo->setRoles($userId, [Roles::FREELOADER_ID]);
        $this->userRepo->cancelSubscription($user);

        $attributes = array_only($attributes, [
            'plan_id',
            'access_starts_at',
            'access_expires_at'
        ]);

        $user = $this->userRepo->update($userId, $attributes);
        $this->logger->log(new UserFreeloaderUpdated($userId, $attributes));
        return $user;
    }

    public function delete($userId)
    {
        $result = $this->userRepo->delete($userId);
        $this->logger->log(new UserDeleted($userId, Auth::user()));
        return $result;
    }

    public function loginAs($userId)
    {
        $this->logger->log(new UserLoginAs($userId, Auth::user()));
    }

    public function setPassword($userId, $password)
    {
        $user = $this->userRepo->setPassword($userId, $password);
        $this->logger->log(new UserPasswordUpdated($userId, Auth::user()));
        return $user;
    }

    public function update($userId, $attributes)
    {
        $user = $this->userRepo->update($userId, $attributes);
        $attributes = Arr::except($attributes, ['password', 'password_confirmation']);
        $this->logger->log(new UserUpdated($userId, $attributes, Auth::user()));
        return $user;
    }

    public function make(array $attributes, $roleId)
    {
        $user = $this->userRepo->make($attributes, [$roleId]);
        $attributes = Arr::except($attributes, ['password', 'password_confirmation']);
        $attributes['role_id'] = $roleId;
        $this->logger->log(new UserCreated($user->id, $attributes, Auth::user()));

        return $user;
    }

    public function downgrade($userId)
    {
        $user = $this->userRepo->downgrade($userId);
        $this->logger->log(new UserDowngraded($userId, Auth::user()));
        return $user;
    }

    public function setDisabledByReachingDownloadLimit(User $user)
    {
        $user->disabled = 1;
        $user->save();

        $this->logger->log(new UserDisabledByReachingDownloadLimit($user->id));
        return $user;
    }
}
