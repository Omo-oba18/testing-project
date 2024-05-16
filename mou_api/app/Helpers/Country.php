<?php

namespace App\Helpers;

class Country
{
    public static $jsonCountries = [];

    private static function getJsonFile()
    {
        if (count(self::$jsonCountries) == 0) {
            try {
                $jsonCountries = json_decode(file_get_contents(config_path('countries_phone.json')));
                self::$jsonCountries = $jsonCountries; //cache
            } catch (\Exception $exception) {

            }
        }

        return self::$jsonCountries;
    }

    /**
     * Get countries
     *
     * @return array
     */
    public static function getCountries()
    {
        $jsonCountries = self::getJsonFile();
        $countries = [];
        if ($jsonCountries) {
            foreach ($jsonCountries as $country) {
                $countries[strtolower($country->code)] = $country->name;
            }
        }

        return $countries;
    }

    /**
     * Get countries
     *
     * @return array
     */
    public static function getCountryDialCodes()
    {
        $jsonCountries = self::getJsonFile();

        $countries = [];
        if ($jsonCountries) {
            foreach ($jsonCountries as $country) {
                $countries[strtolower($country->code)] = $country->dial_code;
            }
        }

        return $countries;
    }
}
