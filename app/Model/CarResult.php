<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CarResult extends Model
{
    //

    protected $primaryKey = "id";
    protected $table = "CarResult";
    protected $guarded=[];
    public $timestamps = false;


    public function city()
    {
        return $this->belongsTo(CityModel::class, 'FineCity');
    }

    public function type()
    {
        return $this->belongsTo(CarTypeModel::class, 'Type');
    }
    public function brand()
    {
        return $this->belongsTo(CarBrandModel::class,'Brand');
    }
    public function color()
    {
        return $this->belongsTo(CarColorModel::class,'Colour');
    }
    public function model()
    {
        return $this->belongsTo(CarModel::class,'Model');
    }
    public function carFines()
    {
        return $this->belongsTo(CarFinesModel::class,'FineItem');
    }
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class,'DriverId');
    }
    public function employeer()
    {
        return $this->belongsTo(EmployeeModel::class,'RecourseId');
    }
}
