<?php namespace MotionArray\Facades;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use AlgoliaSearch\Client;

class Algolia extends Facade
{
    protected static function getFacadeAccessor()
    {
        $app_id = Config::get('algolia.app_id');
        $secret = Config::get('algolia.admin_key');

        $client = new Client($app_id, $secret);

        return $client;
    }
}