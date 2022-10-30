<?php namespace MotionArray\Repositories;

use MotionArray\Models\User;
use MotionArray\Models\UserSite;
use MotionArray\Models\UserSiteApp;

abstract class UserSiteAppRepository extends EloquentBaseRepository
{
    public function createDefault(UserSite $userSite)
    {
        $app = $this->model->replicate();

        $app->site()->associate($userSite);

        $app->save();

        return $app;
    }

    public function findByUser(User $user)
    {
        return $this->model->whereHas('site', function ($q) use ($user) {
            $q->where(['user_id' => $user->id]);
        })->first();
    }

    public function findBySite(UserSite $userSite)
    {
        return $this->model->where(['user_site_id' => $userSite->id])->first();
    }

    public function findOrCreateBySite(UserSite $userSite)
    {
        $app = $this->findBySite($userSite);

        if (!$app) {
            $app = $this->createDefault($userSite);
        }

        return $app;
    }

    public function update(UserSiteApp $app, $data)
    {
        $app->update($data);

        return $app;
    }
}