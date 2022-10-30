<?php

namespace MotionArray\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MotionArray\Models\UserSite;
use MotionArray\Services\UserSite\UserSiteStatusCheckService;

class UserSiteStatusCheck extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var UserSite */
    private $userSite;

    /**
     * Create a new job instance.
     * @param $userSite
     */
    public function __construct(UserSite $userSite)
    {
        $this->userSite = $userSite;
    }

    /**
     * @param UserSiteStatusCheckService $userSiteStatusCheckService
     */
    public function handle(UserSiteStatusCheckService $userSiteStatusCheckService)
    {
        $userSiteStatusCheckService->checkUserSiteDomains($this->userSite);
    }
}
