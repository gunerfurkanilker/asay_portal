<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeePropertyModel extends Model
{
    protected $table = "EmployeeProperty";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';

}
