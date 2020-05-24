<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DistrictModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "District";

    public static function getDistrict($id)
    {
        return self::find('Id',$id);
    }

}
