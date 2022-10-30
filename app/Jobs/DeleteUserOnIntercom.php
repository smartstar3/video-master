<?php

namespace MotionArray\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use MotionArray\Models\User;
use MotionArray\Services\Intercom\IntercomService;
use MotionArray\Repositories\IntercomRepository;

class DeleteUserOnIntercom extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IntercomService $intercom)
    {
        if ($this->user->intercom_id) {
            $intercom->deleteUser($this->user->intercom_id);
        }
    }
}
