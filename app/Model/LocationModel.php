<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class LocationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Location";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'City',
        'District',
        'Country',
        'Address',
        'ZIPCode',
        'CityID',
        'DistrictID',
        'CountryID',

    ];

    public static function saveLocation($request)
    {
        if ($request->LocationID != null)
            $location = self::where(['EmployeeID' => $request->EmployeeID, 'Id' => $request->LocationID])->first();
        else
            $location = new LocationModel();

        $location->EmployeeID   = $request->EmployeeID;
        $location->Address      = $request->Address;
        $location->CityID       = $request->CityID;
        $location->DistrictID   = $request->DistrictID;
        $location->CountryID    = $request->CountryID;
        $location->ZIPCode      = $request->ZIPCode;

        return $location->save();

    }

    public static function addLocation($request, $employee)
    {
        $location = self::create([
            'Address' => $request['address'],
            'CityID' => $request['city'],
            'DistrictID' => $request['district'],
            'CountryID' => $request['country'],
            'ZIPCode' => $request['zipcode']
        ]);
        $location->save();
        if ($location != null) {
            $employee->LocationID = $location->Id;
            $employee->save();
            return $location->fresh();
        } else
            return $location;
    }

    public static function getLocationFields()
    {
        $data = [];
        $data['Countries'] = CountryModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::all();

        return $data;
    }

    public function getCityAttribute()
    {
        $city = $this->hasOne(CityModel::class, "Id", "CityID");

        return $city->where("Active", "1")->first();

    }

    public function getDistrictAttribute()
    {
        $district = $this->hasOne(DistrictModel::class, "Id", "DistrictID");

        return $district->where("Active", "1")->first();
    }

    public function getCountryAttribute()
    {
        $country = $this->hasOne(CountryModel::class, "Id", "CountryID");

        return $country->where("Active", "1")->first();
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['Address'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getAddressAttribute($value)
    {
        try {
            return $this->attributes['Address'] !== null || $this->attributes['Address'] != '' ? Crypt::decryptString($this->attributes['Address']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setZIPCodeAttribute($value)
    {
        $this->attributes['ZIPCode'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getZIPCodeAttribute($value)
    {
        try {
            return $this->attributes['ZIPCode'] !== null || $this->attributes['ZIPCode'] != '' ? Crypt::decryptString($this->attributes['ZIPCode']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setCityIDAttribute($value)
    {
        $this->attributes['CityID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getCityIDAttribute($value)
    {
        try {
            return $this->attributes['CityID'] !== null || $this->attributes['CityID'] != '' ? (int) Crypt::decryptString($this->attributes['CityID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setCountryIDAttribute($value)
    {
        $this->attributes['CountryID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getCountryIDAttribute($value)
    {
        try {
            return $this->attributes['CountryID'] !== null || $this->attributes['CountryID'] != '' ? (int) Crypt::decryptString($this->attributes['CountryID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDistrictIDAttribute($value)
    {
        $this->attributes['DistrictID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDistrictIDAttribute($value)
    {
        try {
            return $this->attributes['DistrictID'] !== null || $this->attributes['DistrictID'] != '' ? (int) Crypt::decryptString($this->attributes['DistrictID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

}
