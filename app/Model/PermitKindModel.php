<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PermitKindModel extends Model
{
    protected $table = 'PermitKinds';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public static function getPermitKinds(){
        return self::all();
    }


}
