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


    public function accesstypemodel()
    {
        return $this->hasOne("App\Model\AccessTypeModel","id","AccessTypeID");
    }

    public function addEmployee(Request $request)
    {

    }



}
