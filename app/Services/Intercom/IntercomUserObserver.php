<?php

namespace MotionArray\Services\Intercom;

use Illuminate\Support\Facades\Bus;
use MotionArray\Jobs\DeleteUserOnIntercom;
use MotionArray\Jobs\UpdateUserOnIntercom;
use MotionArray\Models\User;

class IntercomUserObserver
{
    public function created(User $user)
    {
        Bus::dispatch(new UpdateUserOnIntercom($user, true, true));
    }

    public function updated(User $user)
    {
        Bus::dispatch(new UpdateUserOnIntercom($user, true, true));
    }

    public function deleted(User $user)
    {
        Bus::dispatch(new DeleteUserOnIntercom($user));
    }

    public function restored(User $user)
    {
        Bus::dispatch(new UpdateUserOnIntercom($user));
    }
}
