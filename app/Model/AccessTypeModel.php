<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AccessTypeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "accesstype";

    public function employeemodel()
    {
        return $this->belongsTo("App\Model\EmployeeModel","AccessTypeID","id");
    }
}
