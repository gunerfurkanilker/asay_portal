<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CityModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "city";

    public function districts(){
        return $this->hasMany("App\Model\Ik\Employee\District","CityID","Id");
    }
}
