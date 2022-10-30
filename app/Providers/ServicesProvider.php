<?php namespace MotionArray\Providers;

use Illuminate\Support\ServiceProvider;
use Event;
use MotionArray\Services\Cdn\PreviewCdnChecker;
use MotionArray\Services\GeoIpCountryReader;
use MotionArray\Services\GeoIpReader;

class ServicesProvider extends ServiceProvider
{
    /**
     * binding services contracts
     */
    public function register()
    {
        $this->app->singleton(PreviewCdnChecker::class);

        $this->app->bind(GeoIpReader::class, function () {
            return new GeoIpReader(config('geoip.city'));
        });
        $this->app->bind(GeoIpCountryReader::class, function () {
            return new GeoIpCountryReader(config('geoip.country'));
        });

        $this->app->bind(
            'MotionArray\Services\Encoding\EncodingInterface',
            'MotionArray\Services\Encoding\ZencoderEncoding'
        );
    }
}
