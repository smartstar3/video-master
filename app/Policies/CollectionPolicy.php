<?php

namespace MotionArray\Policies;

use MotionArray\Models\Collection;
use MotionArray\Models\User;

class CollectionPolicy
{
    public const create = 'create';
    public const update = 'update';
    public const delete = 'delete';
    public const show = 'show';

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Collection $collection)
    {
        return $this->isOwner($user, $collection);
    }

    public function delete(User $user, Collection $collection)
    {
        return $this->isOwner($user, $collection);
    }

    public function show(User $user, Collection $collection)
    {
        return true;
    }

    protected function isOwner(User $user, Collection $collection): bool
    {
        return $user->id == $collection->user_id;
    }
}
