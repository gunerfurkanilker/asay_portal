<?php

namespace App\Model;

use http\Client\Request;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';




    public static function saveGeneralInformations($id,$requestData)
    {
        $isProcessOk = self::where('Id',$id)->update($requestData);
        return $isProcessOk;
    }



}
