<?php

namespace MotionArray\Providers\Deferred;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use MotionArray\Services\Algolia\AlgoliaCache;

class AlgoliaServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->bind(AlgoliaCache::class, function () {
            $enabled = config('algolia.server_cache.enabled');
            $expireAfterMinutes = config('algolia.server_cache.expire_after_minutes');
            $cache = Cache::store();
            return new AlgoliaCache($enabled, $expireAfterMinutes, $cache);
        });
    }

    public function provides()
    {
        return [AlgoliaCache::class];
    }
}
