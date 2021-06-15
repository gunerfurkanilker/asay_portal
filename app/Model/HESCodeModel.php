<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HESCodeModel extends Model
{
    //
    protected $table = "HESCodes";
    protected $appends = [
        'Employee'
    ];

    public function getEmployeeAttribute(){

        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
        {
            $employee = $employee->where("Active",1)->first();
            return $employee;
        }
        else
            return null;
    }

}
