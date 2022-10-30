<?php

namespace MotionArray\Services\Cdn;

use Config;
use Illuminate\Http\Request;
use MotionArray\Services\GeoIpReader;

/**
 * Should be registered as a singleton to cache results for repeat checks
 */
class PreviewCdnChecker
{
    protected $useCdn = false;
    protected $excludedCountries = [];
    protected $shouldUse = null;

    /**
     * @var GeoIpReader
     */
    protected $geoIpReader;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(GeoIpReader $geoIpReader, Request $request)
    {
        $this->useCdn = Config::get('aws.previews_use_cdn');
        $this->excludedCountries = Config::get('aws.previews_cdn_exceptions', []);
        $this->geoIpReader = $geoIpReader;
        $this->request = $request;
    }

    public function shouldUseCDN(): bool
    {
        if ($this->shouldUse === null) {
            $this->shouldUse = $this->check();
        }

        return $this->shouldUse;
    }

    protected function check(): bool
    {
        if (!$this->useCdn) {
            return false;
        }

        $ip = $this->request->ip();
        // we call city because a country method does not exist.
        $record = $this->geoIpReader->city($ip);

        // only use cdn if we can confirm origin
        if (!$record) {
            return false;
        }

        $location = strtolower($record->country->name);
        $excluded = in_array($location, $this->excludedCountries);

        return !$excluded;
    }
}
