<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Country";

    public static function getCitiesOfCountry($request_data)
    {
        $country = self::find($request_data['countryid']);
        if ($country == null)
            return false;
        $cities = CityModel::where('CountryID',$country->Id)->get();

        return $cities;
    }


}
