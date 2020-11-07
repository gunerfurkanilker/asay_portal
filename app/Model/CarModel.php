<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "Car";
    protected $appends = [
        'CarType',
        'CarBrand',
        'CarBrandModel'
    ];


    public function getCarTypeAttribute()
    {

        $carType = $this->hasOne(CarTypeModel::class, "id", "CarTypeID");
        if ($carType) {
            return $carType->first();
        } else {
            return "";
        }

    }

    public function getCarBrandAttribute()
    {

        $carBrand = $this->hasOne(CarBrandModel::class, "id", "CarBrandID");
        if ($carBrand) {
            return $carBrand->first();
        } else {
            return "";
        }

    }

    public function getCarBrandModelAttribute()
    {

        $carBrandModel = $this->hasOne(CarBrandModel::class, "id", "CarBrandModelID");
        if ($carBrandModel) {
            return $carBrandModel->first();
        } else {
            return "";
        }

    }



}
