<?php

namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\UserSite;
use MotionArray\Services\UserSite\UserSiteStatusCheckService;

class UserSiteStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray:user-site-status-check 
        {--user-site-id= : Filter by user_site_id } 
        {--domain= : Filter by domain }   
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether DNS for user sites are set up correctly by making a request to an endpoint on their portfolio site';

    /**
     * @var UserSiteStatusCheckService
     */
    protected $userSiteStatusCheckService;

    /**
     * Create a new command instance.
     *
     * @param UserSiteStatusCheckService $userSiteStatusCheckService
     * @return void
     */
    public function __construct(UserSiteStatusCheckService $userSiteStatusCheckService)
    {
        parent::__construct();

        $this->userSiteStatusCheckService = $userSiteStatusCheckService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userSiteId =$this->option('user-site-id');
        $domain = $this->option('domain');

        $userSitesQueryBuilder = UserSite::query()
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->whereUseDomain('true');

        if (!empty($userSiteId)) {
            $userSitesQueryBuilder->whereUserSiteId($userSiteId);
        }
        if (!empty($domain)) {
            $userSitesQueryBuilder->whereDomain($domain);
        }

        $userSitesQueryBuilder->chunk(10, function ($userSites) {
            foreach ($userSites as $userSite) {
                $this->line("{$userSite->id} - {$userSite->domain}");
                $this->userSiteStatusCheckService->checkUserSiteDomains($userSite);
            }
        });
    }
}
