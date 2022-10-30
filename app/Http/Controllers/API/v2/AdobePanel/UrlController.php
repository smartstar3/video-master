<?php

namespace MotionArray\Http\Controllers\API\v2\AdobePanel;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use MotionArray\Services\AdobePanel\AdobePanelUrlService;
use Auth;

class UrlController extends Controller
{
    /**
     * @var AdobePanelUrlService
     */
    private $urlService;

    public function __construct(AdobePanelUrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function siteUrls()
    {
        $appUrl = config('app.url');

        $adobeUrls = [
            'register' => "{$appUrl}/pricing",
            'details' => "{$appUrl}/account/details",
            'collections' => "{$appUrl}/account/collections",
            'upgrade' => "{$appUrl}/account/upgrade",
            'contact' => "{$appUrl}/contact",
            'terms_of_service' => "{$appUrl}/terms-of-service"
        ];

        return new JsonResponse($adobeUrls);
    }

    public function signedUrls()
    {
        $user = Auth::user();

        $urlNames = [
            'details',
            'upgrade',
            'collections',
            'requests'
        ];
        $routeNames = [];
        foreach ($urlNames as $urlName) {
            $routeName = "signed-{$urlName}";
            $routeNames[$urlName] = $routeName;
        }
        $data = $this->urlService->signedUrls($user, $routeNames);

        return new JsonResponse($data);
    }
}
