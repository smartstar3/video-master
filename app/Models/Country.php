<?php

namespace MotionArray\Models;

class Country extends BaseModel
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Return country object based on 2 letter code
     *
     * @param string $countryCode
     * @return Country
     * @throws \Exception
     */
    public static function byCode($countryCode)
    {
        $countryCode = strtoupper($countryCode);
        $country = static::where("code", "=", $countryCode)->first();
        if ($country === null) {
            throw new \Exception("Invalid country code '{$countryCode}'.");
        }
        return $country;
    }
}
