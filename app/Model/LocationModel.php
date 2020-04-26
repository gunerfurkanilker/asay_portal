<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LocationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Location";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
      'City',
      'District',
      'Country'
    ];

    public static function saveLocation($request,$locationID)
    {
        $location = self::find($locationID);

        if ($location != null)
        {
            $location->Address = $request['address'];
            $location->CityID = $request['city'];
            $location->DistrictID = $request['district'];
            $location->CountryID = $request['country'];
            $location->ZIPCode = $request['zipcode'];

            $location->save();

            return $location->fresh();
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
        $location->save();
        if ($location != null)
        {
            $employee->LocationID = $location->Id;
            $employee->save();
            return $location->fresh();
        }

        else
            return $location;
    }

    public static function getLocationFields()
    {
        $data = [];
        $data['Countries'] = CountryModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::where('CityID',35)->get();

        return $data;
    }

    public function getCityAttribute()
    {
        $city = $this->hasOne(CityModel::class,"Id","CityID");

        return $city->where("Active","1")->first();

    }

    public function getDistrictAttribute()
    {
        $district = $this->hasOne(DistrictModel::class,"Id","DistrictID");

        return $district->where("Active","1")->first();
    }

    public function getCountryAttribute()
    {
        $country = $this->hasOne(CountryModel::class,"Id","CountryID");

        return $country->where("Active","1")->first();
    }

}
