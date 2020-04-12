<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AccessTypeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "AccessType";


    public function employee()
    {
        return $this->belongsTo("App\Model\EmployeeModel","AccessTypeID","Id");
    }
}
