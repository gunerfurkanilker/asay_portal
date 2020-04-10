<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NationalityModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "nationality";

    public static function allNationalities()
    {
        return self::where("active",1)->get()->toArray();
    }
}
