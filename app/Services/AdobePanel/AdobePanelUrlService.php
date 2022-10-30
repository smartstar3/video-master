<?php

namespace MotionArray\Services\AdobePanel;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use MotionArray\Models\User;

class AdobePanelUrlService
{
    public function signedUrls(User $user, array $routeNames)
    {
        $signedUrlTimeToLive = 10;
        $expireDate = Carbon::now()->addMinutes($signedUrlTimeToLive);
        $signedUrls = [];

        foreach ($routeNames as $key => $routeName) {
            $url = $this->createSignedUrl($routeName, $expireDate, ['user' => $user->id]);
            $signedUrls[$key] = $url;
        }

        $data = [
            'expireDate' => $expireDate,
            'urls' => $signedUrls
        ];

        return $data;
    }

    protected function createSignedUrl(string $routeName, Carbon $expireDate, array $arguments)
    {
        $appUrl = config('app.url');
        URL::forceRootUrl($appUrl);
        $url = URL::temporarySignedRoute($routeName, $expireDate, $arguments);
        URL::forceRootUrl(null);

        return $url;
    }
}
