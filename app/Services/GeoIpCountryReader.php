<?php

namespace MotionArray\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

class GeoIpCountryReader extends Reader
{
    protected static $resolvedCountries;

    /**
     * @param string $ipAddress
     * @return \GeoIp2\Model\Country|null
     */
    public function country($ipAddress)
    {
        if (isset(self::$resolvedCountries[$ipAddress])) {
            return self::$resolvedCountries[$ipAddress];
        }

        $class = null;
        try {
            $class = parent::country($ipAddress);
        } catch (AddressNotFoundException $exception) {
        } catch (\Exception $exception) {
            \Log::error($exception);
        }

        self::$resolvedCountries[$ipAddress] = $class;

        return $class;
    }
}
