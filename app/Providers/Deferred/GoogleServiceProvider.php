<?php

namespace MotionArray\Providers\Deferred;

use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\ServiceProvider;
use MotionArray\Models\YoutubeAccessToken;

class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerClient();

        $this->registerYouTube();
    }

    /**
     * Register the Google Client implementation.
     */
    protected function registerClient()
    {
        $this->app->singleton(Google_Client::class, function ($app) {
            $config = $app['config']['services.google'];
            $client = new Google_Client();

            $client->setAccessType('offline');
            $client->setApplicationName('Motion Array');
            $client->setClientId($config['key']);
            $client->setClientSecret($config['secret']);
            $client->setScopes('https://www.googleapis.com/auth/youtube');
            $client->setAccessToken(json_encode($this->getLatestAccessTokenFromDB()));

            if ($client->isAccessTokenExpired()) {
                $this->refreshToken($client);
            }

            return $client;
        });
    }

    /**
     * Register the YouTube implementation.
     */
    protected function registerYouTube()
    {
        $this->app->singleton(Google_Service_YouTube::class, function () {
            $youtube = new Google_Service_YouTube(app(Google_Client::class));

            return $youtube;
        });

        $this->app->alias(Google_Service_YouTube::class, 'youtube');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Google_Client::class, Google_Service_YouTube::class, 'youtube'];
    }

    /**
     * Returns the last saved access token.
     *
     * @return mixed
     */
    private function getLatestAccessTokenFromDB()
    {
        return $token = YoutubeAccessToken::orderBy('created_at', 'desc')->first();
    }

    /**
     * Refresh token.
     *
     * @param Google_Client $client
     */
    private function refreshToken(Google_Client $client)
    {
        $currentToken = json_decode($client->getAccessToken(), true);

        $client->refreshToken($currentToken['refresh_token']);

        YoutubeAccessToken::create(json_decode($client->getAccessToken(), true));
    }
}
