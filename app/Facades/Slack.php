<?php namespace MotionArray\Facades;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Maknz\Slack\Client;

class Slack extends Facade
{
    protected static function getFacadeAccessor()
    {
        $url = Config::get('services.slack.webhook');

        $client = new Client($url);

        return $client;
    }
}