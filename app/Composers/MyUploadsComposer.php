<?php namespace MotionArray\Composers;

use Illuminate\Support\Facades\Auth;
use MotionArray\Helpers\Helpers;
use MotionArray\Repositories\UserRepository;
use MotionArray\Facades\Flash;

class MyUploadsComposer
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function compose($view)
    {
        $user = Auth::user();

        $projectsCount = $user->projects()->count();

        if ($user->portfolioTrialExpired) {
            Flash::info('Your portfolio trial has expired, please <a href="/account/upgrade" target="_blank">upgrade</a> your account to upload new video projects.', "locked");

            return;
        }

        if ($projectsCount) {
            $portfolioExpirationWarning = $user->present()->portfolioExpirationWarning();

            if ($portfolioExpirationWarning) {
                Flash::info($portfolioExpirationWarning, "locked");
            }
        }
    }
}
