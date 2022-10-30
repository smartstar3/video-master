<?php

namespace MotionArray\Services\UserSite;

use MotionArray\Models\UserSite;
use MotionArray\Repositories\UserSiteStatusCheckRepository;

class UserSiteStatusCheckService
{
    /**
     * @var UserSiteStatusCheckRepository
     */
    protected $userSiteStatusCheckRepository;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $statusCheckPath = '/status-check';

    /**
     * @param UserSiteStatusCheckRepository $userSiteStatusCheckRepository
     * @param \GuzzleHttp\Client $httpClient
     */
    public function __construct(UserSiteStatusCheckRepository $userSiteStatusCheckRepository, \GuzzleHttp\Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->userSiteStatusCheckRepository = $userSiteStatusCheckRepository;
    }

    public function checkUserSiteDomains(UserSite $userSite): void
    {
        $domain = $this->cleanDomain($userSite->domain);
        $wwwDomain = "www.{$domain}";

        $userSiteStatusCheckAttributes = [];
        $userSiteStatusCheckAttributes['user_site_id'] = $userSite->id;

        $userSiteStatusCheckAttributes['domain'] = $domain;
        $userSiteStatusCheckAttributes['is_success'] = $this->checkDomain($domain);
        $this->userSiteStatusCheckRepository->updateOrCreate($userSiteStatusCheckAttributes);

        $userSiteStatusCheckAttributes['domain'] = $wwwDomain;
        $userSiteStatusCheckAttributes['is_success'] = $this->checkDomain($wwwDomain);
        $this->userSiteStatusCheckRepository->updateOrCreate($userSiteStatusCheckAttributes);
    }

    private function checkDomain(string $domain): bool
    {
        $statusCheckUrl = $domain . $this->statusCheckPath;
        try {
            $response = $this->httpClient->get($statusCheckUrl, ['timeout' => 5, 'connection_timeout' => 5]);
            $responseCode = $response->getStatusCode();
            $responseContent = $response->getBody()->getContents();
            $responseObject = json_decode($responseContent);
            if ($responseCode == 200 && $responseObject->status === 'ok') {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    private function cleanDomain(string $domain): string
    {
        // Remove 'http://' or 'https://' from the domain string.
        $domain = preg_replace("(^https?://)", "", $domain);

        // Remove 'www.' from the domain string.
        $domain = preg_replace('#^www\.(.+\.)#i', '$1', $domain);

        // Remove trailing slashes from the domain string.
        $domain = trim($domain, '/');

        return $domain;
    }
}
