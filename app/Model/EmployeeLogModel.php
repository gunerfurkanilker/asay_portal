<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeLogModel extends Model
{
    protected $table = "EmployeeLog";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';


}
