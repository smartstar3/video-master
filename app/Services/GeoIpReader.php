<?php namespace MotionArray\Services;

use GeoIp2\Database\Reader;
use App;
use Exception;

class GeoIpReader extends Reader
{
    protected static $resolvedCities;

    public function city($ipAddress)
    {
        if ($ipAddress == '127.0.0.1' || App::isLocal()) {
            $ipAddress = \Config::get('geoip.test_ip');
        }

        if (isset(self::$resolvedCities[$ipAddress])) {
            return self::$resolvedCities[$ipAddress];
        }

        $class = null;
        try {
            $class = parent::city($ipAddress);
        } catch (Exception $e) {
        }

        self::$resolvedCities[$ipAddress] = $class;

        return $class;
    }
}