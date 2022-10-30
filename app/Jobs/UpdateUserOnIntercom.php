<?php

namespace MotionArray\Jobs;

use MotionArray\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;
use MotionArray\Services\Intercom\IntercomService;

class UpdateUserOnIntercom extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $user;

    protected $newUser;

    protected $includeStats;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $newUser = false, $includeStats = false)
    {
        $this->user = $user;

        $this->newUser = $newUser;

        $this->includeStats = $includeStats;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserRepository $userRepo, IntercomService $intercomRepo)
    {
        if ($this->user->intercom_id || $this->newUser) {
            $userData = $userRepo->getIntercomData($this->user, $this->includeStats);

            $intercomUser = $intercomRepo->createUser($userData);

            if (!$this->user->intercom_id && $intercomUser) {
                $this->user->intercom_id = $intercomUser->id;

                $this->user->save();
            }
        }
    }
}
