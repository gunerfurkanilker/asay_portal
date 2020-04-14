<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LocationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Location";

    public static function saveLocation($request,$locationID)
    {
        $location = self::find($locationID);

        if ($location != null)
        {
            $location->Address = $request['address'];
            $location->CityID = $request['address'];
            $location->DistrictID = $request['address'];
            $location->CountryID = $request['address'];
            $location->ZIPCode = $request['zipcode'];
        }

        else
            return false;

    }

    public static function addLocation($request,$employee)
    {
        $location = self::create([
            'Address' => $request['address'],
            'CityID' => $request['city'],
            'DistrictID' => $request['district'],
            'CountryID' => $request['country'],
            'ZIPCode' => $request['zipcode']
        ]);

        if ($location)
        {
            $employee->LocationID = $location->Id;
            return $location;
        }

        else
            return false;
    }
}
