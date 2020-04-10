<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DistrictModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "district";

    public function belongsCity()
    {
        return $this->belongsTo("App\Model\Ik\Employee","Id","CityID");
    }
}
