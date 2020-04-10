<?php

namespace App\Model;

use http\Client\Request;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';

    public function addEmployee(Request $request)
    {

    }

}
