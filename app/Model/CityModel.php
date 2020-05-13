<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CityModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "City";

    public function districts(){
        return $this->hasMany("App\Model\Ik\Employee\District","CityID","Id");
    }

    public static function getDistrictsOfCity($request_data)
    {
        $city = self::find($request_data['cityid']);
        if ($city == null)
            return false;
        $cities = DistrictModel::where('CityID',$city->Id)->get();

        return $cities;
    }

}
