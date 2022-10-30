<?php

namespace MotionArray\Services\Algolia;

use Closure;
use Illuminate\Contracts\Cache\Repository;

class AlgoliaCache
{
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $expireAfterMinutes;

    /**
     * @var Repository
     */
    protected $cache;

    public function __construct(bool $enabled, int $expireAfterMinutes, Repository $cache)
    {
        $this->enabled = $enabled;
        $this->expireAfterMinutes = $expireAfterMinutes;
        $this->cache = $cache;
    }

    public function rememberRequest(AlgoliaSearchRequest $request, Closure $callback)
    {
        if ($this->enabled) {
            $key = $this->toKey($request->attributes());
            return $this->cache->remember($key, $this->expireAfterMinutes, $callback);
        } else {
            return $callback();
        }
    }

    public function remember($key, Closure $callback)
    {
        if ($this->enabled) {
            return $this->cache->remember($key, $this->expireAfterMinutes, $callback);
        } else {
            return $callback();
        }
    }

    protected function toKey($request)
    {
        $key = serialize($request);
        return "algolia-" . md5($key);
    }
}
